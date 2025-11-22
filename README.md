# 📦 StockMaster - Inventory Management System

A modern, full-stack inventory management system built for the ODOO SPIT Hackathon 2025.

## 🌟 Features

- **User Authentication** - Secure login/signup with password hashing
- **Dashboard Analytics** - Real-time KPIs and stock insights
- **Product Management** - Complete CRUD operations for inventory
- **Stock Receipts** - Track incoming stock with automated quantity updates
- **Stock Deliveries** - Manage outgoing stock with validation
- **Low Stock Alerts** - Automatic notifications for products below threshold
- **Responsive Design** - Beautiful gradient UI with glassmorphism effects

## 🛠️ Tech Stack

**Frontend:**
- HTML5
- CSS3 (Custom gradient theme)
- Vanilla JavaScript (ES6+)

**Backend:**
- PHP 8+
- MySQL 8+

**Tools:**
- XAMPP (Apache + MySQL)
- Git for version control

## 📁 Project Structure
odoo-spit-25-stockmaster/
├── backend/
│ └── api/
│ ├── auth.php # Authentication endpoints
│ ├── dashboard.php # Dashboard KPIs
│ ├── products.php # Product CRUD
│ ├── receipts.php # Stock receipt management
│ ├── deliveries.php # Stock delivery management
│ └── low_stock.php # Low stock alerts
├── frontend/
│ ├── css/
│ ├── js/
│ ├── index.html # Dashboard page
│ ├── login.html # Login page
│ ├── signup.html # Signup page
│ ├── products.html # Products management
│ ├── receipts.html # Receipts page
│ └── deliveries.html # Deliveries page
├── db.php # Database connection
├── init.sql # Database initialization
├── .gitignore
└── README.md


