import json
import subprocess
import sys

# Test data with locked groups
test_data = {
    "days": ["Mon", "Tue", "Wed", "Thu", "Fri"],
    "day_caps": {"Mon": 9, "Tue": 9, "Wed": 9, "Thu": 9, "Fri": 9},
    "max_same_subject_per_day": 2,
    "max_time_seconds": 10,
    "num_workers": 4,
    "lesson_times": [
        {"slot": 1, "start": "08:00", "end": "08:45"},
        {"slot": 2, "start": "08:55", "end": "09:40"},
        {"slot": 3, "start": "09:50", "end": "10:35"},
        {"slot": 4, "start": "10:45", "end": "11:30"},
        {"slot": 5, "start": "11:40", "end": "12:25"}
    ],
    "groups": [
        {
            "id": 1,
            "lessons_per_week": 3,
            "teacher_id": 101,
            "room_id": 201,
            "subject_id": 1,
            "priority": 5,
            "can_merge": False,
            "student_ids": [1, 2, 3],
            "is_locked": True  # This group is locked
        },
        {
            "id": 2,
            "lessons_per_week": 2,
            "teacher_id": 102,
            "room_id": 202,
            "subject_id": 2,
            "priority": 0,
            "can_merge": False,
            "student_ids": [1, 2, 3],
            "is_locked": False
        }
    ],
    "teacher_unavailability": {},
    "existing_slots": [
        # Group 1 is locked at these positions
        {"timetable_group_id": 1, "day": "Mon", "slot": 1},
        {"timetable_group_id": 1, "day": "Tue", "slot": 2},
        {"timetable_group_id": 1, "day": "Wed", "slot": 3}
    ]
}

# Write JSON to stdin of Python solver
json_input = json.dumps(test_data)

# Run the solver
process = subprocess.Popen(
    ["python", "python/timetable_solver.py"],
    stdin=subprocess.PIPE,
    stdout=subprocess.PIPE,
    stderr=subprocess.PIPE,
    text=True
)

stdout, stderr = process.communicate(input=json_input)

print("=== STDOUT ===")
print(stdout)

if stderr:
    print("\n=== STDERR ===")
    print(stderr)

# Parse result
try:
    result = json.loads(stdout.strip())
    print("\n=== RESULT ===")
    print(json.dumps(result, indent=2))
    
    if result.get("success"):
        print("\n=== LOCKED GROUP CHECK ===")
        # Check if group 1 (locked) kept its positions
        group1_assignments = [a for a in result["assignments"] if a["timetable_group_id"] == 1]
        print(f"Group 1 (locked) assignments: {group1_assignments}")
        
        expected = [
            {"timetable_group_id": 1, "day": "Mon", "slot": 1},
            {"timetable_group_id": 1, "day": "Tue", "slot": 2},
            {"timetable_group_id": 1, "day": "Wed", "slot": 3}
        ]
        
        for exp in expected:
            if exp in group1_assignments:
                print(f"✓ Found expected assignment: {exp}")
            else:
                print(f"✗ MISSING expected assignment: {exp}")
        
        print("\nGroup 2 (not locked) assignments:")
        group2_assignments = [a for a in result["assignments"] if a["timetable_group_id"] == 2]
        print(group2_assignments)
        
except json.JSONDecodeError as e:
    print(f"\nFailed to parse JSON: {e}")
