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
        td:last-child {
            white-space: nowrap;
        }
        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            margin-right: 6px;
        }
        .btn-edit {
            background-color: #44bd32;
            color: white;
        }
        .btn-delete {
            background-color: #e84118;
            color: white;
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
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <nav>
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php" class="active"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
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
</body>
</html>
