<?php
session_start();
require '../config.php';
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'admin') {
    header('Location: ../one.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_id'])) {
    $cancelId = $_POST['cancel_id'];
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$cancelId]);
    $_SESSION['message'] = "Booking canceled successfully!";

}

$bookings = $pdo->query("
    SELECT b.id, b.check_in_date, b.check_out_date, b.status,
           u.username, r.room_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    WHERE b.status = 'confirmed'
    ORDER BY b.check_in_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booked Rooms - Admin</title>
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
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> Add Room</a>
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> All Rooms</a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
            <a href="booked_rooms.php" class="active"><i class="fa-solid fa-ban"></i> Stop Bookings</a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <h1>Confirmed Bookings</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $index => $booking): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($booking['username']) ?></td>
                        <td><?= htmlspecialchars($booking['room_number']) ?></td>
                        <td><?= htmlspecialchars($booking['check_in_date']) ?></td>
                        <td><?= htmlspecialchars($booking['check_out_date']) ?></td>
                        <td><?= htmlspecialchars($booking['status']) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                <input type="hidden" name="cancel_id" value="<?= $booking['id'] ?>">
                                <button type="submit" class="btn-cancel">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="7" style="text-align:center;">No confirmed bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script src="script.js"></script>
</body>
</html>
