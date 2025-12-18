import json
import sys
from typing import Dict, List, Tuple

try:
    from ortools.sat.python import cp_model
except Exception as e:
    print(json.dumps({"error": f"ortools import failed: {e}"}))
    sys.exit(1)


def get_slot_time_range(slot: int, lesson_times: List[Dict]) -> Tuple[str, str]:
    """Get start and end time for a slot from lesson_times config"""
    for lesson in lesson_times:
        if lesson.get('slot') == slot:
            return lesson.get('start', '08:00'), lesson.get('end', '09:00')
    # Fallback if slot not found
    start_hour = 7 + slot
    end_hour = 8 + slot
    return f"{start_hour:02d}:00", f"{end_hour:02d}:00"


def overlaps_slot(start_str: str, end_str: str, slot: int, lesson_times: List[Dict]) -> bool:
    """Check if teacher unavailability overlaps with lesson time slot"""
    # Get lesson time range for this slot
    slot_start, slot_end = get_slot_time_range(slot, lesson_times)
    
    # Parse times as minutes since midnight
    sh, sm = map(int, start_str.split(':'))
    eh, em = map(int, end_str.split(':'))
    slot_sh, slot_sm = map(int, slot_start.split(':'))
    slot_eh, slot_em = map(int, slot_end.split(':'))
    
    start_min = sh * 60 + sm
    end_min = eh * 60 + em
    slot_start_min = slot_sh * 60 + slot_sm
    slot_end_min = slot_eh * 60 + slot_em
    
    # overlap if start < slot_end and end > slot_start
    return start_min < slot_end_min and end_min > slot_start_min


def solve_timetable(data: Dict) -> Dict:
    days: List[str] = data.get('days', ['Mon','Tue','Wed','Thu','Fri'])
    day_caps: Dict[str, int] = data.get('day_caps', {d: 9 for d in days})
    groups: List[Dict] = data.get('groups', [])
    teacher_unavail: Dict[str, Dict[str, List[Tuple[str, str]]]] = data.get('teacher_unavailability', {})
    lesson_times: List[Dict] = data.get('lesson_times', [])  # Get lesson times from school settings
    max_same_subject_per_day: int = int(data.get('max_same_subject_per_day', 2))
    allow_merges: bool = True  # enable merge logic when flagged

    # Build indices
    day_index = {d: i for i, d in enumerate(days)}
    max_slots_per_day = max(day_caps.values()) if day_caps else 9

    model = cp_model.CpModel()

    # Decision variables: x[g][d][s] in {0,1}
    x = {}
    for g in groups:
        gid = g['id']
        x[gid] = {}
        for d in days:
            x[gid][d] = {}
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                x[gid][d][s] = model.NewBoolVar(f"x_g{gid}_{d}_{s}")

    # Each group SHOULD get lessons_per_week slots (soft constraint via objective)
    # We'll maximize the number of placed lessons later in objective
    lesson_targets = {}
    for g in groups:
        gid = g['id']
        lessons = int(max(1, int(g.get('lessons_per_week', 1))))
        lesson_targets[gid] = lessons
        vars_list = []
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                vars_list.append(x[gid][d][s])
        # Allow 0 to lessons slots (not strictly equal)
        model.Add(sum(vars_list) <= lessons)

    # Subject per day cap: Sum_s x[g,d,s] <= max_same_subject_per_day
    for g in groups:
        gid = g['id']
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            model.Add(sum(x[gid][d][s] for s in range(1, cap + 1)) <= max_same_subject_per_day)

    # Teacher time-based unavailability: forbid slots overlapping any range
    for g in groups:
        gid = g['id']
        tid = g.get('teacher_id')
        if not tid:
            continue
        tid_str = str(tid)
        t_unavail = teacher_unavail.get(tid_str, {})
        for d in days:
            ranges = t_unavail.get(d, [])
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                # if any range overlaps, forbid assignment
                disallow = any(overlaps_slot(start, end, s, lesson_times) for (start, end) in ranges)
                if disallow:
                    model.Add(x[gid][d][s] == 0)

    # Teacher conflict constraints with merging
    # Limit sum per teacher/day/slot to at most 1, but allow up to 2 if merging same subject and both flags on
    # We'll enforce: Sum_{g with teacher t} x[g,d,s] <= 2, and forbid non-mergeable pairs at same slot
    teachers = {}
    for g in groups:
        tid = g.get('teacher_id')
        if tid:
            teachers.setdefault(tid, []).append(g)

    for t, tg in teachers.items():
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                # Global cap at most 2 stacks
                model.Add(sum(x[g['id']][d][s] for g in tg) <= 2)
                # Forbid pairs that are not mergeable
                n = len(tg)
                for i in range(n):
                    for j in range(i+1, n):
                        g1 = tg[i]
                        g2 = tg[j]
                        mergeable = False
                        if allow_merges:
                            if (g1.get('subject_id') and g2.get('subject_id') and g1['subject_id'] == g2['subject_id']
                                and bool(g1.get('can_merge')) and bool(g2.get('can_merge'))):
                                mergeable = True
                        if not mergeable:
                            # x1 + x2 <= 1
                            model.Add(x[g1['id']][d][s] + x[g2['id']][d][s] <= 1)

    # Room conflict: Sum_{g with room r} x[g,d,s] <= 1
    rooms = {}
    for g in groups:
        rid = g.get('room_id')
        if rid:
            rooms.setdefault(rid, []).append(g)
    for r, rg in rooms.items():
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                model.Add(sum(x[g['id']][d][s] for g in rg) <= 1)

    # Student conflict: Sum_{g containing student u} x[g,d,s] <= 1
    student_groups: Dict[int, List[Dict]] = {}
    for g in groups:
        for u in g.get('student_ids', []):
            student_groups.setdefault(int(u), []).append(g)
    for u, sg in student_groups.items():
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                model.Add(sum(x[g['id']][d][s] for g in sg) <= 1)

    # Window minimization for students: minimize gaps during the day
    # For each student and day, create gap variables to penalize in objective
    gap_vars = []
    for u, sg in student_groups.items():
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            # For each pair of slots, if student has lesson at both but not between them, it's a gap
            for s in range(1, cap):
                for s_next in range(s + 2, cap + 1):  # At least 2 slots apart
                    # Check if this forms a gap
                    has_at_s = sum(x[g['id']][d][s] for g in sg)
                    has_at_s_next = sum(x[g['id']][d][s_next] for g in sg)
                    
                    # Check all intermediate slots
                    has_between = sum(x[g['id']][d][s_mid] for g in sg for s_mid in range(s + 1, s_next))
                    
                    # Gap variable: 1 if has at s AND has at s_next but nothing between
                    gap_var = model.NewBoolVar(f'gap_{u}_{d}_{s}_{s_next}')
                    model.Add(has_at_s + has_at_s_next - has_between >= 2).OnlyEnforceIf(gap_var)
                    model.Add(has_at_s + has_at_s_next - has_between < 2).OnlyEnforceIf(gap_var.Not())
                    gap_vars.append(gap_var)

    # Objective: 
    # 1. Maximize number of placed lessons (primary goal)
    # 2. Prefer high-priority groups
    # 3. Prefer earlier slots for high-priority groups
    # 4. Minimize windows (gaps) for students during the day
    objective_terms = []
    
    # Primary: maximize total placed lessons (multiply by large weight)
    for g in groups:
        gid = g['id']
        prio = int(g.get('priority', 0) or 0)
        # Weight: priority matters, but placing ANY lesson is most important
        placement_weight = 10000 + (prio * 100)  # Base 10000 + priority bonus
        
        for d in days:
            cap = day_caps.get(d, max_slots_per_day)
            for s in range(1, cap + 1):
                # Maximize placement (negative because we'll minimize)
                objective_terms.append(-placement_weight * x[gid][d][s])
                
                # Secondary: small penalty for late slots
                if prio > 0 and s > 5:
                    late_penalty = prio * (s - 5)
                    objective_terms.append(late_penalty * x[gid][d][s])

    # Minimize windows (gaps) for students: penalize gap variables
    # Each gap costs 500 points (high enough to avoid when possible)
    for gap_var in gap_vars:
        objective_terms.append(500 * gap_var)

    if objective_terms:
        model.Minimize(sum(objective_terms))

    solver = cp_model.CpSolver()
    solver.parameters.max_time_in_seconds = float(data.get('max_time_seconds', 15))
    solver.parameters.num_search_workers = int(data.get('num_workers', 8))

    status = solver.Solve(model)
    
    # Even if not optimal/feasible, try to return partial solution
    assignments = []
    placed_units = 0
    placed_by_group = {}  # Track which groups got how many slots
    
    if status in (cp_model.OPTIMAL, cp_model.FEASIBLE):
        # Full or partial solution found
        for g in groups:
            gid = g['id']
            placed_count = 0
            for d in days:
                cap = day_caps.get(d, max_slots_per_day)
                for s in range(1, cap + 1):
                    if solver.Value(x[gid][d][s]) == 1:
                        assignments.append({
                            "timetable_group_id": gid,
                            "day": d,
                            "slot": s
                        })
                        placed_units += 1
                        placed_count += 1
            placed_by_group[gid] = placed_count
    
    # Analyze unplaced groups and provide reasons
    unplaced_info = []
    for g in groups:
        gid = g['id']
        needed = lesson_targets.get(gid, 1)
        placed = placed_by_group.get(gid, 0)
        
        if placed < needed:
            reasons = []
            tid = g.get('teacher_id')
            rid = g.get('room_id')
            
            # Check teacher unavailability
            if tid and str(tid) in teacher_unavail:
                t_unavail = teacher_unavail[str(tid)]
                blocked_slots = sum(len(ranges) for ranges in t_unavail.values())
                if blocked_slots > 0:
                    reasons.append(f"mokytojas užimtas {blocked_slots} laiko tarpu")
            
            # Generic reasons based on resource conflicts
            if placed == 0:
                reasons.append("nepavyko rasti laisvos vietos")
            elif placed < needed:
                missing = needed - placed
                reasons.append(f"trūksta {missing} laisvų periodų")
            
            if not reasons:
                reasons.append("konfliktai su kitais užsiėmimais")
            
            unplaced_info.append({
                "group_id": gid,
                "needed": needed,
                "placed": placed,
                "missing": needed - placed,
                "reason": "; ".join(reasons)
            })
    
    # Always return success with whatever we managed to place
    # Unplaced groups will be shown in "unscheduled" section
    total_units_needed = sum(g.get('lessons_per_week', 1) for g in groups)
    
    return {
        "success": True,
        "assignments": assignments,
        "placed_units": placed_units,
        "total_units": total_units_needed,
        "status": "optimal" if status == cp_model.OPTIMAL else "partial" if status == cp_model.FEASIBLE else "no_solution",
        "unplaced_groups": unplaced_info
    }


def main():
    try:
        import sys
        import io
        # Fix Windows encoding issues - use utf-8-sig to handle BOM
        if sys.platform == 'win32':
            sys.stdin = io.TextIOWrapper(sys.stdin.buffer, encoding='utf-8-sig')
        raw = sys.stdin.read().strip()
        if not raw:
            print(json.dumps({"success": False, "error": "Empty input received"}))
            return
        data = json.loads(raw)
    except Exception as e:
        print(json.dumps({"success": False, "error": f"Invalid JSON input: {e}"}))
        return
    result = solve_timetable(data)
    print(json.dumps(result))

if __name__ == '__main__':
    main()
