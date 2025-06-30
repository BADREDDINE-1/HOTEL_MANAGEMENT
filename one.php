<?php
session_start();
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$registerError = $registerSuccess = '';
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'register') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $name_pattern = "/^[a-zA-Z\s]{3,50}$/";
    $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d).{8,}$/";

    if (!preg_match($name_pattern, $name)) {
        $registerError = "Name must be letters and spaces only, 3-50 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = "Invalid email format.";
    } elseif (!preg_match($password_pattern, $password)) {
        $registerError = "Password must be minimum 8 characters, with at least one letter and one number.";
    } elseif ($password !== $confirm_password) {
        $registerError = "Passwords do not match.";
    } else {
        try {
            $sql = "INSERT INTO users (username, email, password, verification_code, is_verified, role) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($stmt->execute([$name, $email, $hashed_password, $code, 0, 'customer'])) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'your email';
                    $mail->Password = 'rhtm aahv sfaf xnfn';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('your email', 'QA-HOTEL');
                    $mail->addAddress($email, $name);
                    $mail->Subject = 'Verify your email - QA-HOTEL';
                    $mail->isHTML(true);
                    $mail->Body = "<h1>Welcome to QA-HOTEL</h1><p>Dear $name,</p><p>Please use the following code to verify your email:</p><h2>$code</h2><p>Best regards,<br>QA-HOTEL Team</p>";
                    $mail->AltBody = "Verification code: $code";

                    if ($mail->send()) {
                        $_SESSION['code'] = $code;
                        $_SESSION['user_id'] = $pdo->lastInsertId();
                        header("Location: verify.php");
                        exit();
                    } else {
                        $registerError = "Failed to send verification email.";
                    }
                } catch (Exception $e) {
                    $registerError = "Mailer Error: " . $mail->ErrorInfo;
                }
            }
        } catch (PDOException $e) {
            $registerError = "Database error: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['form_type'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $loginError = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_verified']) {
                $_SESSION['isLogged'] = true;
                $_SESSION['userId'] = $user['id'];
                $_SESSION['userRole'] = $user['role'];
                if($user['role'] === 'admin') {
                    header("Location: admin/admin_dashboard.php");
                } else {
                    header("Location: customer/index.php");
                }
            } else {
                $loginError = "Your email is not verified.";
            }
        } else {
            $loginError = "Invalid email or password.";
        }
    }
}

$showRegister = !empty($registerError);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login/Register Switch</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet" />
  <style>
    * {
      margin: 0; 
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
    body, html {
      height: 100%;
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }
    .container {
      width: 85vw;
      max-width: 950px;
      height: 85vh;
      min-height: 600px;
      background: transparent;
      border-radius: 25px;
      display: flex;
      overflow: hidden;
      position: relative;
      transition: all 0.8s ease;
    }
    .left-panel {
      width: 45%;
      background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
      box-shadow: inset 0 0 50px rgba(255,255,255,0.1);
      color: white;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      user-select: none;
      position: relative;
      overflow: hidden;
    }
    .left-panel h1 {
      font-size: 3.2rem;
      margin-bottom: 25px;
      font-weight: 700;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }
    .left-panel p {
      font-size: 1.3rem;
      line-height: 1.6;
      max-width: 320px;
      opacity: 0.95;
      text-shadow: 0 1px 5px rgba(0,0,0,0.2);
    }
    .right-panel {
      width: 55%;
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(10px);
      padding: 60px 70px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }
    form {
      width: 100%;
      max-width: 320px;
      display: none;
      flex-direction: column;
      gap: 20px;
      animation: slideIn 0.6s ease forwards;
    }
    form.active {
      display: flex;
    }
    form h2 {
      font-size: 1.8rem;
      color: #333;
      text-align: center;
      margin-bottom: 10px;
      font-weight: 600;
    }
    .input-group {
      position: relative;
    }
    input {
      width: 100%;
      padding: 14px 18px;
      border-radius: 12px;
      border: 2px solid rgba(102, 126, 234, 0.2);
      font-size: 1rem;
      background: rgba(255,255,255,0.8);
      transition: all 0.3s ease;
      outline: none;
    }
    input:focus {
      border-color: #667eea;
      background: white;
      box-shadow: 0 0 20px rgba(102, 126, 234, 0.2);
      transform: translateY(-2px);
    }
    input::placeholder {
      color: #999;
      font-weight: 400;
    }
    button.submit-btn {
      padding: 14px;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      font-weight: 600;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
      transition: all 0.3s ease;
      margin-top: 10px;
    }
    button.submit-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
    }
    button.submit-btn:active {
      transform: translateY(-1px);
    }
    .switch-section {
      text-align: center;
      margin-top: 35px;
    }
    .switch-text {
      color: #666;
      margin-bottom: 15px;
      font-size: 1rem;
    }
    button.switch-btn {
      background: transparent;
      border: 2px solid #667eea;
      color: #667eea;
      padding: 12px 45px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      user-select: none;
    }
    button.switch-btn:hover {
      background: #667eea;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    .container.register-mode .left-panel {
      transform: translateX(122%);
    }
    .container.register-mode .right-panel {
      transform: translateX(-82%);
    }
    @keyframes slideIn {
      from {
        opacity: 0; 
        transform: translateY(30px);
      }
      to {
        opacity: 1; 
        transform: translateY(0);
      }
    }
    @media (max-width: 1024px) {
      .container {
        width: 90vw;
        height: 90vh;
      }
      .left-panel {
        padding: 40px 30px;
      }
      .right-panel {
        padding: 40px 50px;
      }
    }
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        height: auto;
        width: 95vw;
        min-height: auto;
      }
      .left-panel, .right-panel {
        width: 100% !important;
        transform: none !important;
        padding: 40px 30px;
      }
      .left-panel {
        height: 250px;
      }
      .left-panel h1 {
        font-size: 2.5rem;
      }
      .left-panel p {
        font-size: 1.1rem;
      }
      form h2 {
        font-size: 1.6rem;
      }
      .container.register-mode .left-panel,
      .container.register-mode .right-panel {
        transform: none;
      }
    }
    @media (max-width: 480px) {
      .container {
        width: 100vw;
        border-radius: 0;
        height: 100vh;
      }
      .left-panel, .right-panel {
        padding: 30px 20px;
      }
      form {
        max-width: 100%;
      }
    }
    .message {
      background-color: #f8d7da;
      color: #842029;
      border-radius: 8px;
      padding: 10px 15px;
      margin-bottom: 15px;
      border: 1px solid #f5c2c7;
      font-weight: 600;
      font-size: 0.95rem;
      text-align: center;
    }
    .success-message {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }
  </style>
</head>

<body>

<div class="container <?php echo $showRegister ? 'register-mode' : '' ?>" id="container">

  <div class="left-panel">
    <h1>Welcome!</h1>
    <p>Join us or login to manage your hotel bookings</p>
  </div>

  <div class="right-panel">

    <form id="login-form" class="<?php echo !$showRegister ? 'active' : '' ?>" method="POST" action="">
      <h2>Sign In</h2>
      <?php if ($loginError): ?>
        <div class="message"><?php echo htmlspecialchars($loginError); ?></div>
      <?php endif; ?>
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="password" name="password" placeholder="Password" required />
      <a href="forgot.php">Forgot Password?</a>
      <button type="submit" name="form_type" value="login" class="submit-btn">Sign In</button>
    </form>

    <form id="register-form" class="<?php echo $showRegister ? 'active' : '' ?>" method="POST" action="">
      <h2>Create Account</h2>
      <?php if ($registerError): ?>
        <div class="message"><?php echo htmlspecialchars($registerError); ?></div>
      <?php endif; ?>
      <input type="text" name="name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email Address" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="password" name="confirm_password" placeholder="Confirm Password" required />
      <button type="submit" name="form_type" value="register" class="submit-btn">Create Account</button>
    </form>

    <div class="switch-section">
      <p class="switch-text" id="switch-text">Don't have an account?</p>
      <button class="switch-btn" id="toggle-btn" onclick="toggleForm()">Sign Up</button>
    </div>
  </div>
</div>

<script>
  const container = document.getElementById('container');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  const toggleBtn = document.getElementById('toggle-btn');
  const switchText = document.getElementById('switch-text');

  let isRegister = <?php echo $showRegister ? 'true' : 'false'; ?>;

  function toggleForm() {
    isRegister = !isRegister;
    container.classList.toggle('register-mode');

    if (isRegister) {
      loginForm.classList.remove('active');
      registerForm.classList.add('active');
      toggleBtn.textContent = "Sign In";
      switchText.textContent = "Already have an account?";
    } else {
      registerForm.classList.remove('active');
      loginForm.classList.add('active');
      toggleBtn.textContent = "Sign Up";
      switchText.textContent = "Don't have an account?";
    }
  }
</script>

</body>
</html>
