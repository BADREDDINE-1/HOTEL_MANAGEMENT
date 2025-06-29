<?php
    session_start();
    require '../config.php';

    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: admin_rooms.php");
    exit();
?>