-- Clinic Reservation System Database
-- Created: June 12, 2025
-- Version: 2.0

-- Create database
CREATE DATABASE IF NOT EXISTS clinic_reservation_system;
USE clinic_reservation_system;

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    birthday DATE NOT NULL,
    sex ENUM('Male', 'Female') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'admin', 'doctor') DEFAULT 'patient',
    phone VARCHAR(20),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    medical_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Create services table
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    duration_minutes INT DEFAULT 30,
    price DECIMAL(10,2) DEFAULT 0.00,
    available BOOLEAN DEFAULT TRUE,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create appointments table (renamed from reservations for clarity)
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    notes TEXT,
    symptoms TEXT,
    diagnosis TEXT,
    treatment TEXT,
    prescription TEXT,
    doctor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_appointments_user ON appointments(user_id);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

-- Insert default services
INSERT INTO services (name, description, duration_minutes, price, category) VALUES
('General Consultation', 'General health checkup and consultation', 30, 50.00, 'General'),
('Dental Check-Up', 'Comprehensive dental examination and cleaning', 45, 75.00, 'Dental'),
('Pediatric Consultation', 'Specialized care for children and infants', 30, 60.00, 'Pediatrics'),
('Dermatology Consultation', 'Skin, hair, and nail examination', 30, 80.00, 'Dermatology'),
('Cardiology Consultation', 'Heart and cardiovascular system examination', 45, 120.00, 'Cardiology'),
('Orthopedic Consultation', 'Bone, joint, and muscle examination', 30, 100.00, 'Orthopedics'),
('Eye Examination', 'Comprehensive eye and vision testing', 30, 70.00, 'Ophthalmology'),
('Blood Test', 'Complete blood count and basic metabolic panel', 15, 40.00, 'Laboratory'),
('X-Ray', 'Digital X-ray imaging service', 20, 60.00, 'Radiology'),
('Vaccination', 'Immunization and vaccination services', 15, 25.00, 'Preventive');

-- Insert default admin user (password: admin123)
INSERT INTO users (first_name, last_name, birthday, sex, email, username, password, role, phone) VALUES
('System', 'Administrator', '1990-01-01', 'Male', 'admin@clinic.com', 'admin', '$2b$10$8qQ0tPzeZKNwiz1GIGR2wu3DeE5aI91lC21ZhgbpLUaI.UZTm7iPa', 'admin', '+1234567890');

-- Insert sample doctor
INSERT INTO users (first_name, last_name, birthday, sex, email, username, password, role, phone) VALUES
('Dr. Sarah', 'Johnson', '1985-05-15', 'Female', 'dr.johnson@clinic.com', 'dr.johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+1234567891');

-- Insert sample patient for testing
INSERT INTO users (first_name, last_name, birthday, sex, email, username, password, role, phone) VALUES
('John', 'Doe', '1995-03-20', 'Male', 'john.doe@email.com', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+1234567892');

-- Create view for appointment details
CREATE VIEW appointment_details AS
SELECT 
    a.id,
    a.appointment_date,
    a.appointment_time,
    a.status,
    a.notes,
    a.symptoms,
    a.created_at,
    u.first_name as patient_first_name,
    u.last_name as patient_last_name,
    u.email as patient_email,
    u.phone as patient_phone,
    s.name as service_name,
    s.duration_minutes,
    s.price,
    d.first_name as doctor_first_name,
    d.last_name as doctor_last_name
FROM appointments a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN services s ON a.service_id = s.id
LEFT JOIN users d ON a.doctor_id = d.id AND d.role = 'doctor';

-- Show tables created
SHOW TABLES;

-- Display summary
SELECT 'Database setup completed successfully!' as Status;
SELECT COUNT(*) as 'Total Services' FROM services;
SELECT COUNT(*) as 'Total Users' FROM users;
