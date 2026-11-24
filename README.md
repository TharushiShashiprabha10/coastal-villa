# Dynamic Web-Based Coastal Villa and Room Booking System

## 1. Project Overview
This project is a dynamic web-based room booking and management application designed for a coastal villa in Sri Lanka (Mirissa/Weligama/Tangalle style) using HTML5, CSS3, JavaScript, PHP (PDO), and MySQL. Visitors can explore the villa, search available rooms by dates and guest count, submit booking requests, and view/cancel pending bookings, while the administrator manages rooms and reservation statuses.

## 2. Implemented Features
- **Responsive Home Page (`index.php`):** Displays villa introduction, coastal location highlights, facilities, featured rooms, and navigation.
- **Rooms & Availability Search (`rooms.php`):** Lists available rooms with pricing, facilities, and occupancy, and filters rooms by check-in/check-out dates and guest count.
- **Guest Registration & Authentication (`register.php`, `login.php`, `logout.php`):** Uses `password_hash()`, `password_verify()`, and PHP sessions with `guest` and `admin` roles.
- **Booking Request & Dynamic Cost Calculation (`booking.php`):** Uses JavaScript for live night and total price calculation and date validation, paired with server-side validation and overlap checks against `Confirmed` bookings.
- **My Bookings (`my_bookings.php`):** Shows a logged-in guest's bookings (`Pending`, `Confirmed`, `Cancelled`) and allows cancelling `Pending` bookings.
- **Administrator Panel (`admin/dashboard.php`, `admin/rooms.php`, `admin/bookings.php`):** Provides room CRUD/status management, booking statistics, and booking confirmation/cancellation with overlap prevention.

## 3. Software Requirements
- **Local Server Stack:** XAMPP (Apache + MySQL/MariaDB + PHP 8.x)
- **Database Tool:** phpMyAdmin or MySQL CLI
- **Web Browser:** Google Chrome, Microsoft Edge, or Mozilla Firefox
- **Code Editor:** Google Antigravity / VS Code / Notepad

## 4. Installation and Configuration Instructions
1. Start **Apache** and **MySQL** in the **XAMPP Control Panel**.
2. Place the project folder at `C:\xampp\htdocs\coastal_villa\`.
3. Open `http://localhost/phpmyadmin/`, create a database named `coastal_villa_db`, and import `db_structure.sql`.
4. Verify database credentials in `config/db.php` (`host=127.0.0.1`, `dbname=coastal_villa_db`, `username=root`, `password=''`).
5. Open `http://localhost/coastal_villa/` in a web browser.

## 5. Sample Login Credentials
- **Administrator:**
  - **Email:** `admin@serenecoastvilla.lk`
  - **Password:** `Password123` *(or a guest account promoted to `admin` in the `users` table)*
- **Guest:**
  - **Email:** `nethmi.guest@example.com`
  - **Password:** `Password123` *(or register a new guest account via `register.php`)*

## 6. AI & External Resources Declaration
This project was developed individually for the assignment. Generative AI assistance (Google Antigravity / Gemini) was used to help structure code templates, database schemas, and troubleshooting steps during development, and all code was reviewed and tested locally.