<?php
session_start();
require '../config.php';
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'admin') {
    header('Location: ../one.php');
    exit();
}
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['userId']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$rooms = $pdo->query("SELECT COUNT(*) as total FROM rooms")->fetch()['total'] ?? 0;
$bookings = $pdo->query("SELECT COUNT(*) as total FROM bookings")->fetch()['total'] ?? 0;
$users = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'] ?? 0;
$available = $pdo->query("SELECT COUNT(*) as total FROM rooms WHERE status='available'")->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Hotel Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            margin-bottom: 12px;
            color: #232946;
        }
        .dashboard-subtitle {
            color: #888;
            margin-bottom: 30px;
        }
        .dashboard-cards {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 28px 24px;
            flex: 1 1 220px;
            max-width: 280px;
        }
        .card-title {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 10px;
        }
        .card-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #eebbc3;
        }
        @media (max-width: 900px) {
            .main-content {
                padding: 20px;
                margin-left: 60px;
            }
            .dashboard-cards {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <nav>
            <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> <span>All Rooms</span></a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        <div class="dashboard-header">Welcome, Admin <?= htmlspecialchars($user['username']) ?></div>
        <div class="dashboard-subtitle">Hotel Management Dashboard Overview</div>
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-title">Total Rooms</div>
                <div class="card-value"><?= $rooms ?></div>
            </div>
            <div class="card">
                <div class="card-title">Available Rooms</div>
                <div class="card-value"><?= $available ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Bookings</div>
                <div class="card-value"><?= $bookings ?></div>
            </div>
            <div class="card">
                <div class="card-title">Registered Users</div>
                <div class="card-value"><?= $users ?></div>
            </div>
        </div>
    </div>
</body>
</html>
