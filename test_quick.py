import json
import sys

# Load data and modify timeout
with open('storage/logs/last_timetable_input.json', 'r', encoding='utf-8-sig') as f:
    data = json.load(f)

# Set shorter timeout for testing
data['max_time_seconds'] = 30

# Write to stdout
sys.stdout.write(json.dumps(data))
