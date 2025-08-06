# MSC Student Portal Backend API

A PHP REST API for the Management Science Club student portal with authentication, student management, events, and announcements.

## 🚀 Quick Start

1. **Database Setup**: Import `main.sql` to create the database structure
2. **Config**: Update `config/database.php` with your database credentials
3. **Test**: Visit `/api/` to check if the API is running

## 📚 API Reference

All endpoints return JSON. Base URL: `your-domain.com/api`

### 🔐 Authentication

#### Register New User

```
POST /auth/register
```

**Request Body:**

```json
{
  "username": "jane_smith",
  "email": "jane@example.com",
  "password": "Password123",
  "first_name": "Jane",
  "middle_name": "A",
  "last_name": "Smith",
  "birthdate": "2000-05-15",
  "gender": "Female",
  "role": "member",
  "student_no": "2021-12345",
  "year_level": "3rd Year",
  "college": "College of Engineering",
  "program": "Computer Science"
}
```

#### Login

```
POST /auth/login
```

**Request Body:**

```json
{
  "username": "jane_smith",
  "password": "Password123"
}
```

#### Get Profile

```
GET /auth/profile
```

_Requires authentication_

#### Logout

```
POST /auth/logout
```

### 👥 Students

#### Get All Students

```
GET /students?page=1&limit=20&role=member
```

_Requires officer role_

#### Get Student by ID

```
GET /students/{id}
```

#### Search Students

```
GET /students/search?q=jane
```

_Requires officer role_

### 📅 Events

#### Get All Events

```
GET /events?page=1&limit=10&status=upcoming
```

#### Get Single Event

```
GET /events/{id}
```

#### Create Event

```
POST /events
```

_Requires officer role_

**Request Body:**

```json
{
  "event_name": "Tech Workshop",
  "event_date": "2025-03-15",
  "event_time_start": "14:00",
  "event_time_end": "17:00",
  "location": "Room 301",
  "event_type": "onsite",
  "description": "Learn modern web development",
  "event_restriction": "members",
  "registration_required": true
}
```

#### Register for Event

```
POST /events/{id}/register
```

_Requires authentication_

#### Update Event

```
PUT /events/{id}
```

_Requires officer role_

### 📢 Announcements

#### Get All Announcements

```
GET /announcements?page=1&limit=10&include_archived=false
```

#### Get Single Announcement

```
GET /announcements/{id}
```

#### Create Announcement

```
POST /announcements
```

_Requires officer role_

**Request Body:**

```json
{
  "title": "Meeting Tomorrow",
  "content": "Don't forget about the monthly meeting tomorrow at 2 PM.",
  "posted_by": "Admin"
}
```

#### Update Announcement

```
PUT /announcements/{id}
```

_Requires officer role_

## � Sample Data

### Student Table

| Field      | Example               | Type    |
| ---------- | --------------------- | ------- |
| username   | "jane_doe"            | string  |
| email      | "jane@example.com"    | string  |
| role       | "member" or "officer" | enum    |
| first_name | "Jane"                | string  |
| student_no | "2021-12345"          | string  |
| year_level | "3rd Year"            | string  |
| is_active  | true                  | boolean |

### Event Table

| Field                 | Example                             | Type    |
| --------------------- | ----------------------------------- | ------- |
| event_name            | "Tech Workshop"                     | string  |
| event_date            | "2025-03-15"                        | date    |
| event_time_start      | "14:00"                             | time    |
| location              | "Room 301"                          | string  |
| event_type            | "onsite", "online", "hybrid"        | enum    |
| event_status          | "upcoming", "completed", "canceled" | enum    |
| registration_required | true                                | boolean |

### Announcement Table

| Field       | Example                   | Type    |
| ----------- | ------------------------- | ------- |
| title       | "Meeting Tomorrow"        | string  |
| content     | "Monthly meeting at 2 PM" | text    |
| posted_by   | "Admin"                   | string  |
| is_archived | false                     | boolean |

## � Authentication

- Uses PHP sessions for authentication
- Two roles: **member** and **officer**
- Officers can create/edit events and announcements
- Members can register for events and view content

## 📝 Response Format

**Success:**

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

**Error:**

```json
{
  "success": false,
  "message": "Error description",
  "errors": {...}
}
```

## 🛠 Frontend Integration

### JavaScript Example

```javascript
// Login user
const login = async (username, password) => {
  const response = await fetch("/api/auth/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include", // Important for sessions
    body: JSON.stringify({ username, password }),
  });
  return response.json();
};

// Get events
const getEvents = async () => {
  const response = await fetch("/api/events", {
    credentials: "include",
  });
  return response.json();
};
```

### Common Headers

- `Content-Type: application/json` for POST/PUT requests
- `credentials: 'include'` for session-based auth

## 🔧 Testing

Use the included `api_tester.html` file to test all endpoints with a web interface.

## ⚠️ Important Notes

- Always include `credentials: 'include'` in fetch requests for authentication
- Officer-only endpoints return 403 error for non-officers
- All dates should be in YYYY-MM-DD format
- All times should be in HH:MM format (24-hour)
- Passwords must be at least 6 characters
