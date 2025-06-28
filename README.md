# 🎫 Ticket Booking Platform

A complete PHP-based ticket booking system with modern web technologies. This platform allows users to browse events, purchase tickets, and receive digital tickets via email with PDF generation.

## ✨ Features

- **🎯 Event Management** - Browse and manage sports matches and events
- **🛒 Shopping Cart** - Add tickets to cart with real-time price calculation
- **💳 Payment Processing** - Secure payment handling with transaction management
- **📄 PDF Generation** - Automatic ticket generation with QR codes
- **📧 Email Notifications** - Send tickets via email with SendGrid integration
- **👥 User Management** - User registration and order history
- **🎨 Modern UI** - Responsive design with beautiful animations
- **🔒 Security** - SQL injection prevention, input validation, and secure practices

## 🏗️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **PDF Generation**: TCPDF
- **Email Service**: SendGrid
- **Dependencies**: Composer

## 📁 Project Structure

```
Soccer_Ticket_Booking/
├── Final/
│   ├── config.php              # Application configuration
│   ├── database.php            # Database connection class
│   ├── payment.php             # Payment processing API
│   ├── mail_service.php        # Email service integration
│   ├── TicketGenerator.php     # PDF ticket generation
│   ├── index.html              # Main application interface
│   ├── js/
│   │   └── payment.js          # Frontend payment handling
│   ├── tickets/                # Generated ticket PDFs
│   └── vendor/                 # Composer dependencies
├── ticket_platform.sql         # Database schema
```

## 🚀 Quick Start

### Prerequisites

- PHP 8.0 or higher
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)
- SendGrid account (for email functionality)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/ticket-booking-platform.git
   cd ticket-booking-platform
   ```

2. **Set up the database**
   ```bash
   # Import the database schema
   mysql -u root -p < ticket_platform.sql
   ```

3. **Configure the application**
   ```bash
   # Copy and edit the configuration file
   cp Final/config.php.example Final/config.php
   ```
   
   Update the database credentials and API keys in `Final/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ticket_platform');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   
   // SendGrid configuration
   define('SENDGRID_API_KEY', 'your_sendgrid_api_key');
   define('SENDGRID_FROM_EMAIL', 'your-email@domain.com');
   ```

4. **Install dependencies**
   ```bash
   cd Final
   composer install
   ```

5. **Set up your web server**
   - Point your web server to the `Final/` directory
   - Ensure PHP has write permissions for the `tickets/` directory

6. **Access the application**
   - Open your browser and navigate to the application URL
   - The system should be ready to use!

## 🗄️ Database Schema

The application uses a relational database with the following main tables:

- **`users`** - User accounts and information
- **`matches`** - Event/match details
- **`orders`** - Order tracking and payment status
- **`tickets`** - Individual ticket records
- **`sections`** - Stadium seating sections
- **`payments`** - Payment transaction records

## 🔧 Configuration

### Environment Variables

The application supports environment variables for sensitive configuration:

```bash
# Database
DB_HOST=localhost
DB_NAME=ticket_platform
DB_USER=root
DB_PASS=

# SendGrid
SENDGRID_API_KEY=your_api_key_here
SENDGRID_FROM_EMAIL=your-email@domain.com

# Application
APP_URL=http://localhost/Soccer_Ticket_Booking
DEBUG_MODE=false
```

### API Configuration

The system includes a RESTful API for payment processing:

- **Endpoint**: `payment.php`
- **Method**: POST
- **Content-Type**: application/json
- **CORS**: Enabled for cross-origin requests

## 🎨 Features in Detail

### Event Browsing
- View upcoming matches and events
- Filter by date, teams, and availability
- Real-time seat availability checking

### Ticket Purchase
- Add tickets to shopping cart
- Select seating sections and categories
- Real-time price calculation
- Secure payment processing

### Digital Tickets
- Automatic PDF generation with TCPDF
- QR code integration for validation
- Professional ticket design
- Email delivery via SendGrid

### User Experience
- Responsive design for all devices
- Modern UI with smooth animations
- Intuitive navigation
- Real-time feedback and notifications

## 🔒 Security Features

- **SQL Injection Prevention** - Prepared statements throughout
- **Input Validation** - Comprehensive data sanitization
- **Error Handling** - Secure error messages without information leakage
- **Transaction Management** - ACID compliance for data integrity
- **Environment Configuration** - Secure API key management

## 📧 Email Integration

The system uses SendGrid for reliable email delivery:

- **HTML Email Templates** - Professional ticket confirmations
- **PDF Attachments** - Automatic ticket delivery
- **Delivery Tracking** - SendGrid analytics and monitoring
- **Fallback Handling** - Error recovery for failed deliveries

## 🛠️ Development

### Code Structure

The application follows modern PHP practices:

- **Object-Oriented Design** - Clean class structure
- **Separation of Concerns** - Modular architecture
- **Error Handling** - Comprehensive exception management
- **Logging** - Debug and error logging system

### Key Classes

- `Database` - Database connection and management
- `MailService` - Email functionality
- `TicketGenerator` - PDF generation
- Payment API - RESTful payment processing

## 📚 Learning Resources

This project serves as an excellent learning resource for:

- **Backend Development** - Complete PHP application architecture
- **Database Design** - Relational database modeling
- **API Development** - RESTful API implementation
- **Security Best Practices** - Web application security
- **Integration** - Third-party service integration



## 🤝 Contributing

We welcome contributions! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

### Development Setup

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🚀 Deployment

### Production Checklist

- [ ] Set `DEBUG_MODE` to `false`
- [ ] Configure production database credentials
- [ ] Set up SendGrid API key
- [ ] Ensure proper file permissions
- [ ] Configure web server security
- [ ] Set up SSL certificate
- [ ] Configure backup strategy

### Performance Optimization

- Enable PHP OPcache
- Configure MySQL query cache
- Use CDN for static assets
- Implement caching strategies
- Monitor application performance

---

**Built with ❤️ for learning and development**

*This project demonstrates real-world backend development concepts and serves as a comprehensive learning resource for PHP developers.*
