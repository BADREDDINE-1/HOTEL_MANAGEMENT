<?php
session_start();
require '../config.php';
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'admin') {
    header('Location: ../one.php');
    exit();
}

$success = $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_number = trim($_POST['room_number']);
    $room_type = trim($_POST['room_type']);
    $price = trim($_POST['price']);
    $status = trim($_POST['status']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageNameForDB = $newFileName;
            } else {
                $error = "Error moving the uploaded file.";
            }
        } else {
            $error = "Upload failed. Allowed file types: " . implode(", ", $allowedfileExtensions);
        }
    } else {
        $imageNameForDB = null;
    }

    if (!$error && $room_number && $room_type && $price && $status) {
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, type, price, status, image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$room_number, $room_type, $price, $status, $imageNameForDB])) {
            $success = "Room added successfully!";
        } else {
            $error = "Error adding room.";
        }
    } elseif (!$error) {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Room - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Mobile Header with Hamburger -->
    <header class="mobile-header">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <button class="mobile-menu-btn" aria-label="Toggle Menu"><i class="fa-solid fa-bars"></i></button>
    </header>

    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>
    <div class="sidebar">
        <div class="logo"><i class="fa-solid fa-hotel"></i> <span>Admin</span></div>
        <nav>
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> <span>Dashboard</span></a>
            <a href="add_rooms.php" class="active"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="boocked_rooms.php"><i class="fa-solid fa-ban"></i> Stop Bookings</a>
            <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </div>
    <div class="main-content">
        <div class="dashboard-header">Add New Room</div>
        <div class="form-card">
            <h2>Room Details</h2>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" autocomplete="off">
                <div class="form-group">
                    <label for="room_number">Room Number</label>
                    <input type="text" id="room_number" name="room_number" required>
                </div>
                <div class="form-group">
                    <label for="room_type">Room Type</label>
                    <select id="room_type" name="room_type" required>
                        <option value="">Select Type</option>
                        <option value="Single">Single</option>
                        <option value="Double">Double</option>
                        <option value="Suite">Suite</option>
                        <option value="Deluxe">Deluxe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price per Night ($)</label>
                    <input type="number" id="price" name="price" min="1" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="available">available</option>
                        <option value="unavailable">unavailable</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Room Image</label>
                    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
                </div>
                <button type="submit" class="btn"><i class="fa-solid fa-plus"></i> Add Room</button>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
