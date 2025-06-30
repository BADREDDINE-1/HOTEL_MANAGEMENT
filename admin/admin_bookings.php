<?php
session_start();
require '../config.php';
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'admin') {
    header('Location: ../one.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['action'])) {
    $bookingId = (int)$_POST['booking_id'];
    $action = $_POST['action'];

    if (in_array($action, ['confirmed', 'canceled'])) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$action, $bookingId]);
    }
}

$bookings = $pdo->query("
    SELECT b.id, b.user_id, b.room_id, b.check_in_date, b.check_out_date, b.status, 
           u.username AS user_name, 
           r.room_number AS room_number 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings - Admin Dashboard</title>
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
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php" class="active"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="boocked_rooms.php"><i class="fa-solid fa-ban"></i> Stop Bookings</a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </div>

    <div class="main-content">
        <div class="dashboard-header">All Bookings</div>
        <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= $booking['id'] ?></td>
                            <td><?= htmlspecialchars($booking['user_name']) ?></td>
                            <td><?= htmlspecialchars($booking['room_number']) ?></td>
                            <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
                            <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
                            <td><?= htmlspecialchars($booking['status']) ?></td>
                            <td class="actions">
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <form class="status-form" method="post">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="confirmed">
                                        <button type="submit" class="btn-accept" title="Accept booking">
                                            <i class="fa-solid fa-check"></i> Accept
                                        </button>
                                    </form>
                                    <form class="status-form" method="post">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="canceled">
                                        <button type="submit" class="btn-decline" title="Decline booking">
                                            <i class="fa-solid fa-times"></i> Decline
                                        </button>
                                    </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">No bookings found.</div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>
