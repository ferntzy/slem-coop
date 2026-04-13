# COOP Management Information System

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2-blue)
![Filament](https://img.shields.io/badge/Admin-Filament-orange)
![License](https://img.shields.io/badge/license-MIT-green)

A **Laravel-based application** built using **Filament** with **Filament Shield** for role and permission management.

---

# Features

- Laravel backend framework
- Filament admin panel
- Filament Shield role & permission management
- Database migrations
- Frontend asset compilation with Node.js

---

# Requirements

Make sure the following are installed on your system:

- PHP **8.2+**
- Composer
- Node.js
- npm
- MySQL or MariaDB

---

# Installation

## 1. Clone the Repository

```bash
git clone https://github.com/Rinvee-git/coop_management
cd coop_management
```

---

## 2. Install PHP Dependencies

```bash
composer install
```

---

## 3. Setup Environment

Copy the example environment file and generate the application key.

```bash
cp .env.example .env
php artisan key:generate
```

---

## 4. Install Frontend Dependencies

```bash
npm install
npm run build
```

For development mode:

```bash
npm run dev
```

---

## 5. Configure Database

Update the database credentials in your `.env` file.

Example:

```
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations:

```bash
php artisan migrate
```

---

## 6. Run the Application

Start the Laravel development server:

```bash
php artisan serve
```

The application will be available at:

```
http://127.0.0.1:8000
```

---

# Filament Shield Setup

Generate permissions and policies:

```bash
php artisan shield:generate --all
```

Create the super admin account:

```bash
php artisan shield:super-admin
```

---

# Troubleshooting

### Composer Issues

If dependencies fail to install:

```bash
composer update
```

---

### Node Modules Issues

Delete and reinstall node modules:

```bash
rm -rf node_modules
npm install
```

---

# License

This project is open-source and available under the **MIT License**.
