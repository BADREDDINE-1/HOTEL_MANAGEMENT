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
        table td.actions {
            white-space: nowrap;
        }
        form.status-form {
            display: inline-block;
            margin-right: 6px;
        }
        button.btn-accept {
            background-color: #44bd32;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        button.btn-decline {
            background-color: #e84118;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <nav>
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php" class="active"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
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
                                    <form class="status-form" method="post" style="display:inline;">
                                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                        <input type="hidden" name="action" value="confirmed">
                                        <button type="submit" class="btn-accept" title="Accept booking">
                                            <i class="fa-solid fa-check"></i> Accept
                                        </button>
                                    </form>
                                    <form class="status-form" method="post" style="display:inline;">
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
</body>
</html>
