<?php
session_start();
require 'config.php';

if (!isset($_SESSION['userId'])) {
    header('Location: one.php');
    exit;
}

$userId = $_SESSION['userId'];
$roomId = $_GET['room_id'] ?? null;
$checkIn = $_GET['check_in'] ?? '';
$checkOut = $_GET['check_out'] ?? '';

if (!$roomId) {
    header('Location: rooms.php');
    exit;
}

$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? LIMIT 1");
$stmt->execute([$roomId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("Room not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkInPost = $_POST['check_in'] ?? '';
    $checkOutPost = $_POST['check_out'] ?? '';

    if (!$checkInPost || !$checkOutPost) {
        $error = "Please provide both check-in and check-out dates.";
    } elseif ($checkInPost >= $checkOutPost) {
        $error = "Check-out date must be after check-in date.";
    } else {
        // Check if room is available for these dates (confirmed bookings overlap)
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE room_id = ? AND status = 'confirmed' 
            AND NOT (check_out_date <= ? OR check_in_date >= ?)
        ");
        $checkStmt->execute([$roomId, $checkInPost, $checkOutPost]);
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            $error = "Sorry, this room is not available for the selected dates.";
        } else {
            // Insert booking
            $insertStmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, status) VALUES (?, ?, ?, ?, 'pending')");
            if ($insertStmt->execute([$userId, $roomId, $checkInPost, $checkOutPost])) {
                $success = "Booking request submitted successfully! You will receive confirmation soon.";
            } else {
                $error = "Failed to submit booking. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Book Room - <?= htmlspecialchars($room['room_number']) ?></title>
<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<style>
body {
    font-family: 'Roboto', sans-serif;
    background: #f4f6f9;
    margin: 0;
    color: #222;
    padding: 30px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}
h1 {
    color: #232946;
    text-align: center;
    margin-bottom: 24px;
}
.room-info {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(35,41,70,0.07);
    margin-bottom: 30px;
}
.room-info img {
    width: 100%;
    max-height: 250px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 15px;
}
.room-info h2 {
    margin: 0 0 10px;
    color: #232946;
}
.room-info p {
    margin: 4px 0;
    color: #444;
}
form {
    background: #fff;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(35,41,70,0.07);
}
label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #393e6a;
}
input[type="date"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 20px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #f7f7fa;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s;
}
input[type="date"]:focus {
    border-color: #eebbc3;
}
button {
    background: #eebbc3;
    color: #232946;
    border: none;
    padding: 12px 28px;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s;
    width: 100%;
}
button:hover {
    background: #f6c7d1;
}
.alert-success {
    background: #d1fae5;
    color: #065f46;
    padding: 12px 18px;
    border-radius: 6px;
    margin-bottom: 18px;
}
.alert-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px 18px;
    border-radius: 6px;
    margin-bottom: 18px;
}
</style>
</head>
<body>

<h1>Book Room <?= htmlspecialchars($room['room_number']) ?></h1>

<div class="room-info">
    <img src="uploads/<?= htmlspecialchars($room['image'] ?: 'default-room.jpg') ?>" alt="Room Image" />
    <h2><?= htmlspecialchars($room['type']) ?></h2>
    <p>Price: <?= number_format($room['price'], 2) ?> MAD / night</p>
    <p><?= htmlspecialchars($room['description']) ?></p>
</div>

<?php if ($success): ?>
    <div class="alert-success"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
    <div class="alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" autocomplete="off">
    <label for="check_in">Check-in Date</label>
    <input type="date" id="check_in" name="check_in" required value="<?= htmlspecialchars($checkIn) ?>" min="<?= date('Y-m-d') ?>" />

    <label for="check_out">Check-out Date</label>
    <input type="date" id="check_out" name="check_out" required value="<?= htmlspecialchars($checkOut) ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />

    <button type="submit"><i class="fa-solid fa-bed"></i> Confirm Booking</button>
    <a href="index.php" class="mt-3">Go back?</a>
</form>

</body>
</html>
