# Aurora Hotel - Online Room Booking System

Welcome to **Aurora Hotel**, a modern and stylish web-based room booking system. This project allows users to explore available rooms, make bookings, and view their reservations, all within a sleek and responsive interface.

## 🌟 Features

- ✅ User Authentication (Login/Logout)
- 🏨 Room Listing Page with Images, Prices, and Descriptions
- 📆 Booking System with Availability Checks
- 👤 My Bookings Page (View your reservations)
- 🖼 Uploadable Room Images (admin-side)
- 📱 Fully Responsive and Mobile-Friendly UI
- 🎨 Dark Theme with Elegant Design and Animations

---

## 📁 Project Structure

/aurora-hotel/
│
├── index.php # Homepage (welcome screen)
├── rooms.php # Room listings with booking buttons
├── book.php # Booking form with validation
├── my_bookings.php # User booking history
│
├── /admin/ # Admin dashboard (optional)
│ ├── admin_rooms.php # Room management
│ ├── admin_bookings.php # View/manage bookings
│
├── /uploads/ # Uploaded room images
├── /assets/ # CSS, JS files
│ ├── style.css
│ └── script.js
│
├── config.php # DB connection via PDO
├── one.php # Login page
├── logout.php # Logout handler
└── database.sql # SQL structure and seed data
