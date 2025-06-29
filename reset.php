<?php
  session_start();
  require_once 'config.php';

  $error = '';
  $success = '';
  $showForm = false;

  $token = $_GET['token'] ?? '';

  if (empty($token)) {
      $error = "Invalid or missing reset token.";
  } else {
      $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
      $stmt->execute([$token]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$user) {
          $error = "Reset token is invalid or has expired.";
      } else {
          $showForm = true;

          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
              $password = $_POST['password'] ?? '';
              $confirm_password = $_POST['confirm_password'] ?? '';

              if (empty($password) || empty($confirm_password)) {
                  $error = "Both password fields are required.";
              } elseif ($password !== $confirm_password) {
                  $error = "Passwords do not match.";
              } else {
                  $hashed = password_hash($password, PASSWORD_DEFAULT);
                  $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                  $stmt->execute([$hashed, $user['id']]);

                  $success = "Your password has been reset successfully. You can now <a href='one.php'>login</a>.";
                  $showForm = false;
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
  <title>Reset Password - QA-HOTEL</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <style>
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

    .reset-container {
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
      font-size: 2.2rem;
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
      font-size: 1rem;
      text-align: left;
      text-shadow: 0 1px 4px rgba(0,0,0,0.2);
    }

    input[type="password"] {
      padding: 15px 20px;
      border-radius: 15px;
      border: 2px solid rgba(255, 255, 255, 0.5);
      outline: none;
      font-size: 1rem;
      color: #333;
      background: #fff;
      box-shadow: 0 2px 15px rgba(102,126,234,0.3);
      transition: all 0.3s ease;
    }

    input[type="password"]:focus {
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

    a {
      color: #fff;
      text-decoration: underline;
      font-weight: 600;
    }

    footer {
      position: fixed;
      bottom: 15px;
      width: 100%;
      text-align: center;
      color: white;
      font-weight: 500;
      font-size: 0.9rem;
      text-shadow: 0 0 8px rgba(0,0,0,0.4);
    }

    @media (max-width: 480px) {
      .reset-container {
        padding: 30px 25px;
      }
      h3 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>

<div class="reset-container" role="main" aria-label="Reset password form">
  <h3>Reset Password</h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
  <?php endif; ?>

  <?php if ($showForm): ?>
    <form method="POST">
      <div>
        <label for="password">New Password</label>
        <input type="password" name="password" id="password" required minlength="6" placeholder="Enter new password" />
      </div>
      <div>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required minlength="6" placeholder="Confirm password" />
      </div>
      <button type="submit">Reset Password</button>
    </form>
  <?php else: ?>
    <p><a href="one.php">Back to Login</a></p>
  <?php endif; ?>
</div>

<footer>
  &copy; 2025 QA-HOTEL. All rights reserved.
</footer>

</body>
</html>
