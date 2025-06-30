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
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php" class="active"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="boocked_rooms.php"><i class="fa-solid fa-ban"></i> Stop Bookings</a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
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
        <script src="script.js"></script>
    </div>
</body>
</html>
