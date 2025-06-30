<?php
session_start();
require '../config.php';

if (!isset($_SESSION['userId'])) {
    header('Location: one.php');
    exit;
}

$userId = $_SESSION['userId'];
$roomId = $_GET['room_id'] ?? null;
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';

if (!$roomId) {
    header('Location: rooms.php');
    exit;
}

$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? LIMIT 1");
$stmt->execute([$roomId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("Room not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkInPost = $_POST['check_in'] ?? '';
    $checkOutPost = $_POST['check_out'] ?? '';

    if (!$checkInPost || !$checkOutPost) {
        $error = "Please provide both check-in and check-out dates.";
    } elseif ($checkInPost >= $checkOutPost) {
        $error = "Check-out date must be after check-in date.";
    } else {
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE room_id = ? AND status = 'confirmed' 
            AND NOT (check_out_date <= ? OR check_in_date >= ?)
        ");
        $checkStmt->execute([$roomId, $checkInPost, $checkOutPost]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            $error = "Sorry, this room is not available for the selected dates.";
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, 'pending')");
            if ($insertStmt->execute([$userId, $roomId, $checkInPost, $checkOutPost])) {
                $success = "Booking request submitted successfully! You will receive confirmation soon.";
            } else {
                $error = "Failed to submit booking. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Book Room - <?= htmlspecialchars($room['room_number']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    :root {
      --primary: #34568B;
      --accent: #88CCF1;
      --dark: #121212;
      --light: #f9f9f9;
      --text-light: #ddd;
      --error: #ff4d4d;
      --success: #4caf50;
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
      flex-grow: 1;
      padding: 6rem 2rem 2rem;
      max-width: 900px;
      margin: 0 auto;
      width: 100%;
    }

    h1 {
      text-align: center;
      color: var(--accent);
      margin-bottom: 2rem;
      font-weight: 700;
    }

    .room-info {
      background-color: #1c1c1c;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.4);
      text-align: center;
      margin-bottom: 2rem;
    }

    .room-info img {
      width: 100%;
      max-height: 300px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    .room-info h2 {
      color: var(--accent);
      margin-bottom: 0.5rem;
    }

    .room-info p {
      color: #ccc;
      margin: 0.5rem 0;
    }

    form {
      background-color: #1e1e1e;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.4);
    }

    label {
      display: block;
      margin-bottom: 0.3rem;
      font-weight: 500;
    }

    input[type="date"] {
      width: 100%;
      padding: 0.8rem;
      margin-bottom: 1.2rem;
      border: none;
      border-radius: 8px;
      background-color: #2d2d2d;
      color: var(--light);
      font-size: 1rem;
    }

    button {
      width: 100%;
      padding: 1rem;
      background-color: var(--accent);
      border: none;
      border-radius: 30px;
      color: var(--dark);
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.3s ease;
    }

    button:hover {
      background-color: #5fbbe0;
      transform: scale(1.05);
    }

    .alert-success,
    .alert-error {
      text-align: center;
      padding: 1rem;
      margin: 1rem 0;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
    }

    .alert-success {
      background-color: var(--success);
      color: white;
    }

    .alert-error {
      background-color: var(--error);
      color: white;
    }

    a.back-link {
      display: block;
      text-align: center;
      color: var(--accent);
      margin-top: 1.5rem;
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
    }

    a.back-link:hover {
      text-decoration: underline;
    }

  </style>
</head>
<body>

<header>
  <div class="logo"><i class="fa-solid fa-hotel"></i> Aurora</div>
  <div class="menu-toggle" id="menu-toggle"><i class="fa fa-bars"></i></div>
  <nav id="nav-links">
    <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
    <a href="rooms.php" class="active"><i class="fa-solid fa-bed"></i> Rooms</a>
    <a href="my_bookings.php"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
    <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
  </nav>
</header>

<main>
  <h1>Book Room <?= htmlspecialchars($room['room_number']) ?></h1>

  <div class="room-info">
    <img src="../uploads/<?= htmlspecialchars($room['image'] ?: 'default-room.jpg') ?>" alt="Room Image" />
    <h2><?= htmlspecialchars($room['type']) ?></h2>
    <p>Price: <?= number_format($room['price'], 2) ?> MAD / night</p>
    <p><?= htmlspecialchars($room['description']) ?></p>
  </div>

  <?php if ($success): ?>
    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
  <?php elseif ($error): ?>
    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <label for="check_in">Check-in Date</label>
    <input type="date" id="check_in" name="check_in" required value="<?= htmlspecialchars($checkIn) ?>" min="<?= date('Y-m-d') ?>" />

    <label for="check_out">Check-out Date</label>
    <input type="date" id="check_out" name="check_out" required value="<?= htmlspecialchars($checkOut) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />

    <button type="submit"><i class="fa-solid fa-bed"></i> Confirm Booking</button>
  </form>

  <a href="index.php" class="back-link">← Back to Home</a>
</main>

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
