# time-api

Small time manipulating API that accepts a `POST` request in `/api/time` with an input such as:
```json
{
  "occurrences": [
    "01:43",
    "01:52",
    "02:30",
    "04:30",
    "05:00",
    "05:25",
    "05:38",
    "06:10",
    "06:44",
    "06:45"
  ]
}
```

and responds with a JSON as such:

```json
{
  "grouped_occurrences": {
    "1": 2,
    "2": 1,
    "4": 1,
    "5": 3,
    "6": 3
  },
  "most_occurrences_at": 13,
  "occurrences_per_hour": 2.5,
  "biggest_interval_in_minutes": 120,
  "smallest_interval_in_minutes": 0
}
```
