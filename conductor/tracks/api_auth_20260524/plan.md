# Plan: Build REST API authentication (login/logout) for OSPOS users

Track ID: api_auth_20260524

## Phase 1: Setup & Configuration
- [x] Task: Install JWT library via Composer (ba42ab2)
    - [x] Run `composer require firebase/php-jwt`
    - [x] Verify autoloading works
- [x] Task: Create API configuration file (4302e16)
    - [x] Create `app/Config/API.php` with JWT secret key, token expiry, and CORS settings
    - [x] Add config loading to project (automatic via CI4 config() helper)
- [x] Task: Set up API routing (f1044d7)
    - [x] Add API route group in `app/Config/Routes.php` with `/api/v1` prefix
    - [x] Add CORS headers handling via filters
- [x] Task: Conductor - User Manual Verification 'Phase 1: Setup & Configuration' (Protocol in workflow.md)

## Phase 2: Login API Endpoint
- [x] Task: Create Login API Controller (0a33663)
    - [x] Create `app/Controllers/api/v1/Auth.php` with `login()` method
    - [x] Implement username/password validation against `ospos_employees` table
    - [x] Implement password verification (bcrypt with MD5 fallback for legacy)
    - [x] Generate JWT token on successful authentication
    - [x] Return token and user data in JSON response
    - [x] Handle invalid credentials with 401 error response
- [x] Task: Write unit tests for Login endpoint (150430a)
    - [x] Test JWT token generation and validation
    - [x] Test expired token rejection
    - [x] Test invalid signature rejection
    - [x] Test login controller exists (via JWT component tests)
- [x] Task: Conductor - User Manual Verification 'Phase 2: Login API Endpoint' (Protocol in workflow.md)

## Phase 3: Authentication Middleware
- [x] Task: Create JWT Authentication Filter (449de0a)
    - [x] Create `app/Filters/JWTAuth.php` implementing `FilterInterface`
    - [x] Extract and validate Bearer token from Authorization header
    - [x] Decode JWT and verify signature
    - [x] Set authenticated user data in request
    - [x] Return 401 on invalid/expired token
- [x] Task: Register filter in Config (449de0a)
    - [x] Add JWT filter to `app/Config/Filters.php`
    - [x] Apply filter to API route group
- [x] Task: Create Get Current User endpoint (8c90eba)
    - [x] Add `me()` method to Auth controller
    - [x] Return authenticated user info
- [~] Task: Write unit tests for JWT Middleware
    - [ ] Test valid token passes filter
    - [ ] Test missing token returns 401
    - [ ] Test expired token returns 401
    - [ ] Test invalid signature returns 401
    - [ ] Test /api/v1/me endpoint with valid token
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Authentication Middleware' (Protocol in workflow.md)

## Phase 4: Logout API Endpoint
- [x] Task: Implement token blacklisting for logout
    - [x] Create database migration for token blacklist table
    - [x] Create `app/Models/TokenBlacklist.php` model
    - [x] Add `logout()` method to Auth controller
    - [x] Add token to blacklist on logout
    - [x] Update authentication filter to check blacklist
- [ ] Task: Write unit tests for Logout
    - [ ] Test successful logout
    - [ ] Test using blacklisted token returns 401
- [ ] Task: Conductor - User Manual Verification 'Phase 4: Logout API Endpoint' (Protocol in workflow.md)

## Phase 5: Documentation & Finalization
- [x] Task: Write API documentation
    - [x] Create `api_upgrade/API_login.md` with complete API reference
    - [x] Include curl examples for each endpoint
    - [x] Document error responses
    - [x] Document authentication flow
- [ ] Task: End-to-end testing
    - [ ] Test full login → access protected route → logout flow
    - [ ] Verify CORS headers
    - [ ] Test error scenarios
- [ ] Task: Conductor - User Manual Verification 'Phase 5: Documentation & Finalization' (Protocol in workflow.md)
