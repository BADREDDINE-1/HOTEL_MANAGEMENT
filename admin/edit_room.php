<?php
session_start();
require '../config.php';
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'admin') {
    header('Location: ../one.php');
    exit();
}
$success = $error = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: admin_rooms.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    header("Location: admin_rooms.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $room_type = trim($_POST['room_type']);
    $price = trim($_POST['price']);
    $status = trim($_POST['status']);

    if ($room_number && $room_type && $price && $status) {
        $update = $pdo->prepare("UPDATE rooms SET room_number = ?, type = ?, price = ?, status = ? WHERE id = ?");
        if ($update->execute([$room_number, $room_type, $price, $status, $id])) {
            $success = "Room updated successfully! <a href='admin_rooms.php'>View Rooms</a>";
            // Refresh data
            $stmt->execute([$id]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Error updating room.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Room - Admin Dashboard</title>
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
    .form-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(35,41,70,0.07);
        padding: 32px 28px;
        max-width: 480px;
        margin: 0 auto;
    }
    .form-card h2 {
        margin-top: 0;
        font-size: 1.3rem;
        color: #232946;
        margin-bottom: 18px;
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        display: block;
        margin-bottom: 7px;
        font-weight: 500;
        color: #393e6a;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
        background: #f7f7fa;
        transition: border 0.2s;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #eebbc3;
        outline: none;
    }
    .btn {
        background: #eebbc3;
        color: #232946;
        border: none;
        padding: 12px 28px;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.18s;
    }
    .btn:hover {
        background: #f6c7d1;
    }
    .alert {
        padding: 12px 18px;
        border-radius: 6px;
        margin-bottom: 18px;
        font-size: 1rem;
    }
    .alert-success {
        background: #d1fae5;
        color: #065f46;
    }
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
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
        .form-card {
            padding: 18px 8px;
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
        <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
        <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>
</div>
<div class="main-content">
    <div class="dashboard-header">Edit Room #<?= htmlspecialchars($room['id']) ?></div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="room_number">Room Number</label>
                <input type="text" id="room_number" name="room_number" value="<?= htmlspecialchars($room['room_number']) ?>" required>
            </div>
            <div class="form-group">
                <label for="room_type">Room Type</label>
                <select id="room_type" name="room_type" required>
                    <option value="">Select Type</option>
                    <option value="Single" <?= $room['type'] === 'Single' ? 'selected' : '' ?>>Single</option>
                    <option value="Double" <?= $room['type'] === 'Double' ? 'selected' : '' ?>>Double</option>
                    <option value="Suite" <?= $room['type'] === 'Suite' ? 'selected' : '' ?>>Suite</option>
                    <option value="Deluxe" <?= $room['type'] === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price per Night ($)</label>
                <input type="number" id="price" name="price" min="1" step="0.01" value="<?= htmlspecialchars($room['price']) ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="available" <?= $room['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="unavailable" <?= $room['status'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>
            <button type="submit" class="btn"><i class="fa-solid fa-save"></i> Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>
