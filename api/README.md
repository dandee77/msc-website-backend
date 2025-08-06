# MSC Website Backend API

A professional PHP backend API for the MSC (Management Science Club) Student Portal with proper MVC architecture, authentication, and CRUD operations.

## 🏗 Architecture

The API follows a professional PHP structure with separation of concerns:

```
api/
├── config/
│   ├── database.php      # Database connection with singleton pattern
│   └── cors.php          # CORS configuration
├── controllers/
│   ├── AuthController.php        # Authentication operations
│   ├── StudentController.php     # Student management
│   ├── EventController.php       # Event management
│   └── AnnouncementController.php # Announcement management
├── models/
│   ├── Student.php       # Student data model
│   ├── Event.php         # Event data model
│   └── Announcement.php  # Announcement data model
├── middleware/
│   └── AuthMiddleware.php # Authentication & authorization
├── utils/
│   ├── Response.php      # Standardized API responses
│   └── Validator.php     # Input validation utilities
├── routes/
│   ├── auth.php          # Authentication routes
│   ├── students.php      # Student routes
│   ├── events.php        # Event routes
│   └── announcements.php # Announcement routes
├── index.php             # Main API router
└── main.sql              # Database schema
```

## 🗄 Database Schema

### Students Table

- Complete user profile management
- Role-based access (member/officer)
- Auto-generated MSC IDs
- Password hashing with PHP password_hash()

### Events Table

- Comprehensive event management
- Registration tracking
- Attendance management
- Multiple event types (onsite/online/hybrid)

### Announcements Table

- Content management
- Archive functionality
- Author tracking

### Additional Tables

- `settings` - System configuration
- `event_registrations` - Event registration tracking
- `password_resets` - Password recovery (ready for implementation)

## 🔌 API Endpoints

### Authentication

```
POST /api/auth/login           # User login
POST /api/auth/register        # User registration
POST /api/auth/logout          # User logout
GET  /api/auth/profile         # Get current user profile
POST /api/auth/change-password # Change password
POST /api/auth/forgot-password # Password recovery
```

### Students

```
GET    /api/students           # Get all students (Officer only)
GET    /api/students/{id}      # Get student by ID
PUT    /api/students/{id}/profile # Update student profile
PUT    /api/students/{id}/toggle-active # Toggle active status (Officer only)
GET    /api/students/dashboard # Get dashboard data
GET    /api/students/search    # Search students (Officer only)
```

### Events

```
GET    /api/events             # Get all events
POST   /api/events             # Create event (Officer only)
GET    /api/events/{id}        # Get event by ID
PUT    /api/events/{id}        # Update event (Officer only)
DELETE /api/events/{id}        # Delete event (Officer only)
GET    /api/events/upcoming    # Get upcoming events
GET    /api/events/calendar    # Get calendar events
POST   /api/events/{id}/register # Register for event
GET    /api/events/{id}/registrations # Get registrations (Officer only)
PUT    /api/events/{id}/attendance/{student_id} # Update attendance
```

### Announcements

```
GET    /api/announcements      # Get all announcements
POST   /api/announcements      # Create announcement (Officer only)
GET    /api/announcements/{id} # Get announcement by ID
PUT    /api/announcements/{id} # Update announcement (Officer only)
DELETE /api/announcements/{id} # Delete announcement (Officer only)
PUT    /api/announcements/{id}/archive # Archive announcement
PUT    /api/announcements/{id}/unarchive # Unarchive announcement
GET    /api/announcements/recent # Get recent announcements
GET    /api/announcements/search # Search announcements
```

## 🔐 Authentication & Authorization

### Session-Based Authentication

- Secure session management
- Role-based access control (member/officer)
- Password hashing with bcrypt

### Authorization Levels

- **Public**: Some endpoints accessible without authentication
- **Member**: Authenticated users
- **Officer**: Admin-level access for management operations

## 📋 Features

### Professional Standards

- ✅ MVC Architecture
- ✅ Singleton Database Pattern
- ✅ Standardized API Responses
- ✅ Comprehensive Input Validation
- ✅ Error Handling & Logging
- ✅ CORS Configuration
- ✅ Security Best Practices

### Validation & Security

- Input sanitization
- SQL injection prevention with prepared statements
- Password strength validation
- Email format validation
- Date/time format validation
- Enum validation for restricted values

### Error Handling

- Standardized error responses
- HTTP status codes
- Detailed validation errors
- Global exception handling

## 🚀 Getting Started

### 1. Database Setup

```sql
-- Import the main.sql file to create the database structure
mysql -u username -p < api/main.sql
```

### 2. Configuration

Update database credentials in `config/database.php`:

```php
private $host = "localhost";
private $dbname = "student_portal";
private $username = "root";
private $password = "";
```

### 3. CORS Setup

Update allowed origins in `config/cors.php`:

```php
$allowedOrigins = [
    'http://localhost:3000',
    'http://localhost:5173',
    'http://localhost:8080'
];
```

### 4. API Testing

Test the health endpoint:

```
GET /api/health
```

Expected response:

```json
{
  "success": true,
  "message": "API is running",
  "version": "1.0.0",
  "timestamp": "2025-01-06 12:00:00"
}
```

## 🔄 Backward Compatibility

All existing API endpoints have been updated to use the new architecture while maintaining backward compatibility:

- `login.php` → `AuthController::login()`
- `register.php` → `AuthController::register()`
- `get_profile.php` → `AuthController::getProfile()`
- `create_event.php` → `EventController::create()`
- `create_announcement.php` → `AnnouncementController::create()`

## 📝 Response Format

All API responses follow a standardized format:

### Success Response

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {...},
    "timestamp": "2025-01-06 12:00:00"
}
```

### Error Response

```json
{
    "success": false,
    "message": "Error description",
    "errors": {...},
    "timestamp": "2025-01-06 12:00:00"
}
```

### Validation Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": "Error message",
    "another_field": ["Multiple", "error", "messages"]
  },
  "timestamp": "2025-01-06 12:00:00"
}
```

## 🛠 Development Guidelines

### Adding New Endpoints

1. Create method in appropriate Controller
2. Add route in corresponding route file
3. Implement validation using Validator utility
4. Use Response utility for consistent responses
5. Add authentication/authorization as needed

### Database Operations

1. Use the singleton Database class
2. Always use prepared statements
3. Implement proper error handling
4. Follow the Model pattern for data operations

### Security Considerations

- Always validate and sanitize input
- Use AuthMiddleware for protected endpoints
- Implement proper error messages (don't reveal sensitive info)
- Keep database credentials secure
- Use HTTPS in production

## 📊 API Usage Examples

### Register a New Student

```bash
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "SecurePass123",
    "first_name": "John",
    "last_name": "Doe",
    "birthdate": "2000-01-01",
    "gender": "Male",
    "student_no": "2021-12345",
    "year_level": "4th Year",
    "college": "College of Engineering",
    "program": "Computer Science"
  }'
```

### Login

```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "password": "SecurePass123"
  }'
```

### Create Event (Officer Only)

```bash
curl -X POST http://localhost/api/events \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{
    "event_name": "MSC General Assembly",
    "event_date": "2025-02-15",
    "event_time_start": "14:00",
    "event_time_end": "17:00",
    "location": "Engineering Auditorium",
    "event_type": "onsite",
    "description": "Annual general assembly meeting",
    "registration_required": true
  }'
```

## 🔧 Troubleshooting

### Common Issues

1. **Database Connection Failed**: Check credentials in `config/database.php`
2. **CORS Errors**: Update allowed origins in `config/cors.php`
3. **Session Issues**: Ensure session cookies are enabled
4. **File Permissions**: Ensure PHP has read/write access to files

### Debug Mode

Enable error reporting in `index.php` for development:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Remember to disable in production!

## 📈 Future Enhancements

- JWT token authentication
- File upload handling
- Email notifications
- API rate limiting
- Comprehensive logging
- API documentation with Swagger
- Unit testing
- Database migrations

---

## 📄 License

This project is part of the MSC Student Portal system. Please follow your organization's guidelines for usage and distribution.
