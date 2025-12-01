# ☕ Cafe Sales Management System

A comprehensive cafe management system built with Laravel for tracking sales, managing inventory, monitoring KPIs, and coordinating operations across multiple branches.

## 📋 Overview

This system is designed for cafe businesses with multiple branches, providing role-based access for HQ Administrators, Branch Managers, and Staff members. It streamlines daily operations from sales recording to performance tracking.

## ✨ Features

### 🏢 Multi-Role Access
- **HQ Admin** - Full system control, benchmark setting, reporting across all branches
- **Branch Manager** - Branch oversight, staff KPI monitoring, local reporting
- **Staff** - Sales submission, inventory updates, personal KPI tracking

### 💰 Sales Management
- Record daily sales transactions with multiple items
- Support for various payment methods (Cash, Card, E-Wallet, Bank Transfer)
- Automatic stock deduction on sales
- Transaction history and reporting

### 📊 KPI & Performance Tracking
- HQ-defined benchmarks (monthly sales target, transaction target, staff sales target)
- Branch-specific KPI assignments
- Real-time progress tracking with visual charts
- Staff performance comparison

### 📦 Inventory Management
- Product catalog with categories
- Stock quantity tracking with +/- adjustments
- Low stock alerts (< 10 units)
- Stock change logging

### 📈 Reporting & Analytics
- Sales summary (daily, weekly, monthly)
- Category-based sales pie charts
- CSV and PDF export functionality
- Branch comparison reports

### 🔔 Notifications
- Low stock alerts in navigation dropdown
- KPI target reminders
- Real-time notification badges

## 🛠️ Tech Stack

- **Backend:** Laravel 12.x (PHP 8.4)
- **Frontend:** Blade Templates, Bootstrap 5.3
- **Database:** pgSQL
- **Charts:** Chart.js 4.4
- **PDF Generation:** barryvdh/laravel-dompdf
- **Build Tool:** Vite

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # HQ Admin, Branch Manager, Staff controllers
│   ├── Models/               # Eloquent models (User, Branch, Product, DailySale, KPI, etc.)
│   └── Providers/            # Service providers (View Composer for notifications)
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Sample data seeders
├── resources/views/
│   ├── dashboards/           # Role-specific dashboards
│   ├── hq-admin/             # HQ Admin views (reports, benchmarks, etc.)
│   ├── branch-manager/       # Branch Manager views
│   ├── staff/                # Staff views (sales, inventory, KPI)
│   └── layouts/              # Layout templates
└── routes/
    ├── web.php               # Main routes
    └── auth.php              # Authentication routes
```

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/amarylleez/cafe-sales-system.git
   cd cafe-sales-system
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** in `.env`
   ```
   DB_DATABASE=cafe_sales
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets and start server**
   ```bash
   npm run build
   php artisan serve
   ```

## 👥 Default Users

After seeding, you can login with:

| Role | Email | Password |
|------|-------|----------|
| HQ Admin | admin@cafe.com | password |
| Branch Manager | manager@cafe.com | password |
| Staff | staff@cafe.com | password |

## 📸 Key Pages

- **Staff Dashboard** - Sales summary, category pie chart, quick actions
- **Submit Sales** - Add items, apply discounts, select payment method
- **My KPI** - Personal targets, progress bars, branch performance
- **Inventory** - Product list with stock controls
- **HQ Reports** - Branch comparison, export to CSV/PDF

## 📄 License

This project is developed as a Final Year Project (FYP).

---

Built with ❤️ using [Laravel](https://laravel.com)
