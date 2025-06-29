<?php
session_start();
require 'config.php';

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
    <title>My Bookings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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
        .dashboard-header {
            font-size: 2rem;
            font-weight: 500;
            margin-bottom: 24px;
            color: #232946;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }
        thead {
            background: #eebbc3;
            color: #232946;
        }
        th, td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #888;
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
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Customer</span></div>
        <nav>
            <a href="index.php"><i class="fa-solid fa-house"></i> <span>Home</span></a>
            <a href="rooms.php"><i class="fa-solid fa-bed"></i> <span>Available Rooms</span></a>
            <a href="my_bookings.php" class="active"><i class="fa-solid fa-calendar-check"></i> <span>My Bookings</span></a>
            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        <div class="dashboard-header">My Bookings</div>
        <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Room Number</th>
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
    </div>
</body>
</html>
