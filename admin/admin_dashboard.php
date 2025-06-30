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
$available = $pdo->query("SELECT COUNT(*) as total FROM rooms WHERE status='available'")->fetch()['total'] ?? 0;
$bookings = $pdo->query("SELECT COUNT(*) as total FROM bookings")->fetch()['total'] ?? 0;
$users = $pdo->query("SELECT COUNT(*) as total FROM users")->fetch()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard - Hotel Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <header class="mobile-header">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <button class="mobile-menu-btn" aria-label="Toggle Menu"><i class="fa-solid fa-bars"></i></button>
    </header>
    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>

    <div class="sidebar">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <nav>
            <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="boocked_rooms.php"><i class="fa-solid fa-ban"></i> <span>Stop Bookings</span></a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        <header class="dashboard-header">
            <h1>Welcome back, <?= htmlspecialchars($user['username']) ?>!</h1>
            <p>Here's what's happening with your hotel today.</p>
        </header>

        <section class="dashboard-cards">
            <article class="card">
                <header>
                    <h2>Total Rooms</h2>
                    <i class="fa-solid fa-bed card-icon"></i>
                </header>
                <div class="card-value"><?= $rooms ?></div>
                <footer><i class="fa-solid fa-arrow-up"></i> All rooms configured</footer>
            </article>

            <article class="card">
                <header>
                    <h2>Available Rooms</h2>
                    <i class="fa-solid fa-door-open card-icon"></i>
                </header>
                <div class="card-value"><?= $available ?></div>
                <footer><i class="fa-solid fa-check"></i> Ready for booking</footer>
            </article>

            <article class="card">
                <header>
                    <h2>Total Bookings</h2>
                    <i class="fa-solid fa-calendar-check card-icon"></i>
                </header>
                <div class="card-value"><?= $bookings ?></div>
                <footer><i class="fa-solid fa-trend-up"></i> Active reservations</footer>
            </article>

            <article class="card">
                <header>
                    <h2>Registered Users</h2>
                    <i class="fa-solid fa-users card-icon"></i>
                </header>
                <div class="card-value"><?= $users ?></div>
                <footer><i class="fa-solid fa-user-plus"></i> Total customers</footer>
            </article>
        </section>

        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="add_rooms.php" class="action-btn"><i class="fa-solid fa-plus"></i> Add New Room</a>
                <a href="admin_bookings.php" class="action-btn"><i class="fa-solid fa-eye"></i> View Bookings</a>
                <a href="admin_rooms.php" class="action-btn"><i class="fa-solid fa-cog"></i> Manage Rooms</a>
                <a href="boocked_rooms.php" class="action-btn"><i class="fa-solid fa-ban"></i> Room Controls</a>
            </div>
        </section>
    </div>

    <script src="script.js"></script>
</body>
</html>
