# 🍽️ Self Order Laravel

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-green.svg)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.2-blue.svg)](https://www.typescriptlang.org)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)](https://php.net)

A comprehensive self-ordering system built with Laravel 12 and Vue.js 3 for restaurants, cafes, and food establishments. This system provides a complete solution for customer ordering, kitchen management, payment processing, and administrative oversight.

> **Note**: This project was developed as a comprehensive learning exercise and demonstration of modern web development practices using Laravel and Vue.js. It showcases full-stack development capabilities including real-time features, payment integration, and thermal printing.

## 📸 Screenshots

### Customer Interface
- **Menu Browsing**: Interactive menu with search and filtering
- **Shopping Cart**: Real-time cart management
- **Order Checkout**: Streamlined ordering process

### Admin Dashboard
- **Analytics Dashboard**: Comprehensive sales overview
- **Order Management**: Real-time order tracking
- **Menu Management**: Easy menu item management

### Kasir Interface
- **Order Queue**: Efficient order processing
- **Kitchen Display**: Workflow-based kitchen management
- **Payment Processing**: Quick payment handling

*Screenshots will be added soon...*

## 🌟 Features

### 👥 Customer Interface
- **Modern Menu Browsing**: Interactive menu with categories, search, and filtering
- **Shopping Cart**: Real-time cart management with localStorage persistence
- **Order Placement**: Easy checkout process with customer information
- **Payment Integration**: Multiple payment methods (Cash, QRIS, Bank Transfer, E-Wallet)
- **Order Tracking**: Real-time order status updates
- **Responsive Design**: Mobile-first approach for all devices

### 🏢 Admin Dashboard
- **Analytics Overview**: Comprehensive dashboard with sales metrics and charts
- **Order Management**: Real-time order monitoring and status updates
- **Menu Management**: Full CRUD operations for categories and menu items
- **Payment Processing**: Payment tracking and management
- **User Management**: Role-based access control (Admin, Kasir)
- **Reports & Export**: Sales reports with Excel/PDF export functionality

### 💰 Kasir (Cashier) Interface
- **Order Queue**: Streamlined order management with priority indicators
- **Kitchen Display**: Workflow-based kitchen order management
- **Quick Payments**: Fast payment processing with change calculation
- **Sales Reports**: Real-time sales analytics and reporting
- **Print Integration**: Thermal printer support for receipts

### 🔧 Technical Features
- **Real-time Updates**: WebSocket integration with Pusher for live updates
- **Print System**: Thermal printer integration for customer and kitchen receipts
- **Performance Optimized**: Database indexing, query optimization, and caching
- **Security Hardened**: Input validation, XSS protection, CSRF tokens
- **API Documentation**: Complete Swagger/OpenAPI documentation
- **Testing Suite**: Comprehensive unit, feature, and browser tests

## 🛠️ Tech Stack

### Backend
- **Laravel 12**: PHP framework with modern features
- **MySQL**: Primary database with optimized indexes
- **Redis**: Caching and session storage
- **Pusher**: Real-time WebSocket communication
- **Laravel Sanctum**: API authentication
- **Spatie Laravel Permission**: Role-based access control

### Frontend
- **Vue.js 3.5**: Progressive JavaScript framework
- **TypeScript 5.2**: Type-safe JavaScript development
- **Inertia.js 2.0**: Modern monolith approach
- **Vite 7.0**: Fast build tool and development server
- **Tailwind CSS 4.1**: Utility-first CSS framework
- **Reka UI 2.2**: Modern Vue component library
- **Pinia 3.0**: State management for Vue

### Additional Tools
- **ESC/POS PHP 4.0**: Thermal printer integration
- **DomPDF 3.1**: PDF generation for receipts
- **Intervention Image 3.11**: Image processing and optimization
- **Laravel Echo 2.1**: Frontend WebSocket client
- **L5 Swagger 9.0**: API documentation generation

## 📋 Requirements

- **PHP**: >= 8.2
- **Node.js**: >= 18.0
- **MySQL**: >= 8.0
- **Redis**: >= 6.0 (recommended for caching and sessions)
- **Composer**: >= 2.0
- **NPM**: >= 8.0 or **Yarn**: >= 1.22

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/prassaaa/self-order-laravel
cd self-order-laravel
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Configure database in .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=self_order_laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seeders
php artisan migrate --seed
```

### 5. Storage Setup
```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 6. Build Assets
```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Development Server
```bash
# Start Laravel server
php artisan serve

# Start Vite dev server (in another terminal)
npm run dev
```

## ⚙️ Configuration

### Environment Variables
```env
# Application
APP_NAME="UMKM Restaurant"
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=self_order_laravel
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Real-time (Pusher)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=ap1

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Printing
CUSTOMER_PRINTER_ENABLED=false
CUSTOMER_PRINTER_TYPE=network
CUSTOMER_PRINTER_HOST=192.168.1.100
CUSTOMER_PRINTER_PORT=9100

KITCHEN_PRINTER_ENABLED=false
KITCHEN_PRINTER_TYPE=network
KITCHEN_PRINTER_HOST=192.168.1.101
KITCHEN_PRINTER_PORT=9100
```

### Printer Configuration
The system supports thermal printers via network or USB connection. Configure printer settings in the `.env` file or `config/printing.php`.

### Real-time Features
Configure Pusher credentials for real-time order updates, kitchen notifications, and payment confirmations.

## 👤 Default Users

After running seeders, you can login with:

### Admin User
- **Email**: admin@example.com
- **Password**: password
- **Role**: Admin (full access)

### Kasir User
- **Email**: kasir@example.com
- **Password**: password
- **Role**: Kasir (cashier access)

## 📱 Usage

### Customer Flow
1. Browse menu by categories
2. Add items to cart with customizations
3. Proceed to checkout
4. Enter customer information
5. Select payment method
6. Place order and receive confirmation
7. Track order status in real-time

### Admin Flow
1. Login to admin dashboard
2. Monitor orders and analytics
3. Manage menu items and categories
4. Process payments and refunds
5. Generate sales reports
6. Manage user accounts

### Kasir Flow
1. Login to kasir interface
2. View order queue and kitchen display
3. Process payments quickly
4. Print customer receipts
5. Monitor kitchen workflow
6. View sales reports

## 🔧 API Documentation

The system provides a comprehensive REST API. Access the interactive API documentation at:
- **Swagger UI**: `/api/documentation`
- **OpenAPI JSON**: `/api/documentation.json`

### Key Endpoints
- `GET /api/menus` - Get menu items
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details
- `POST /api/payments` - Process payment
- `GET /api/dashboard/stats` - Get dashboard statistics

## 🧪 Testing

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run browser tests
php artisan dusk
```

### Test Categories
- **Unit Tests**: Model and service testing
- **Feature Tests**: API endpoint testing
- **Browser Tests**: Frontend functionality testing
- **Integration Tests**: Payment and printing testing

## 🚀 Deployment

### Production Setup
1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database and cache
3. Set up SSL certificate
4. Configure web server (Nginx/Apache)
5. Set up process manager (Supervisor)
6. Configure backup strategy

### Performance Optimization
- Enable OPcache for PHP
- Use Redis for caching and sessions
- Configure CDN for static assets
- Set up database read replicas
- Enable gzip compression

## 🔒 Security

### Security Features
- Input validation and sanitization
- SQL injection prevention
- XSS protection with CSP headers
- CSRF token validation
- Rate limiting on API endpoints
- Secure file upload handling
- Password hashing with bcrypt
- Session security configuration

### Security Checklist
- [ ] Update all dependencies regularly
- [ ] Use HTTPS in production
- [ ] Configure firewall rules
- [ ] Set up intrusion detection
- [ ] Regular security audits
- [ ] Backup encryption
- [ ] Access logging and monitoring

## 📊 Monitoring

### Performance Monitoring
```bash
# Check application performance
php artisan performance:monitor

# Generate performance report
php artisan performance:monitor --report
```

### Log Monitoring
- Application logs: `storage/logs/laravel.log`
- Performance logs: `storage/logs/performance.log`
- Security logs: `storage/logs/security.log`

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines on:
- Development setup
- Coding standards
- Testing requirements
- Pull request process

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📋 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed history of changes and version releases.

## 🆘 Support

### Documentation
- [API Documentation](docs/api.md)
- [Database Schema](docs/database.md)
- [Deployment Guide](docs/deployment.md)
- [Troubleshooting](docs/troubleshooting.md)

### Contact
- **Developer**: Prasetyo Ari Wibowo
- **Email**: pras.ari69@gmail.com
- **GitHub**: [@prassaaa](https://github.com/prassaaa)
- **Issues**: [GitHub Issues](https://github.com/prassaaa/self-order-laravel/issues)
- **Discussions**: [GitHub Discussions](https://github.com/prassaaa/self-order-laravel/discussions)

---

Made with ❤️ for the restaurant industry
