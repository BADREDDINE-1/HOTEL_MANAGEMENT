<?php
session_start();
require 'config.php';

$error = $_SESSION['error'] ?? '';
$message = $_SESSION['message'] ?? '';
unset($_SESSION['error'], $_SESSION['message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['verification_code'] ?? '');

    if (empty($code)) {
        $_SESSION['error'] = "Please enter the verification code.";
        header('Location: verify.php');
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Session expired. Please login again.";
        header('Location: one.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT verification_code, is_verified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header('Location: one.php');
        exit;
    }

    if ($user['is_verified']) {
        $_SESSION['message'] = "Your account is already verified.";
        header('Location: one.php');
        exit;
    }

    if ($code === $user['verification_code']) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
        $stmt->execute([$user_id]);

        $_SESSION['message'] = "Your email has been verified successfully.";
        header('Location: one.php');
        exit;
    } else {
        $_SESSION['error'] = "Invalid verification code.";
        header('Location: verify.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Verify Email - QA-HOTEL</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <style>
    /* Reset */
    * {
      margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif;
    }
    body, html {
      height: 100%;
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      user-select: none;
    }

    .verification-container {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 25px;
      padding: 40px 50px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.2);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.3);
      color: #fff;
      text-align: center;
      animation: fadeInUp 0.8s ease forwards;
      position: relative;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    h3 {
      font-weight: 700;
      font-size: 2.4rem;
      margin-bottom: 30px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    label {
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 6px;
      text-align: left;
      user-select: text;
      text-shadow: 0 1px 4px rgba(0,0,0,0.2);
    }

    input[type="text"] {
      padding: 15px 20px;
      border-radius: 15px;
      border: 2px solid rgba(255, 255, 255, 0.5);
      outline: none;
      font-size: 1rem;
      color: #333;
      transition: all 0.3s ease;
      background: #fff;
      box-shadow: 0 2px 15px rgba(102,126,234,0.3);
    }
    input[type="text"]:focus {
      border-color: #667eea;
      box-shadow: 0 5px 25px rgba(102,126,234,0.5);
      transform: translateY(-2px);
    }

    button {
      padding: 15px 20px;
      border-radius: 25px;
      border: none;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 8px 30px rgba(102,126,234,0.6);
      transition: all 0.3s ease;
      user-select: none;
    }
    button:hover {
      box-shadow: 0 12px 45px rgba(102,126,234,0.8);
      transform: translateY(-3px);
    }

    .alert {
      padding: 12px 20px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1rem;
      margin-bottom: 20px;
      user-select: text;
      text-align: center;
    }
    .alert-danger {
      background: rgba(255, 77, 79, 0.85);
      color: #fff;
      box-shadow: 0 3px 10px rgba(255, 0, 0, 0.3);
    }
    .alert-success {
      background: rgba(64, 196, 99, 0.85);
      color: #fff;
      box-shadow: 0 3px 10px rgba(0, 200, 50, 0.3);
    }

    footer {
      position: fixed;
      bottom: 15px;
      width: 100%;
      text-align: center;
      color: white;
      font-weight: 500;
      user-select: none;
      font-size: 0.9rem;
      text-shadow: 0 0 8px rgba(0,0,0,0.4);
    }

    @media (max-width: 480px) {
      .verification-container {
        padding: 30px 25px;
      }
      h3 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>

  <div class="verification-container" role="main" aria-label="Email verification form">
    <h3>Email Verification</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
      <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="verify.php" novalidate>
      <label for="verification-code">Enter Verification Code</label>
      <input
        type="text"
        id="verification-code"
        name="verification_code"
        maxlength="10"
        required
        autocomplete="off"
        aria-required="true"
        aria-describedby="codeHelp"
        placeholder="6-digit code"
        pattern="\d{6}"
        title="Please enter the 6-digit verification code"
      />
      <button type="submit">Verify Email</button>
    </form>
  </div>

  <footer>
    &copy; 2025 QA-HOTEL. All rights reserved.
  </footer>

</body>
</html>
