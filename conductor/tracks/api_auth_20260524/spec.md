# Spec: REST API Authentication (Login/Logout)

## Overview
Build a REST API authentication system for OSPOS (Kasirbaru) that allows external applications to authenticate using OSPOS user credentials and receive a JWT token for subsequent API calls.

## Endpoints

### POST /api/v1/login
Authenticate an OSPOS user and return a JWT token.

**Request:**
```json
{
  "username": "admin",
  "password": "pointofsale"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "username": "admin",
      "person_id": 1
    }
  }
}
```

**Error Response (401):**
```json
{
  "status": "error",
  "message": "Invalid username or password"
}
```

### POST /api/v1/logout
Invalidate the current JWT token.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

### GET /api/v1/me
Get current authenticated user info.

**Headers:**
```
Authorization: Bearer <token>
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "person_id": 1
  }
}
```

## Authentication Flow
1. Client sends POST /api/v1/login with username and password
2. Server validates credentials against `ospos_employees` table
3. On success, generates JWT token with user info and expiry
4. Client includes token in Authorization header for subsequent requests
5. Server validates token via middleware on protected routes

## Tech Requirements
- JWT library: `firebase/php-jwt`
- Token expiry: 1 hour (configurable)
- Password verification: Use PHP `password_verify()` for bcrypt hashes, fallback to MD5 for legacy OSPOS hashes
- Store blacklisted tokens in database or cache for logout functionality
