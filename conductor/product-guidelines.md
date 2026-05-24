# Product Guidelines

## Writing & Communication Style
- Documentation should be written in **Bahasa Indonesia** for local teams and **English** for API documentation to follow open-source standards.
- Use clear, concise, and professional language.
- All API responses should use JSON format with consistent structure.

## Branding
- Application name: **Kasirbaru**
- Primary identity: Web-based Point of Sale system built on OSPOS
- No strict brand guidelines currently; maintain professional appearance.

## UX Principles
1. **Efficiency:** Minimize clicks for common cashier operations (sales, search items, payment).
2. **Accessibility:** Web-based, accessible from any modern browser on desktop and tablet.
3. **Consistency:** Follow existing Bootstrap 3 UI patterns already established in OSPOS.
4. **Feedback:** All actions should provide clear success/error feedback (toastr notifications).

## Code & API Conventions
- Use **CodeIgniter 4** RESTful routing conventions.
- API endpoints follow `/api/v1/{resource}` pattern.
- Authentication via **Bearer Token** (JWT recommended).
- Responses follow standard format:
  - Success: `{ "status": "success", "data": {...} }`
  - Error: `{ "status": "error", "message": "..." }`

## Security Guidelines
- All API endpoints (except login) require authentication.
- Passwords must be hashed (using PHP's `password_hash`).
- Rate limiting for login attempts.
- Input validation on all API endpoints.
- CORS configuration for API access.
