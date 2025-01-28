<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Fetch the most recent 5 donations
$recentDonations = [];
try {
  $stmt = $pdo->query("SELECT name, amount, payment_status, created_at
                         FROM donations
                         ORDER BY id DESC
                         LIMIT 5");
  $recentDonations = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Error fetching donations: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Support Africa - Donate Today</title>
  <link rel="stylesheet" href="css/styles.css">
  <!-- Google Fonts for enhanced typography -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>
<style>
  .navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 50px 20px;
    background-color:rgb(255, 255, 255);
    box-shadow: 0px 2px 0 #000;
  }

  .navbar-brand {
    font-size: 1.5em;
    font-weight: bold;
    color: #000;
    text-decoration: none;
  }

  .navbar-nav {
    list-style: none;
    display: flex;
    margin: 0;
    padding: 0;
  }

  .navbar-nav li {
    margin-left: 20px;
  }

  .navbar-nav a {
    text-decoration: none;
    color: #000;
    font-weight: bold;
    padding: 5px 10px;
    border: 3px solid #000;
    box-shadow: 5px 5px 0 #000;
    background-color:rgb(200, 0, 255);
    transition: transform 0.2s
  }

  /* Footer styles */
  .foot {
    border-top: 3px solid;
    background-color:rgb(255, 255, 255);
    padding: 50px;
    position: relative;
    top: 150px;
  }

  .footer {
    background-color:rgb(255, 255, 255);
    /* Bold background color */
    border-top: 5px solid #000;
    /* Strong top border */
    box-shadow: 0px -10px 0px #000;
    /* Neobrutalist shadow effect */
    text-align: center;
    padding: 20px;
    margin-top: 40px;
  }

  .footer p {
    margin: 0;
    font-weight: bold;
  }

  .footer-nav {
    list-style: none;
    padding: 0;

    display: flex;
    justify-content: center;
  }

  .footer-nav li {
    margin: 0 15px;
  }

  .footer-nav a {
    text-decoration: none;
    color: #000;
    font-weight: bold;
    border: 3px solid #000;
    padding: 5px 10px;
    background-color: rgb(200, 0, 255);
    box-shadow: 5px 5px 0 #000;
    transition: transform 0.2s;
  }

  .footer-nav a:hover {
    transform: translate(-5px, -5px);
  }
  img{
  width:5%;
  height: 20%;

}
</style>

<body>
  <nav class="navbar">
    <a href="#" class="navbar-brand">Support Africa</a>
    <ul class="navbar-nav">
      <li><a href="#">Home</a></li>
      <li><a href="#">About Us</a></li>
      <li><a href="#">Projects</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
  </nav>
  <header>
  <img src="images/pngtree-map-of-africa-isolated-png-image_10061860.png" alt="Support Africa" class="banner">
    <h1>Empowering Africa Together</h1>
    <p>Your generosity helps bring sustainable development and positive change to communities across Africa.</p>
  </header>

  <section class="sec">
    <div class="container">
      <div class="donation-form">
        <h2>Make a Difference</h2>
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert success">
            <?php echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?>
          </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert error">
            <?php echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>

        <form id="donationForm" action="process_donation.php" method="POST" novalidate>
          <div class="form-group">
            <label for="name">Full Name<span class="required">*</span></label>
            <input type="text" id="name" name="name" required>
            <div class="error-message" id="nameError"></div>
          </div>
          <div class="form-group">
            <label for="email">Email Address<span class="required">*</span></label>
            <input type="email" id="email" name="email" required>
            <div class="error-message" id="emailError"></div>
          </div>
          <div class="form-group">
            <label for="amount">Donation Amount (NGN)<span class="required">*</span></label>
            <input type="number" id="amount" name="amount" min="100" required>
            <div class="error-message" id="amountError"></div>
          </div>
          <button type="submit">Donate Now</button>
        </form>

        <?php if (!empty($recentDonations)): ?>
          <h3>Recent Donations</h3>
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Amount (NGN)</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentDonations as $donation): ?>
                <tr>
                  <td><?php echo htmlspecialchars($donation['name']); ?></td>
                  <td><?php echo number_format($donation['amount'], 2); ?></td>
                  <td><?php echo htmlspecialchars($donation['payment_status']); ?></td>
                  <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($donation['created_at']))); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
    
  </section>

  <div class="foot">
    <footer>
      <ul class="footer-nav">
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Contact Us</a></li>
      </ul>
    </footer>
  </div>


  <!-- Confetti Animation -->
  <canvas id="confettiCanvas"></canvas>

  <script src="js/main.js"></script>
</body>

</html>