<?php
session_start();
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$token, $expires, $email]);

            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'badr.liongames@gmail.com';
                $mail->Password = 'rhtm aahv sfaf xnfn'; // Don't expose real credentials in production
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('badr.liongames@gmail.com', 'QA-HOTEL');
                $mail->addAddress($email);
                $mail->Subject = 'Password Reset Request';
                $mail->isHTML(true);
                $mail->Body = "
                    <h1>Password Reset Request</h1>
                    <p>Dear User,</p>
                    <p>Click the link below to reset your password:</p>
                    <a href='http://localhost/all/reset.php?token=$token'>Reset Password</a>
                    <p>This link expires in 1 hour.</p>
                    <p>QA-HOTEL Team</p>";
                $mail->AltBody = "Reset your password at: http://localhost/all/reset.php?token=$token";

                $mail->send();
                $success = "A reset link has been sent to your email.";
            } catch (Exception $e) {
                $error = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "No account found with that email.";
        }
    } else {
        $error = "Email is required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Forgot Password - QA-HOTEL</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <style>
    * {
      margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif;
    }
    body, html {
      height: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
      display: flex;
      justify-content: center;
      width: 100%;
      height: 100%;
      align-items: center;
      padding: 20px;
      user-select: none;
    }

    .forgot-container {
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

    input[type="email"] {
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

    input[type="email"]:focus {
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
      font-weight: 500;
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
      .forgot-container {
        padding: 30px 25px;
      }
      h3 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>

<div class="forgot-container" role="main" aria-label="Forgot password form">
  <h3>Forgot Password</h3>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div>
      <label for="email">Enter your email address</label>
      <input type="email" name="email" id="email" required placeholder="e.g. your@email.com" />
    </div>
    <button type="submit">Send Reset Link</button>
    <p class="text-center mt-3"><a href="one.php">Back to Login</a></p>
  </form>
</div>

<footer>
  &copy; 2025 QA-HOTEL. All rights reserved.
</footer>

</body>
</html>
