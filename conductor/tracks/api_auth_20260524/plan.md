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
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Login API Endpoint' (Protocol in workflow.md)

## Phase 3: Authentication Middleware
- [ ] Task: Create JWT Authentication Filter
    - [ ] Create `app/Filters/JWTAuth.php` implementing `FilterInterface`
    - [ ] Extract and validate Bearer token from Authorization header
    - [ ] Decode JWT and verify signature
    - [ ] Set authenticated user data in request
    - [ ] Return 401 on invalid/expired token
- [ ] Task: Register filter in Config
    - [ ] Add JWT filter to `app/Config/Filters.php`
    - [ ] Apply filter to API route group
- [ ] Task: Create Get Current User endpoint
    - [ ] Add `me()` method to Auth controller
    - [ ] Return authenticated user info
- [ ] Task: Write unit tests for JWT Middleware
    - [ ] Test valid token passes filter
    - [ ] Test missing token returns 401
    - [ ] Test expired token returns 401
    - [ ] Test invalid signature returns 401
    - [ ] Test /api/v1/me endpoint with valid token
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Authentication Middleware' (Protocol in workflow.md)

## Phase 4: Logout API Endpoint
- [ ] Task: Implement token blacklisting for logout
    - [ ] Create database migration for token blacklist table
    - [ ] Create `app/Models/TokenBlacklist.php` model
    - [ ] Add `logout()` method to Auth controller
    - [ ] Add token to blacklist on logout
    - [ ] Update authentication filter to check blacklist
- [ ] Task: Write unit tests for Logout
    - [ ] Test successful logout
    - [ ] Test using blacklisted token returns 401
- [ ] Task: Conductor - User Manual Verification 'Phase 4: Logout API Endpoint' (Protocol in workflow.md)

## Phase 5: Documentation & Finalization
- [ ] Task: Write API documentation
    - [ ] Create `api_upgrade/API_login.md` with complete API reference
    - [ ] Include curl examples for each endpoint
    - [ ] Document error responses
    - [ ] Document authentication flow
- [ ] Task: End-to-end testing
    - [ ] Test full login → access protected route → logout flow
    - [ ] Verify CORS headers
    - [ ] Test error scenarios
- [ ] Task: Conductor - User Manual Verification 'Phase 5: Documentation & Finalization' (Protocol in workflow.md)
