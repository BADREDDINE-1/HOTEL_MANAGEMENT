<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Aurora Hotel - Welcome</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    :root {
      --primary: #34568B;
      --accent: #88CCF1;
      --dark: #121212;
      --light: #f9f9f9;
      --text-light: #ddd;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--dark);
      color: var(--light);
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(12px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      z-index: 1000;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--accent);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    nav {
      display: flex;
      gap: 1.5rem;
    }

    nav a {
      text-decoration: none;
      color: var(--text-light);
      transition: 0.3s;
    }

    nav a:hover,
    nav a.active {
      color: var(--accent);
    }

    .menu-toggle {
      display: none;
      font-size: 1.5rem;
      color: var(--light);
      cursor: pointer;
    }

    @media (max-width: 768px) {
      nav {
        position: absolute;
        top: 70px;
        left: 0;
        right: 0;
        background: #1b1b1b;
        flex-direction: column;
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.3s ease;
      }

      nav.show {
        max-height: 300px;
      }

      nav a {
        padding: 1rem;
        text-align: center;
        border-top: 1px solid #333;
      }

      .menu-toggle {
        display: block;
      }
    }

    .hero {
      height: 100vh;
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.6)),
        url('https://images.unsplash.com/photo-1576671081837-d1aa1b5c7c32?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 2rem;
      animation: fadeIn 1s ease-out;
    }

    .hero h1 {
      font-size: 3rem;
      max-width: 800px;
      margin-bottom: 1rem;
      color: var(--accent);
      font-weight: 700;
    }

    .hero p {
      font-size: 1.1rem;
      color: var(--text-light);
      max-width: 600px;
      margin-bottom: 2rem;
    }

    .btn {
      background: var(--accent);
      color: var(--dark);
      padding: 0.9rem 2rem;
      text-decoration: none;
      font-weight: 600;
      border-radius: 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      transition: 0.3s ease;
    }

    .btn:hover {
      box-shadow: 0 6px 20px rgba(0,0,0,0.5);
      transform: scale(1.05);
    }

    .section {
      padding: 4rem 2rem;
      max-width: 1000px;
      margin: auto;
      text-align: center;
    }

    .section h2 {
      font-size: 2rem;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .section p {
      line-height: 1.6;
      color: #bbb;
    }

    footer {
      background: #0f0f0f;
      text-align: center;
      padding: 1rem;
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid #222;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<header>
  <div class="logo"><i class="fa-solid fa-hotel"></i> Aurora</div>
  <div class="menu-toggle" id="menu-toggle"><i class="fa fa-bars"></i></div>
  <nav id="nav-links">
    <a href="index.php" class="active"><i class="fa-solid fa-house"></i> Home</a>
    <a href="rooms.php"><i class="fa-solid fa-bed"></i> Rooms</a>
    <a href="my_bookings.php"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="../logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
      <a href="../one.php"><i class="fa-solid fa-sign-in-alt"></i> Login</a>
    <?php endif; ?>
  </nav>
</header>

<main>
  <section class="hero">
    <h1>Welcome to Aurora Hotel</h1>
    <p>Luxury, comfort, and elegance — experience your stay in style in the heart of the city.</p>
    <a href="rooms.php" class="btn">Explore Rooms</a>
  </section>

  <section class="section">
    <h2>Why Choose Aurora?</h2>
    <p>
      From luxurious rooms and world-class service to a breathtaking ambiance, Aurora Hotel is your escape from the ordinary. Whether for business or leisure, we promise an unforgettable stay.
    </p>
  </section>
</main>

<footer>
  &copy; <?php echo date("Y"); ?> Aurora Hotel. All rights reserved.
</footer>

<script>
  const toggleBtn = document.getElementById('menu-toggle');
  const navLinks = document.getElementById('nav-links');

  toggleBtn.addEventListener('click', () => {
    navLinks.classList.toggle('show');
    const icon = toggleBtn.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
  });
</script>

</body>
</html>
