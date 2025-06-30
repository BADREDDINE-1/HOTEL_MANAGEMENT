<?php
session_start();
require '../config.php';

if (!isset($_SESSION['userId'])) {
    header('Location: one.php');
    exit;
}

$userId = $_SESSION['userId'];

$stmt = $pdo->prepare("
    SELECT b.id, b.room_id, b.check_in_date, b.check_out_date, b.status, 
           r.room_number, r.type, r.price
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Bookings - Aurora Hotel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    :root {
      --primary: #34568B;
      --accent: #88CCF1;
      --dark: #121212;
      --light: #f9f9f9;
      --text-light: #ddd;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background-color: var(--dark);
      color: var(--light);
      display: flex;
      flex-direction: column;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--dark);
      color: var(--light);
      min-height: 100vh;
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(12px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      z-index: 1000;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--accent);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    nav {
      display: flex;
      gap: 1.5rem;
    }

    nav a {
      text-decoration: none;
      color: var(--text-light);
      transition: 0.3s;
    }

    nav a:hover,
    nav a.active {
      color: var(--accent);
    }

    .menu-toggle {
      display: none;
      font-size: 1.5rem;
      color: var(--light);
      cursor: pointer;
    }

    @media (max-width: 768px) {
      nav {
        position: absolute;
        top: 70px;
        left: 0;
        right: 0;
        background: #1b1b1b;
        flex-direction: column;
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.3s ease;
      }

      nav.show {
        max-height: 300px;
      }

      nav a {
        padding: 1rem;
        text-align: center;
        border-top: 1px solid #333;
      }

      .menu-toggle {
        display: block;
      }
    }

    main {
        padding: 6rem 2rem 4rem;
        max-width: 1000px;
        margin: auto;
        flex-grow: 1;
        padding: 6rem 2rem 4rem;
        max-width: 1000px;
        margin: auto;
    }

    h1 {
      font-size: 2rem;
      margin-bottom: 2rem;
      text-align: center;
      color: var(--accent);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #1e1e1e;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #333;
    }

    th {
      background-color: var(--primary);
      color: white;
    }

    tr:hover {
      background-color: #2a2a2a;
    }

    .no-data {
      background-color: #222;
      padding: 2rem;
      border-radius: 8px;
      text-align: center;
      color: #bbb;
      margin-top: 2rem;
      font-size: 1.1rem;
    }

    footer {
        background: #0f0f0f;
        text-align: center;
        padding: 1rem;
        color: #666;
        font-size: 0.9rem;
        border-top: 1px solid #222;
    }
  </style>
</head>
<body>

<header>
  <div class="logo"><i class="fa-solid fa-hotel"></i> Aurora</div>
  <div class="menu-toggle" id="menu-toggle"><i class="fa fa-bars"></i></div>
  <nav id="nav-links">
    <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
    <a href="rooms.php"><i class="fa-solid fa-bed"></i> Rooms</a>
    <a href="my_bookings.php" class="active"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
    <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
  </nav>
</header>

<main>
  <h1>My Bookings</h1>
  <?php if (count($bookings) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Room</th>
          <th>Type</th>
          <th>Price (MAD)</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $booking): ?>
          <tr>
            <td><?= $booking['id'] ?></td>
            <td><?= htmlspecialchars($booking['room_number']) ?></td>
            <td><?= htmlspecialchars($booking['type']) ?></td>
            <td><?= number_format($booking['price'], 2) ?></td>
            <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
            <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
            <td><?= htmlspecialchars($booking['status']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="no-data">You have no bookings yet.</div>
  <?php endif; ?>
</main>

<footer>
  &copy; <?= date("Y") ?> Aurora Hotel. All rights reserved.
</footer>

<script>
  const toggleBtn = document.getElementById('menu-toggle');
  const navLinks = document.getElementById('nav-links');

  toggleBtn.addEventListener('click', () => {
    navLinks.classList.toggle('show');
    const icon = toggleBtn.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
  });
</script>

</body>
</html>
