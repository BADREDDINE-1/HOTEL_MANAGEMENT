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
        $imageNameForDB = null; // no image uploaded
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
    <!-- Google Fonts for modern look -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <!-- Icons -->
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
            <a href="add_rooms.php" class="active"><i class="fa-solid fa-bed"></i> <span>Add Room</span></a>
            <a href="admin_rooms.php"><i class="fa-solid fa-list"></i> <span>Rooms List</span></a>
            <a href="admin_bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings</span></a>
            <a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a>
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
</body>
</html>

</html>