<?php
session_start();
require '../config.php';
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'admin') {
    header('Location: ../one.php');
    exit();
}
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Rooms - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php" class="active"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="boocked_rooms.php"><i class="fa-solid fa-ban"></i> Stop Bookings</a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        <div class="dashboard-header">All Rooms</div>
        <?php if (count($rooms) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= $room['id']?></td>
                            <td><?= htmlspecialchars($room['room_number'])?></td>
                            <td><?= htmlspecialchars($room['type'])?></td>
                            <td><?= htmlspecialchars($room['price'])?> DH</td>
                            <td><?= htmlspecialchars($room['status'])?></td>
                            <td>
                                <a href="edit_room.php?id=<?= $room['id'] ?>" class="btn btn-edit">Edit</a>
                                <a href="delete_room.php?id=<?= $room['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">No rooms found.</div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>
