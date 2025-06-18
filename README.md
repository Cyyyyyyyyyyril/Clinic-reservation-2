<<<<<<< HEAD
# Clinic-reservation-2
=======
# Clinic Reservation System
## Modern Medical Appointment Management System

### Version 2.0 - Restructured & Enhanced

---

## 🚀 Quick Setup Guide

### Prerequisites
- XAMPP/WAMP/LAMP server with PHP 7.4+ and MySQL 5.7+
- Web browser (Chrome, Firefox, Safari, Edge)

### Installation Steps

1. **Place files in web server directory**
   ```
   Copy the entire project to: c:\xampp\htdocs\Clinic-reservation-2\
   ```

2. **Import Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database or import the SQL file
   - Navigate to: `database/clinic_reservation_system.sql`
   - Import this file to create all tables and sample data

3. **Configure Database Connection**
   - File: `config/db.php`
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: clinic_reservation_system

4. **Access the System**
   - URL: http://localhost/Clinic-reservation-2/public/
   - Will redirect to login page

---

## 👥 Demo Accounts

### Admin Account
   - URL: http://localhost/Clinic-reservation-2/admin/

- **Username:** admin
- **Password:** admin123
- **Access:** Full system management

### Doctor Account
- **Username:** dr.johnson
- **Password:** admin123
- **Access:** Patient management, appointments

### Patient Account
- **Username:** johndoe
- **Password:** admin123
- **Access:** Book appointments, view history

---

## 🏗️ Project Structure

```
Clinic-reservation-2/
├── public/                     # Web accessible files
│   ├── index.php              # Main appointment booking
│   ├── login.php              # User authentication
│   ├── register.php           # User registration
│   ├── dashboard.php          # User dashboard
│   ├── admin_dashboard.php    # Admin panel
│   ├── submit_appointment.php # Form handler
│   ├── logout.php             # Session termination
│   ├── book_appointment.php   # Legacy redirect
│   └── style.css              # Modern CSS framework
├── config/                     # Configuration files
│   ├── db.php                 # Database connection & helpers
│   └── sql                    # Legacy SQL (deprecated)
└── database/                   # Database files
    └── clinic_reservation_system.sql  # Complete DB structure
```

---

## 🎨 Features

### ✅ Completed Features
- **Modern Responsive Design** - Mobile-first CSS framework
- **User Authentication** - Secure login/registration system
- **Role-Based Access** - Admin, Doctor, Patient roles
- **Appointment Management** - Book, view, manage appointments
- **Service Management** - Predefined medical services
- **Dashboard Analytics** - Statistics and quick actions
- **Form Validation** - Client-side and server-side validation
- **Security Features** - SQL injection prevention, password hashing
- **Database Relations** - Proper foreign keys and indexes

### 🔧 Technical Improvements
- **Structured Database** - Normalized tables with proper relationships
- **Helper Functions** - Reusable PHP functions for common tasks
- **Error Handling** - Proper exception handling and logging
- **Session Management** - Secure session handling
- **Input Sanitization** - Protection against XSS and SQL injection
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Performance Optimized** - Indexed database queries

---

## 📊 Database Schema

### Users Table
- Personal information (name, birthday, contact)
- Authentication (username, password)
- Role management (patient, doctor, admin)
- Account status tracking

### Appointments Table
- Complete appointment details
- Service association
- Status tracking (pending, confirmed, completed, cancelled)
- Medical notes and symptoms
- Audit trail with timestamps

### Services Table
- Medical service catalog
- Pricing and duration
- Category organization
- Availability status

---

## 🛡️ Security Features

1. **Password Security**
   - BCrypt hashing
   - Minimum length requirements
   - Confirmation validation

2. **SQL Injection Prevention**
   - Prepared statements
   - Input sanitization
   - Parameter binding

3. **XSS Protection**
   - Output escaping
   - HTML special characters handling
   - Content Security Policy ready

4. **Session Security**
   - Secure session management
   - Automatic logout
   - Role-based access control

---

## 📱 Responsive Design

The system is fully responsive and works seamlessly on:
- **Desktop** - Full featured experience
- **Tablet** - Optimized touch interface
- **Mobile** - Streamlined mobile experience

---

## 🎯 Usage Instructions

### For Patients
1. Register new account or login
2. Book appointments by selecting service, date, and time
3. View appointment history and status
4. Manage personal information

### For Doctors
1. Login with doctor credentials
2. View assigned appointments
3. Update appointment status
4. Add medical notes and prescriptions

### For Administrators
1. Access admin dashboard
2. Manage all appointments
3. Add/edit users and services
4. View system statistics and reports

---

## 🔄 Update Instructions

To update from the old system:
1. Backup existing data
2. Import new database structure
3. Migrate existing appointment data
4. Update file paths in configuration

---

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check MySQL server is running
- Verify database credentials in config/db.php
- Ensure database exists

**Permission Denied**
- Check file permissions
- Ensure web server has read/write access
- Verify user roles are set correctly

**Styling Issues**
- Clear browser cache
- Check CSS file path
- Ensure style.css is accessible

---

## 📞 Support

For technical support or questions:
- Check the troubleshooting section
- Review error logs in your web server
- Ensure all prerequisites are met

---

**System Version:** 2.0  
**Last Updated:** June 12, 2025  
**Compatible with:** PHP 7.4+, MySQL 5.7+, Modern Browsers
>>>>>>> 24829f9 (Initial commit)
