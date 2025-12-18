# Timetable Generation - Python OR-Tools Solver

## Overview
Tvarkaraščio generavimas dabar naudoja Python OR-Tools constraint solver vietoj PHP greedy heuristikos.

## Kaip veikia

1. **Laravel** (`TimetableGenerator.php`):
   - Renka duomenis iš DB (groups, teacher unavailability, constraints)
   - Paruošia JSON input
   - Kviečia `python/timetable_solver.py` per `proc_open`
   - Gauna JSON results
   - Išsaugo `timetable_slots` lentelėje

2. **Python** (`timetable_solver.py`):
   - Gauna JSON per stdin
   - Sukuria CP-SAT model su OR-Tools
   - Constraints:
     - Kiekviena grupė gauna tiksliai `lessons_per_week` slots
     - Max `max_same_subject_per_day` per dieną
     - Teacher time unavailability (HH:MM ranges)
     - Teacher conflicts (allow merge if same subject + both flagged)
     - Room conflicts (hard constraint)
     - Student conflicts (hard constraint)
   - Objective: minimize late slots for high-priority groups
   - Solver time limit: 15s, 8 workers
   - Grąžina assignments JSON per stdout

## Requirements

```bash
python -m pip install ortools
```

## Testing

```bash
# Direct test (Windows)
echo '{"days":["Mon"],"day_caps":{"Mon":5},"groups":[{"id":1,"lessons_per_week":2,"teacher_id":10,"room_id":5,"subject_id":3,"priority":5,"can_merge":false,"student_ids":[1,2,3]}],"teacher_unavailability":{},"max_same_subject_per_day":2,"max_time_seconds":5,"num_workers":4}' | python python/timetable_solver.py
```

## Laravel Integration

Generator kviečiamas per:
- `GenerateTimetableJob::dispatch($timetable)`
- UI: "Generuoti" mygtukas tvarkaraščio puslapyje

## Constraints Applied

- **Time-based unavailability**: Mokytojai gali nurodyti HH:MM intervalus per dieną (pvz., 09:00-11:00 Pirmadienis)
- **Merge logic**: Jei 2 grupės turi tą patį subject_id ir abi `can_merge_with_same_subject=true`, gali būti stacked same slot
- **Priority**: Aukšto prioriteto grupės gauna ankstesnius slots (penalty už vėlyvus)
- **No working days**: Mokytojai nebeprivalo nurodyti darbo dienų - dirba visas dienas, tik su laiko apribojimais
