<?php
session_start();
require 'config.php';

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
    <title>Available Rooms - Customer Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #f4f6f9;
            color: #222;
        }
        .sidebar {
            position: fixed;
            left: 0; top: 0; bottom: 0;
            width: 230px;
            background: #232946;
            color: #fff;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 8px rgba(0,0,0,0.07);
            z-index: 100;
        }
        .sidebar .logo {
            font-size: 1.6rem;
            font-weight: 700;
            padding: 32px 24px 24px 32px;
            letter-spacing: 1px;
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
        .sidebar nav a.active, .sidebar nav a:hover {
            background: #393e6a;
            border-left: 4px solid #eebbc3;
            color: #eebbc3;
        }
        .sidebar nav i {
            margin-right: 16px;
            font-size: 1.2rem;
        }
        .main-content {
            margin-left: 230px;
            padding: 40px 32px;
            min-height: 100vh;
        }
        h1 {
            font-size: 2rem;
            font-weight: 500;
            color: #232946;
            margin-bottom: 24px;
        }
        form.search-dates {
            display: flex;
            gap: 16px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        form.search-dates input[type="date"] {
            flex: 1 1 150px;
            padding: 10px 12px;
            font-size: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f7f7fa;
            transition: border 0.2s;
        }
        form.search-dates input[type="date"]:focus {
            border-color: #eebbc3;
            outline: none;
        }
        form.search-dates button {
            background: #eebbc3;
            color: #232946;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.18s;
            flex: 0 0 auto;
        }
        form.search-dates button:hover {
            background: #f6c7d1;
        }
        .error-msg {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 1rem;
            background: #fee2e2;
            color: #991b1b;
            max-width: 480px;
        }
        .rooms-container {
            display: grid;
            grid-template-columns: repeat(auto-fill,minmax(280px,1fr));
            gap: 24px;
        }
        .room-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 16px rgba(35,41,70,0.07);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        .room-card:hover {
            box-shadow: 0 6px 22px rgba(35,41,70,0.15);
        }
        .room-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .room-details {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .room-type {
            font-size: 1.3rem;
            font-weight: 600;
            color: #232946;
            margin-bottom: 12px;
        }
        .room-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #eebbc3;
            margin-bottom: 12px;
        }
        .room-desc {
            flex-grow: 1;
            font-size: 1rem;
            color: #555;
            margin-bottom: 16px;
        }
        .btn-book {
            background: #232946;
            color: #eebbc3;
            text-align: center;
            padding: 12px 0;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.25s ease;
        }
        .btn-book:hover {
            background: #393e6a;
        }
        @media (max-width: 700px) {
            .sidebar {
                width: 60px;
            }
            .sidebar .logo {
                font-size: 1.1rem;
                padding: 24px 8px;
            }
            .sidebar nav a span {
                display: none;
            }
            .main-content {
                margin-left: 60px;
                padding: 24px 10px;
            }
            form.search-dates {
                flex-direction: column;
            }
            form.search-dates input[type="date"],
            form.search-dates button {
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Customer</span></div>
    <nav>
        <a href="index.php" ><i class="fa-solid fa-house"></i> <span>Dashboard</span></a>
        <a href="rooms.php" class="active"><i class="fa-solid fa-bed"></i> <span>Available Rooms</span></a>
        <a href="my_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>My Bookings</span></a>
        <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>
</div>

<div class="main-content">
    <h1>Available Rooms</h1>

    <form class="search-dates" method="GET" action="rooms.php">
        <input type="date" name="check_in" required value="<?= htmlspecialchars($checkIn) ?>" />
        <input type="date" name="check_out" required value="<?= htmlspecialchars($checkOut) ?>" />
        <button type="submit">Search</button>
    </form>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="rooms-container">
        <?php if (count($rooms) > 0): ?>
            <?php foreach ($rooms as $room): ?>
                <div class="room-card">
                    <img src="uploads/<?= htmlspecialchars($room['image'] ?: 'default-room.jpg') ?>" alt="Room <?= htmlspecialchars($room['type']) ?>" class="room-image" />
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
</div>

</body>
</html>
