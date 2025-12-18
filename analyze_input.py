import json

with open('storage/logs/last_timetable_input.json', 'r', encoding='utf-8-sig') as f:
    data = json.load(f)

print(f"Total groups: {len(data['groups'])}")
print(f"Days: {data['days']}")
print(f"Day caps: {data['day_caps']}")
print(f"Max same subject per day: {data['max_same_subject_per_day']}")

# Calculate total lessons needed
total_lessons = sum(g['lessons_per_week'] for g in data['groups'])
print(f"\nTotal lessons needed per week: {total_lessons}")

# Calculate available slots
total_slots = sum(data['day_caps'].values())
print(f"Total slots available per week: {total_slots}")

# Teacher workload
from collections import defaultdict
teacher_lessons = defaultdict(int)
teacher_groups = defaultdict(int)
for g in data['groups']:
    teacher_lessons[g['teacher_id']] += g['lessons_per_week']
    teacher_groups[g['teacher_id']] += 1

print(f"\nTeacher workload:")
for tid in sorted(teacher_lessons.keys()):
    print(f"  Teacher {tid}: {teacher_groups[tid]} groups, {teacher_lessons[tid]} lessons/week")

# Room usage
room_lessons = defaultdict(int)
for g in data['groups']:
    room_lessons[g['room_id']] += g['lessons_per_week']

print(f"\nRoom workload:")
for rid in sorted(room_lessons.keys()):
    print(f"  Room {rid}: {room_lessons[rid]} lessons/week (out of {total_slots} available)")
    
# Check for overbooked resources
print(f"\nProblems:")
if total_lessons > total_slots:
    print(f"  ERROR: Need {total_lessons} slots but only have {total_slots} available!")
    
for rid, lessons in room_lessons.items():
    if lessons > total_slots:
        print(f"  ERROR: Room {rid} needs {lessons} slots but only {total_slots} available!")
