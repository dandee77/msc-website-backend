-- Create database
CREATE DATABASE IF NOT EXISTS student_portal;
USE student_portal;

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    msc_id VARCHAR(32) UNIQUE,
    role ENUM('member', 'officer') DEFAULT 'member', 

    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    last_name VARCHAR(100),
    name_suffix VARCHAR(20),
    birthdate DATE,
    gender ENUM('Male', 'Female', 'Other'),

    student_no VARCHAR(50),
    year_level VARCHAR(20),
    college VARCHAR(100),
    program VARCHAR(100),
    section VARCHAR(50),
    address TEXT,
    phone VARCHAR(20),
    facebook_link VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    profile_image_path TEXT,
    is_active BOOLEAN,
    officer_role VARCHAR(20)
);

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_time_start TIME,
    event_time_end TIME,
    location TEXT,
    event_type ENUM('onsite', 'online', 'hybrid') DEFAULT 'onsite',
    registration_required BOOLEAN DEFAULT FALSE,
    event_status ENUM('upcoming', 'canceled', 'completed') DEFAULT 'upcoming',
    description TEXT,
    event_image_url TEXT,
    event_batch_image TEXT,
    attendants INT DEFAULT 0,
    event_restriction ENUM('public', 'members', 'officers') DEFAULT 'public',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
    posted_by VARCHAR(100) DEFAULT 'Admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_archived BOOLEAN DEFAULT FALSE
);

-- Create settings table for system configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create event_registrations table for tracking registrations
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('registered', 'attended', 'absent') DEFAULT 'registered',
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, student_id)
);

-- Create password_resets table for password recovery
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (key_name, value, description) VALUES
('school_year_code', '2526', 'Current school year code for ID generation'),
('system_name', 'MSC Student Portal', 'Name of the system'),
('admin_email', 'admin@example.com', 'Administrator email address')
ON DUPLICATE KEY UPDATE value = VALUES(value);
