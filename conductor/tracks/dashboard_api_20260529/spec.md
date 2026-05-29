# Dashboard API for Mobile App

## Overview
Create a REST API endpoint to serve dashboard summary data for a mobile application. The endpoint returns total transaction count, total transaction value, and hourly revenue breakdown, matching the UI shown in `api_upgrade/dashboard/dashboard.html`.

## Type
Feature

## Functional Requirements

### 1. Authentication
- Uses existing JWT Bearer token authentication (`Authorization: Bearer <token>`)
- Protected by the `JWTAuth` filter (same as other API endpoints)

### 2. Endpoint
`GET /api/v1/dashboard`

### 3. Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `date_from` | string (Y-m-d) | No | Today | Start date for data range |
| `date_to` | string (Y-m-d) | No | Today | End date for data range |

If both `date_from` and `date_to` are omitted, data defaults to the current day. If only `date_from` is provided, `date_to` defaults to `date_from`.

### 4. Success Response (200)
Format matches existing API pattern: `{"success": true, "data": {...}}`

```json
{
  "success": true,
  "data": {
    "total_transactions": 142,
    "total_revenue": 12450000.00,
    "hourly_revenue": [
      {"hour": 6, "revenue": 450000},
      {"hour": 7, "revenue": 780000},
      {"hour": 8, "revenue": 1350000},
      {"hour": 9, "revenue": 1650000},
      {"hour": 10, "revenue": 1890000},
      {"hour": 11, "revenue": 980000},
      {"hour": 12, "revenue": 1720000},
      {"hour": 13, "revenue": 890000},
      {"hour": 14, "revenue": 760000},
      {"hour": 15, "revenue": 1150000},
      {"hour": 16, "revenue": 1420000},
      {"hour": 17, "revenue": 1580000},
      {"hour": 18, "revenue": 2100000},
      {"hour": 19, "revenue": 1850000},
      {"hour": 20, "revenue": 1200000},
      {"hour": 21, "revenue": 680000},
      {"hour": 22, "revenue": 320000}
    ]
  }
}
```

- `total_transactions`: Integer count of completed sales (`sale_status = 'complete'`) within the date range
- `total_revenue`: Float sum of total revenue (after discounts) across all completed sales
- `hourly_revenue`: Array of 17 objects (hours 6-22, representing 06:00 to 22:00), each with `hour` (int) and `revenue` (float). Hours with no transactions return `revenue: 0`.

### 5. Error Responses
| Status | Code | Condition |
|--------|------|-----------|
| 400 | `ERR_VALIDATION_FAILED` | Invalid date format |
| 401 | `ERR_AUTH_FAILED` | Missing or invalid token |

Error format follows existing conventions:
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Invalid date format. Use YYYY-MM-DD."
  }
}
```

## Acceptance Criteria
- [ ] `GET /api/v1/dashboard` returns `total_transactions` matching the count of completed sales in the date range
- [ ] `total_revenue` correctly sums transaction values (price * quantity, accounting for discounts)
- [ ] `hourly_revenue` contains 17 entries (hours 6-22), with correct revenue per hour
- [ ] JWT authentication is enforced (returns 401 without valid token)
- [ ] Invalid date format returns 400 with appropriate error message
- [ ] Data aggregates across all stock locations

## Out of Scope
- Per-location filtering (deferred to future enhancement)
- Separate per-metric endpoints (single combined endpoint as requested)
- Caching layer
- Pagination
