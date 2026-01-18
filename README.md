# 🚗 SmartPark - Smart Parking Management System

A comprehensive web-based parking management solution that streamlines parking space reservations, payments, and administration.

## 📋 Overview

SmartPark is a full-featured parking management system designed to simplify the process of finding, booking, and managing parking spaces. The platform provides an intuitive interface for users to reserve parking spots and includes a robust admin panel for managing operations.

## ✨ Features

### User Features
- **User Authentication**: Secure registration, login, and password recovery
- **Parking Reservation**: Browse and book available parking spots
- **Multiple Payment Methods**: 
  - M-Pesa Integration
  - Equity Bank Payment Gateway
- **User Dashboard**: View reservation history and manage bookings
- **Messaging System**: Internal messaging between users and administrators
- **Newsletter Subscription**: Stay updated with parking availability and news
- **Receipt Generation**: Downloadable receipts for all transactions
- **Profile Management**: Update personal information and preferences
- **Contact Support**: Submit inquiries and support requests

### Admin Features
- **Admin Dashboard**: Comprehensive overview of system operations
- **User Management**: View, edit, and manage user accounts
- **Parking Spot Management**: Add, edit, and delete parking spaces
- **Reservation Management**: Monitor and manage all reservations
- **Payment Processing**: Track and verify M-Pesa and Equity payments
- **Inbox System**: Manage contact messages and inquiries
- **Internal Messaging**: Communicate with users
- **Reports**: Generate analytics and reports on parking usage
- **Payment Status Updates**: Approve or reject payment transactions

## 🛠️ Technology Stack

- **Backend**: PHP 8.2
- **Database**: MySQL/MariaDB
- **Frontend**: 
  - HTML5
  - TailwindCSS
  - JavaScript
- **Email**: PHPMailer
- **Payment Integration**: 
  - M-Pesa API
  - Equity Bank Payment Gateway
- **Server**: Apache (XAMPP)

## 📦 Installation

### Prerequisites
- XAMPP (or similar Apache/MySQL/PHP stack)
- PHP 8.0 or higher
- MySQL/MariaDB
- Composer (optional, for PHPMailer dependencies)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/smartpark.git
   ```

2. **Move to XAMPP directory**
   ```bash
   # Copy the project to your XAMPP htdocs folder
   # Windows: C:\xampp\htdocs\smartpark
   # Linux/Mac: /opt/lampp/htdocs/smartpark
   ```

3. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `smartpark`
   - Import the database schema:
     ```
     Import the file: database/schema.sql
     ```

4. **Configure Database Connection**
   - Update database credentials in `components/function.php`
   - Set your database host, username, password, and database name

5. **Configure Email Settings**
   - Update PHPMailer settings in `components/function_mailer.php`
   - Configure your SMTP credentials for email notifications

6. **Configure Payment Gateways**
   - **M-Pesa**: Update credentials in `user/mpesa/` configuration files
   - **Equity Bank**: Update credentials in equity payment configuration

7. **Start XAMPP**
   - Start Apache and MySQL services
   - Access the application at `http://localhost/smartpark`

## 🚀 Usage

### For Users
1. Register for a new account or login
2. Browse available parking spots
3. Select a spot and make a reservation
4. Complete payment via M-Pesa or Equity Bank
5. Receive confirmation and receipt
6. View your active and past reservations in the dashboard

### For Administrators
1. Login with admin credentials
2. Access admin panel at `/admin/admin_dashboard.php`
3. Manage parking spots, users, and reservations
4. Process and verify payments
5. Respond to user inquiries
6. Generate reports and analytics

## 📁 Project Structure

```
smartpark/
├── admin/                  # Admin panel and management pages
├── assets/                 # Static assets (images, uploads)
├── components/             # Core functions and utilities
├── database/               # Database schema and migrations
├── PHPMailer/             # Email library
├── user/                   # User-facing pages
│   ├── includes/          # User interface components
│   ├── mpesa/             # M-Pesa payment integration
│   └── uploads/           # User uploaded files
├── index.php              # Main entry point
├── login.php              # Login page
├── register.php           # Registration page
└── README.md              # This file
```

## 🔐 Security Features

- Session-based authentication
- Password hashing and encryption
- SQL injection prevention
- XSS protection
- CSRF token implementation
- Secure password reset mechanism
- Input validation and sanitization

## 📧 Email Notifications

The system sends automated emails for:
- Account registration confirmation
- Password reset requests
- Reservation confirmations
- Payment receipts
- Newsletter updates

## 💳 Payment Integration

### M-Pesa
- Real-time payment processing
- Automatic confirmation callbacks
- Transaction verification

### Equity Bank
- Secure payment gateway integration
- Transaction tracking
- Payment status updates

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👥 Authors

- Your Name - Initial work

## 🐛 Known Issues

- Please check the [Issues](https://github.com/yourusername/smartpark/issues) page for known bugs and feature requests

## 📞 Support

For support, email your-email@example.com or create an issue in the GitHub repository.

## 🙏 Acknowledgments

- PHPMailer for email functionality
- TailwindCSS for UI styling
- M-Pesa and Equity Bank for payment gateway integration
- All contributors and users of SmartPark

---

**Note**: Remember to configure your environment variables and API keys before deploying to production. Never commit sensitive credentials to the repository.
