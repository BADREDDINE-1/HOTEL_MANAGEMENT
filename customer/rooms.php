<?php
session_start();
require '../config.php';

if (!isset($_SESSION['userId'])) {
    header('Location: one.php');
    exit;
}

$rooms = [];
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';
$error = '';

if ($checkIn && $checkOut) {
    if ($checkIn >= $checkOut) {
        $error = "Check-out date must be after check-in date.";
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM rooms r
            WHERE r.status = 'available' AND r.id NOT IN (
                SELECT b.room_id FROM bookings b 
                WHERE NOT (b.check_out_date <= :checkIn OR b.check_in_date >= :checkOut)
                AND b.status = 'confirmed'
            )
        ");
        $stmt->execute(['checkIn' => $checkIn, 'checkOut' => $checkOut]);
        $rooms = $stmt->fetchAll();
    }
} else {
    $stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'available'");
    $rooms = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8" />
    <title>Available Rooms - Aurora Hotel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
      :root {
        --primary: #34568B;
        --accent: #88CCF1;
        --dark: #121212;
        --light: #f9f9f9;
        --error: #ff4d4d;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Poppins', sans-serif;
        background-color: var(--dark);
        color: var(--light);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
      }

      header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.85);
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
        gap: 2rem;
      }

      nav a {
        color: var(--light);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
      }

      nav a:hover,
      nav a.active {
        color: var(--accent);
      }

      .menu-toggle {
        display: none;
        font-size: 1.8rem;
        color: var(--light);
        cursor: pointer;
      }

      @media (max-width: 768px) {
        nav {
          position: absolute;
          top: 60px;
          left: 0;
          right: 0;
          background-color: var(--dark);
          flex-direction: column;
          overflow: hidden;
          max-height: 0;
          transition: max-height 0.3s ease;
          border-top: 1px solid #333;
        }

        nav.show {
          max-height: 400px;
        }

        nav a {
          padding: 1rem 0;
          border-top: 1px solid #333;
          text-align: center;
          width: 100%;
        }

        .menu-toggle {
          display: block;
        }
      }

      main {
        max-width: 1000px;
        margin: 6rem auto 3rem auto;
        padding: 0 1rem;
        flex-grow: 1;
      }

      h1 {
        font-size: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        color: var(--accent);
      }

      .search-dates {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
      }

      .search-dates input[type="date"] {
        padding: 0.6rem 1rem;
        border-radius: 8px;
        border: none;
        background-color: #2d2d2d;
        color: var(--light);
        font-size: 1rem;
        min-width: 150px;
      }

      .search-dates button {
        background-color: var(--accent);
        border: none;
        border-radius: 30px;
        padding: 0.7rem 2rem;
        color: var(--dark);
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.3s ease;
      }

      .search-dates button:hover {
        background-color: #5fbbe0;
        transform: scale(1.05);
      }

      .error-msg {
        text-align: center;
        color: var(--error);
        margin-bottom: 1.5rem;
        font-weight: 600;
      }

      .rooms-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
      }

      .room-card {
        background-color: #1e1e1e;
        border-radius: 12px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
      }

      .room-card:hover {
        transform: scale(1.03);
      }

      .room-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
      }

      .room-details {
        padding: 1rem 1.2rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
      }

      .room-type {
        font-size: 1.2rem;
        color: var(--accent);
        margin-bottom: 0.3rem;
        font-weight: 700;
      }

      .room-price {
        font-weight: 600;
        margin-bottom: 0.5rem;
      }

      .room-desc {
        color: #bbb;
        flex-grow: 1;
        margin-bottom: 1rem;
        font-size: 0.9rem;
      }

      .btn-book {
        align-self: center;
        text-decoration: none;
        background-color: var(--accent);
        color: var(--dark);
        padding: 0.6rem 1.8rem;
        border-radius: 30px;
        font-weight: 700;
        transition: background-color 0.3s ease, transform 0.3s ease;
      }

      .btn-book:hover {
        background-color: #5fbbe0;
        transform: scale(1.05);
      }

      footer {
        background: #0f0f0f;
        text-align: center;
        padding: 1rem;
        color: #666;
        font-size: 0.9rem;
        border-top: 1px solid #222;
        margin-top: auto;
      }
    </style>
</head>
<body>

<header>
    <div class="logo"><i class="fa-solid fa-hotel"></i> Aurora</div>
    <div class="menu-toggle" id="menu-toggle"><i class="fa fa-bars"></i></div>
    <nav id="nav-links">
        <a href="index.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="rooms.php" class="active"><i class="fa-solid fa-bed"></i> Available Rooms</a>
        <a href="my_bookings.php"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
        <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    </nav>
</header>

<main>
    <h1>Available Rooms</h1>

    <form class="search-dates" method="GET" action="rooms.php">
        <input type="date" name="check_in" required value="<?= htmlspecialchars($checkIn) ?>" min="<?= date('Y-m-d') ?>" />
        <input type="date" name="check_out" required value="<?= htmlspecialchars($checkOut) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />
        <button type="submit">Search</button>
    </form>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="rooms-container">
        <?php if (count($rooms) > 0): ?>
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <img src="../uploads/<?= htmlspecialchars($room['image']) ?>" alt="Room <?= htmlspecialchars($room['type']) ?>" class="room-image" />
                    <div class="room-details">
                        <div class="room-type"><?= htmlspecialchars($room['type']) ?></div>
                        <div class="room-price"><?= number_format($room['price'], 2) ?> MAD / night</div>
                        <div class="room-desc"><?= htmlspecialchars(substr($room['description'], 0, 100)) ?>...</div>
                        <a href="booking.php?room_id=<?= $room['id'] ?>&check_in=<?= urlencode($checkIn) ?>&check_out=<?= urlencode($checkOut) ?>" class="btn-book">Book Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color:#666; font-size:1.1em;">No rooms available for these dates.</p>
        <?php endif; ?>
    </div>
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
