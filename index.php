<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Welcome to Aurora Hotel</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    * {
      margin: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Roboto', sans-serif;
      display: flex;
      background: #f4f6f9;
      color: #232946;
      min-height: 100vh;
    }
    .sidebar {
      width: 230px;
      background: #232946;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding-top: 32px;
    }
    .sidebar .logo {
      font-size: 1.6rem;
      font-weight: 700;
      padding: 0 24px 24px 32px;
      color: #eebbc3;
    }
    .sidebar nav {
      flex: 1;
    }
    .sidebar nav a {
      display: flex;
      align-items: center;
      padding: 14px 32px;
      color: #fff;
      text-decoration: none;
      font-size: 1.05rem;
      transition: background 0.15s;
      border-left: 4px solid transparent;
    }
    .sidebar nav a.active,
    .sidebar nav a:hover {
      background: #393e6a;
      border-left: 4px solid #eebbc3;
      color: #eebbc3;
    }
    .sidebar nav i {
      margin-right: 16px;
      font-size: 1.2rem;
    }

    .main-content {
      flex: 1;
      padding: 40px;
    }
    .hero {
      background: url('uploads/Generate a background of a hotel for my siteweb _QA-hotel_.jpg') no-repeat center/cover;
      height: 55vh;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      text-shadow: 2px 2px 6px rgba(0,0,0,0.6);
      margin-bottom: 40px;
    }
    .hero h1 {
      font-size: 3rem;
      max-width: 800px;
    }
    .content {
      max-width: 800px;
      margin: auto;
      text-align: center;
    }
    .content h2 {
      font-size: 2rem;
      margin-bottom: 20px;
    }
    .content p {
      font-size: 1.1rem;
      color: #555;
      line-height: 1.6;
    }
    .btn {
      display: inline-block;
      margin-top: 28px;
      background: #eebbc3;
      color: #232946;
      padding: 14px 32px;
      font-weight: 600;
      font-size: 1rem;
      border-radius: 8px;
      text-decoration: none;
      transition: background 0.25s;
    }
    .btn:hover {
      background: #f6c7d1;
    }
    @media (max-width: 768px) {
      .sidebar {
        width: 60px;
      }
      .sidebar .logo {
        font-size: 1rem;
        padding: 0 10px;
      }
      .sidebar nav a span {
        display: none;
      }
      .main-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<div class="sidebar">
    <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Customer</span></div>
    <nav>
        <a href="index.php" class="active"><i class="fa-solid fa-house"></i> <span>Home</span></a>
        <a href="rooms.php"><i class="fa-solid fa-bed"></i> <span>Available Rooms</span></a>
        <a href="my_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>My Bookings</span></a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        <?php else: ?>
            <a href="one.php"><i class="fa-solid fa-sign-in-alt"></i> <span>Login</span></a>
        <?php endif; ?>
    </nav>
</div>

<div class="main-content">
  <div class="hero">
    <h1>Experience Tranquility, Comfort, and Class at Aurora Hotel</h1>
  </div>

  <div class="content">
    <h2>Welcome to Aurora Hotel</h2>
    <p>
      Nestled in the heart of the city, Aurora Hotel offers the perfect blend of luxury and modern comfort. Whether you're staying for business or leisure, you'll enjoy our spacious rooms, exceptional service, and unforgettable hospitality. Let us be your home away from home.
    </p>
    <a href="rooms.php" class="btn">Explore Rooms</a>
  </div>
</div>

</body>
</html>
