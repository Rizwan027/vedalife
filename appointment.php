<?php
// Include authentication check
require_once 'auth_check.php';

// Require user to be logged in to access this page
requireLogin();

// Get current user info
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes">
  <title>VEDAMRUT - Book Your Ayurvedic Appointment</title>
  
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    :root {
      --primary-color: #2c6e49;
      --primary-light: #4c956c;
      --primary-dark: #1a4731;
      --secondary-color: #ffc145;
      --accent-color: #ff6b6b;
      --text-dark: #2d3142;
      --text-light: #4f5d75;
      --background-light: #f8fffe;
      --background-white: #ffffff;
      --shadow-soft: 0 10px 30px rgba(44, 110, 73, 0.08);
      --shadow-medium: 0 20px 40px rgba(44, 110, 73, 0.12);
      --shadow-large: 0 30px 60px rgba(44, 110, 73, 0.15);
      --gradient-primary: linear-gradient(135deg, #2c6e49 0%, #4c956c 100%);
      --gradient-secondary: linear-gradient(135deg, #ffc145 0%, #ffb347 100%);
      --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      --border-radius: 20px;
      font-size: 100%;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    html {
      -webkit-text-size-adjust: 100%;
      -moz-text-size-adjust: 100%;
      text-size-adjust: 100%;
      font-size: 100%;
    }

    body {
      font-family: 'Poppins', sans-serif;
      line-height: 1.6;
      color: var(--text-dark);
      background: var(--background-light);
      overflow-x: hidden;
      font-size: 1rem;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* Enhanced Navbar */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 9%;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--transition-smooth);
    }

    .header.scrolled {
      padding: 0.8rem 9%;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      background: rgba(255, 255, 255, 0.98);
    }

    .header .logo {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary-dark);
      font-family: 'Cormorant Garamond', serif;
      letter-spacing: 1px;
      text-decoration: none;
    }

    .header .navbar {
      display: flex;
      align-items: center;
    }

    .header .navbar a {
      position: relative;
      font-size: 0.875rem;
      margin: 0 1.5rem;
      color: var(--text-dark);
      text-decoration: none;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: var(--transition-smooth);
    }

    .header .navbar a::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -8px;
      width: 0;
      height: 3px;
      background: var(--gradient-primary);
      border-radius: 2px;
      transition: var(--transition-smooth);
    }

    .header .navbar a:hover::after,
    .header .navbar a.active::after {
      width: 100%;
    }

    .header .navbar a:hover,
    .header .navbar a.active {
      color: var(--primary-color);
    }

    .nav-buttons {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .nav-buttons .btn {
      padding: 0.7rem 1.5rem;
      font-size: 0.85rem;
      border-radius: 50px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: var(--transition-smooth);
      border: 2px solid transparent;
      text-decoration: none;
    }

    .nav-buttons .login-btn {
      background: transparent;
      color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .nav-buttons .login-btn:hover {
      background: var(--primary-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: var(--shadow-soft);
    }

    .nav-buttons .signup-btn {
      background: var(--gradient-primary);
      color: white;
      border-color: var(--primary-color);
    }

    .nav-buttons .signup-btn:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
      color: white;
    }

    /* Additional navbar styles for logged-in state - matching index.php */
    .nav-buttons .btn-danger {
      background: var(--accent-color);
      color: white;
      border-color: var(--accent-color);
    }

    .nav-buttons .btn-danger:hover {
      background: #ff5252;
      border-color: #ff5252;
      color: white;
    }

    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: var(--accent-color);
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 0.7rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    /* Booking Page Header */
    .booking-header {
      background: var(--gradient-primary);
      padding: 120px 0 80px;
      position: relative;
      overflow: hidden;
      min-height: 40vh;
      display: flex;
      align-items: center;
    }

    .booking-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('images/background2.jpg') center/cover;
      opacity: 0.1;
      z-index: 0;
    }

    .booking-header .container {
      position: relative;
      z-index: 2;
    }

    .booking-header h1 {
      color: white;
      font-size: 3.5rem;
      font-weight: 700;
      text-shadow: 0 4px 20px rgba(0,0,0,0.3);
      margin-bottom: 1rem;
      font-family: 'Cormorant Garamond', serif;
      letter-spacing: 2px;
    }

    .booking-header p {
      color: rgba(255,255,255,0.95);
      font-size: 1.2rem;
      margin-bottom: 0;
      font-weight: 400;
    }

    /* Multi-Step Form Container */
    .booking-container {
      min-height: calc(100vh - 200px);
      display: flex;
      align-items: center;
      padding: 80px 0;
    }

    .form-wizard {
      background: var(--background-white);
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-large);
      overflow: hidden;
      max-width: 900px;
      margin: 0 auto;
      border: 1px solid rgba(44, 110, 73, 0.1);
    }

    /* Progress Steps */
    .progress-steps {
      background: var(--gradient-primary);
      padding: 2rem;
      color: white;
      text-align: center;
    }

    .steps-container {
      display: flex;
      justify-content: center;
      align-items: center;
      max-width: 600px;
      margin: 0 auto;
    }

    .step {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
    }

    .step-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      transition: var(--transition-smooth);
      margin-bottom: 0.5rem;
    }

    .step.active .step-icon {
      background: var(--secondary-color);
      color: var(--text-dark);
      transform: scale(1.1);
    }

    .step.completed .step-icon {
      background: var(--secondary-color);
      color: var(--text-dark);
    }

    .step-title {
      font-size: 0.85rem;
      font-weight: 600;
      opacity: 0.7;
      transition: var(--transition-smooth);
    }

    .step.active .step-title {
      opacity: 1;
      transform: translateY(-2px);
    }

    .step-connector {
      position: absolute;
      top: 25px;
      left: 50%;
      width: 100%;
      height: 2px;
      background: rgba(255, 255, 255, 0.3);
      z-index: 0;
    }

    .step:last-child .step-connector {
      display: none;
    }

    /* Form Steps */
    .form-step {
      padding: 3rem;
      display: none;
      animation: fadeInUp 0.6s ease;
    }

    .form-step.active {
      display: block;
    }

    .step-header {
      text-align: center;
      margin-bottom: 2.5rem;
    }

    .step-header h3 {
      color: var(--primary-color);
      font-size: 1.8rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .step-header p {
      color: var(--text-light);
      font-size: 1rem;
    }

    /* Enhanced Form Fields */
    .form-group {
      position: relative;
      margin-bottom: 2rem;
    }

    .form-group label {
      position: absolute;
      top: 50%;
      left: 1.5rem;
      transform: translateY(-50%);
      color: var(--text-light);
      font-weight: 500;
      pointer-events: none;
      transition: var(--transition-smooth);
      z-index: 2;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 1.2rem 1.5rem;
      border: 2px solid rgba(44, 110, 73, 0.1);
      border-radius: 15px;
      font-size: 1rem;
      background: var(--background-light);
      color: var(--text-dark);
      transition: var(--transition-smooth);
      position: relative;
      z-index: 1;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary-color);
      background: white;
      box-shadow: 0 0 0 3px rgba(44, 110, 73, 0.1);
    }

    .form-group input:focus + label,
    .form-group input:not(:placeholder-shown) + label,
    .form-group select:focus + label,
    .form-group select:not([value=""]):not([value="Select Service"]) + label,
    .form-group textarea:focus + label,
    .form-group textarea:not(:placeholder-shown) + label {
      top: 0;
      left: 1rem;
      font-size: 0.75rem;
      color: var(--primary-color);
      background: white;
      padding: 0 0.5rem;
    }

    .form-group textarea {
      min-height: 120px;
      resize: vertical;
    }

    /* Service Selection Cards */
    .service-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin: 2rem 0;
    }

    .service-card {
      background: var(--background-light);
      border: 2px solid transparent;
      border-radius: 15px;
      padding: 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: var(--transition-smooth);
      position: relative;
    }

    .service-card:hover {
      border-color: var(--primary-color);
      transform: translateY(-5px);
      box-shadow: var(--shadow-medium);
    }

    .service-card.selected {
      border-color: var(--primary-color);
      background: var(--background-white);
      box-shadow: var(--shadow-soft);
    }

    .service-card input[type="radio"] {
      display: none;
    }

    .service-icon {
      width: 60px;
      height: 60px;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      color: white;
      font-size: 1.5rem;
    }

    .service-title {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }

    .service-price {
      color: var(--primary-color);
      font-weight: 700;
      font-size: 1.1rem;
    }

    /* Navigation Buttons */
    .form-navigation {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 3rem;
      padding-top: 2rem;
      border-top: 1px solid rgba(44, 110, 73, 0.1);
    }

    .btn-nav {
      padding: 0.9rem 2.5rem;
      border-radius: 50px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: var(--transition-smooth);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-prev {
      background: transparent;
      color: var(--text-light);
      border: 2px solid rgba(44, 110, 73, 0.2);
    }

    .btn-prev:hover {
      background: var(--background-light);
      color: var(--text-dark);
      border-color: var(--primary-color);
    }

    .btn-next,
    .btn-submit {
      background: var(--gradient-primary);
      color: white;
      box-shadow: var(--shadow-soft);
    }

    .btn-next:hover,
    .btn-submit:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
      color: white;
    }

    /* Review Step */
    .review-section {
      background: var(--background-light);
      border-radius: 15px;
      padding: 2rem;
      margin: 1rem 0;
    }

    .review-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.8rem 0;
      border-bottom: 1px solid rgba(44, 110, 73, 0.1);
    }

    .review-item:last-child {
      border-bottom: none;
    }

    .review-label {
      font-weight: 600;
      color: var(--text-dark);
    }

    .review-value {
      color: var(--text-light);
      text-align: right;
    }

    /* Success Animation */
    .success-icon {
      width: 80px;
      height: 80px;
      background: var(--gradient-secondary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 2rem;
      color: var(--text-dark);
      font-size: 2.5rem;
      animation: bounceIn 0.8s ease;
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes bounceIn {
      0%, 20%, 40%, 60%, 80% {
        animation-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
      }
      0% {
        opacity: 0;
        transform: scale3d(0.3, 0.3, 0.3);
      }
      20% {
        transform: scale3d(1.1, 1.1, 1.1);
      }
      40% {
        transform: scale3d(0.9, 0.9, 0.9);
      }
      60% {
        opacity: 1;
        transform: scale3d(1.03, 1.03, 1.03);
      }
      80% {
        transform: scale3d(0.97, 0.97, 0.97);
      }
      100% {
        opacity: 1;
        transform: scale3d(1, 1, 1);
      }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .header .navbar {
        display: none;
      }
      
      .booking-header h1 {
        font-size: 2.5rem;
      }
      
      .form-step {
        padding: 2rem 1.5rem;
      }
      
      .service-grid {
        grid-template-columns: 1fr;
      }
      
      .steps-container {
        flex-direction: column;
        gap: 1rem;
      }
      
      .step {
        flex-direction: row;
        align-items: center;
        gap: 1rem;
      }
      
      .step-connector {
        display: none;
      }
    }
  </style>
</head>
<body>

<!-- Enhanced Navbar -->
<header class="header" id="navbar">
  <a href="index.php" class="logo">🌿VEDAMRUT</a>
  
  <nav class="navbar">
    <a href="index.php">Home</a>
    <a href="services.php">Services</a>
    <a href="products.php">Products</a>
    <a href="#pricing">Packages</a>
    <a href="#testimonials">Testimonials</a>
    <a href="#contact">Contact</a>
  </nav>

  <div class="nav-buttons">
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="SignUp_LogIn_Form.html" class="btn login-btn"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
        <a href="SignUp_LogIn_Form.html" class="btn signup-btn"><i class="fa-solid fa-user-plus"></i> Sign Up</a>
    <?php else: ?>
        <a href="profile.php" class="btn"><i class="fa-solid fa-user"></i></a>
        <a href="cart.php" class="btn position-relative">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="cart-badge" id="cartCountBadge">0</span>
        </a>
        <a href="logout.php" class="btn btn-danger"><i class="fa-solid fa-right-from-bracket"></i></a>
    <?php endif; ?>
  </div>
</header>

<!-- Enhanced Booking Header -->
<div class="booking-header">
  <div class="container text-center">
    <h1 data-aos="fade-up">Book Your Healing Journey</h1>
    <p data-aos="fade-up" data-aos-delay="200">Welcome, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>! Schedule your personalized Ayurvedic treatment with our expert practitioners.</p>
  </div>
</div>

<!-- Modern Multi-Step Booking Form -->
<div class="booking-container">
  <div class="container">
    <div class="form-wizard" data-aos="fade-up">
      
      <!-- Progress Steps -->
      <div class="progress-steps">
        <div class="steps-container">
          <div class="step active" data-step="1">
            <div class="step-connector"></div>
            <div class="step-icon"><i class="fas fa-user"></i></div>
            <div class="step-title">Personal Info</div>
          </div>
          <div class="step" data-step="2">
            <div class="step-connector"></div>
            <div class="step-icon"><i class="fas fa-spa"></i></div>
            <div class="step-title">Select Service</div>
          </div>
          <div class="step" data-step="3">
            <div class="step-connector"></div>
            <div class="step-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="step-title">Date & Notes</div>
          </div>
          <div class="step" data-step="4">
            <div class="step-icon"><i class="fas fa-check"></i></div>
            <div class="step-title">Review & Confirm</div>
          </div>
        </div>
      </div>

      <form id="appointmentForm">
        <!-- Hidden field to pass user ID -->
        <input type="hidden" name="user_id" value="<?php echo $currentUser['id']; ?>">

        <!-- Step 1: Personal Information -->
        <div class="form-step active" data-step="1">
          <div class="step-header">
            <h3>Personal Information</h3>
            <p>Let's start with your basic details for the appointment</p>
          </div>

          <div class="form-group">
            <input type="text" name="name" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required placeholder=" ">
            <label>Full Name</label>
          </div>

          <div class="form-group">
            <input type="email" name="email" required placeholder=" ">
            <label>Email Address</label>
          </div>

          <div class="form-group">
            <input type="tel" name="phone" required placeholder=" ">
            <label>Phone Number</label>
          </div>

          <div class="form-navigation">
            <div></div>
            <button type="button" class="btn-nav btn-next">Next Step <i class="fas fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 2: Service Selection -->
        <div class="form-step" data-step="2">
          <div class="step-header">
            <h3>Choose Your Treatment</h3>
            <p>Select the Ayurvedic service that best suits your wellness needs</p>
          </div>

          <div class="service-grid">
            <div class="service-card" data-service="Ayurvedic Consultation">
              <input type="radio" name="service" value="Ayurvedic Consultation" required>
              <div class="service-icon"><i class="fas fa-stethoscope"></i></div>
              <div class="service-title">Ayurvedic Consultation</div>
              <div class="service-price">₹1,200</div>
            </div>
            
            <div class="service-card" data-service="Shirodhara">
              <input type="radio" name="service" value="Shirodhara" required>
              <div class="service-icon"><i class="fas fa-tint"></i></div>
              <div class="service-title">Shirodhara Therapy</div>
              <div class="service-price">₹3,000</div>
            </div>
            
            <div class="service-card" data-service="Hrudbasti">
              <input type="radio" name="service" value="Hrudbasti" required>
              <div class="service-icon"><i class="fas fa-heart"></i></div>
              <div class="service-title">Hrudbasti Treatment</div>
              <div class="service-price">₹2,500</div>
            </div>
            
            <div class="service-card" data-service="Kati Basti">
              <input type="radio" name="service" value="Kati Basti" required>
              <div class="service-icon"><i class="fas fa-procedures"></i></div>
              <div class="service-title">Kati Basti Therapy</div>
              <div class="service-price">₹2,200</div>
            </div>
            
            <div class="service-card" data-service="Panchakarma Therapy">
              <input type="radio" name="service" value="Panchakarma Therapy" required>
              <div class="service-icon"><i class="fas fa-leaf"></i></div>
              <div class="service-title">Panchakarma Therapy</div>
              <div class="service-price">₹5,000</div>
            </div>
            
            <div class="service-card" data-service="Massage & Wellness">
              <input type="radio" name="service" value="Massage & Wellness" required>
              <div class="service-icon"><i class="fas fa-spa"></i></div>
              <div class="service-title">Massage & Wellness</div>
              <div class="service-price">₹1,800</div>
            </div>
          </div>

          <div class="form-navigation">
            <button type="button" class="btn-nav btn-prev"><i class="fas fa-arrow-left"></i> Previous</button>
            <button type="button" class="btn-nav btn-next">Next Step <i class="fas fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 3: Date & Additional Information -->
        <div class="form-step" data-step="3">
          <div class="step-header">
            <h3>Schedule Your Appointment</h3>
            <p>Choose your preferred date and share any additional information</p>
          </div>

          <div class="form-group">
            <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required placeholder=" ">
            <label>Preferred Date</label>
          </div>

          <div class="form-group">
            <textarea name="notes" rows="4" placeholder="Tell us about your health concerns, any specific requirements, or questions you might have..."></textarea>
            <label>Additional Notes & Health Information (Optional)</label>
          </div>

          <div class="form-navigation">
            <button type="button" class="btn-nav btn-prev"><i class="fas fa-arrow-left"></i> Previous</button>
            <button type="button" class="btn-nav btn-next">Review Booking <i class="fas fa-arrow-right"></i></button>
          </div>
        </div>

        <!-- Step 4: Review & Confirmation -->
        <div class="form-step" data-step="4">
          <div class="step-header">
            <h3>Review Your Booking</h3>
            <p>Please review your appointment details before confirming</p>
          </div>

          <div class="review-section">
            <div class="review-item">
              <div class="review-label"><i class="fas fa-user"></i> Name:</div>
              <div class="review-value" id="review-name"></div>
            </div>
            <div class="review-item">
              <div class="review-label"><i class="fas fa-envelope"></i> Email:</div>
              <div class="review-value" id="review-email"></div>
            </div>
            <div class="review-item">
              <div class="review-label"><i class="fas fa-phone"></i> Phone:</div>
              <div class="review-value" id="review-phone"></div>
            </div>
            <div class="review-item">
              <div class="review-label"><i class="fas fa-spa"></i> Service:</div>
              <div class="review-value" id="review-service"></div>
            </div>
            <div class="review-item">
              <div class="review-label"><i class="fas fa-calendar-alt"></i> Date:</div>
              <div class="review-value" id="review-date"></div>
            </div>
            <div class="review-item">
              <div class="review-label"><i class="fas fa-sticky-note"></i> Notes:</div>
              <div class="review-value" id="review-notes"></div>
            </div>
          </div>

          <div class="form-navigation">
            <button type="button" class="btn-nav btn-prev"><i class="fas fa-arrow-left"></i> Previous</button>
            <button type="submit" class="btn-nav btn-submit"><i class="fas fa-calendar-check"></i> Confirm Booking</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: var(--shadow-large);">
      <div class="modal-header" style="border-bottom: none; background: var(--gradient-primary); color: white; border-radius: 20px 20px 0 0; padding: 2rem;">
        <div class="text-center w-100">
          <div class="success-icon mx-auto mb-3">
            <i class="fas fa-check-circle"></i>
          </div>
          <h4 class="modal-title mb-0">Appointment Booked Successfully!</h4>
        </div>
      </div>
      <div class="modal-body text-center" style="padding: 2rem;">
        <h6 class="mb-3" style="color: var(--primary-color);">Thank you for choosing VEDAMRUT!</h6>
        <p class="mb-3" style="color: var(--text-light);">Your appointment has been successfully scheduled. We will contact you within 24 hours to confirm your booking and provide additional details.</p>
        <div class="alert" style="background: rgba(44, 110, 73, 0.1); border: 1px solid var(--primary-light); color: var(--text-dark); border-radius: 15px;">
          <i class="fas fa-info-circle" style="color: var(--primary-color);"></i>
          <strong>What's Next?</strong><br>
          Our expert practitioners will call you to confirm the appointment time and provide any preparation instructions if needed.
        </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding: 1.5rem 2rem 2rem; justify-content: center; gap: 1rem;">
        <a href="my_bookings.php" class="btn" style="background: var(--gradient-primary); color: white; border-radius: 50px; padding: 0.8rem 2rem;">
          <i class="fas fa-calendar-alt me-2"></i>View My Appointments
        </a>
        <a href="index.php" class="btn" style="background: transparent; color: var(--primary-color); border: 2px solid var(--primary-color); border-radius: 50px; padding: 0.8rem 2rem;">
          <i class="fas fa-home me-2"></i>Back to Home
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Enhanced Footer - Matching Other Pages -->
<footer style="background: linear-gradient(135deg, var(--text-dark) 0%, #1a1a2e 100%); color: white; padding: 4rem 0 2rem; margin-top: 5rem;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="mb-4">
          <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 700; color: var(--secondary-color); margin-bottom: 1rem;">🌿VEDAMRUT</h3>
          <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.7; margin-bottom: 2rem;">Experience authentic Ayurvedic healing with our premium treatments and natural products. Your journey to holistic wellness begins here.</p>
          
          <div class="d-flex gap-3">
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-2 col-md-6">
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem; font-weight: 600;">Quick Links</h5>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 0.8rem;"><a href="index.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link"><i class="fas fa-home" style="margin-right: 0.5rem; color: var(--primary-light);"></i>Home</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link"><i class="fas fa-spa" style="margin-right: 0.5rem; color: var(--primary-light);"></i>Services</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="products.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link"><i class="fas fa-shopping-bag" style="margin-right: 0.5rem; color: var(--primary-light);"></i>Products</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="appointment.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link"><i class="fas fa-calendar-check" style="margin-right: 0.5rem; color: var(--primary-light);"></i>Booking</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem; font-weight: 600;">Our Services</h5>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 0.8rem;"><a href="appointment.php?service=shirodhara" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Shirodhara Therapy</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="appointment.php?service=hrudbasti" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Hrudbasti Treatment</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="appointment.php?service=katibasti" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Kati Basti</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="appointment.php?service=consultation" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Consultation</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem; font-weight: 600;">Contact Info</h5>
        <div style="color: rgba(255, 255, 255, 0.8);">
          <div style="margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 1rem;">
            <i class="fas fa-map-marker-alt" style="color: var(--primary-light); margin-top: 0.2rem;"></i>
            <div>
              <strong>Address:</strong><br>
              123 Ayurveda Way<br>
              Wellness District, Health City
            </div>
          </div>
          
          <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-envelope" style="color: var(--primary-light);"></i>
            <div>
              <strong>Email:</strong><br>
              <a href="mailto:info@vedamrut.com" style="color: var(--secondary-color); text-decoration: none;">info@vedamrut.com</a>
            </div>
          </div>
          
          <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-phone" style="color: var(--primary-light);"></i>
            <div>
              <strong>Phone:</strong><br>
              <a href="tel:+917382947582" style="color: var(--secondary-color); text-decoration: none;">+91 73829 47582</a>
            </div>
          </div>
          
          <div style="display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-clock" style="color: var(--primary-light);"></i>
            <div>
              <strong>Hours:</strong><br>
              Mon - Sat: 9:00 AM - 7:00 PM
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 3rem 0 2rem;">
    
    <div class="row align-items-center">
      <div class="col-md-6">
        <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">&copy; 2024 VEDAMRUT. All rights reserved. | Privacy Policy | Terms of Service</p>
      </div>
      <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">Designed with ❤️ for holistic wellness</p>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Enhanced Multi-Step Booking Script -->
<script>
  // Initialize AOS
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100
  });

  // Navbar scroll effect - matching index.php
  window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 100) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });

  // Cart management functions
  function getCart() {
    return JSON.parse(localStorage.getItem('vedalife_cart')) || [];
  }

  function getCartItemCount() {
    const cart = getCart();
    return cart.reduce((total, item) => total + item.quantity, 0);
  }

  function updateCartCounter() {
    const cartCountBadge = document.getElementById('cartCountBadge');
    const itemCount = getCartItemCount();
    if (cartCountBadge) {
      cartCountBadge.textContent = itemCount;
      
      // Hide badge if cart is empty
      if (itemCount === 0) {
        cartCountBadge.style.display = 'none';
      } else {
        cartCountBadge.style.display = 'flex';
      }
    }
  }

  // Multi-step form functionality
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart counter on page load
    updateCartCounter();
    
    let currentStep = 1;
    const totalSteps = 4;
    
    const steps = document.querySelectorAll('.step');
    const formSteps = document.querySelectorAll('.form-step');
    const nextButtons = document.querySelectorAll('.btn-next');
    const prevButtons = document.querySelectorAll('.btn-prev');
    
    // Update step display
    function updateStep(step) {
      // Update progress steps
      steps.forEach((s, index) => {
        if (index + 1 < step) {
          s.classList.add('completed');
          s.classList.remove('active');
        } else if (index + 1 === step) {
          s.classList.add('active');
          s.classList.remove('completed');
        } else {
          s.classList.remove('active', 'completed');
        }
      });
      
      // Update form steps
      formSteps.forEach((fs, index) => {
        if (index + 1 === step) {
          fs.classList.add('active');
        } else {
          fs.classList.remove('active');
        }
      });
    }
    
    // Validate current step
    function validateStep(step) {
      const currentFormStep = document.querySelector(`[data-step="${step}"].form-step`);
      const requiredFields = currentFormStep.querySelectorAll('[required]');
      
      for (let field of requiredFields) {
        if (!field.value.trim()) {
          field.focus();
          field.style.borderColor = 'var(--accent-color)';
          setTimeout(() => {
            field.style.borderColor = '';
          }, 3000);
          return false;
        }
      }
      return true;
    }
    
    // Update review section
    function updateReview() {
      const form = document.getElementById('appointmentForm');
      const formData = new FormData(form);
      
      document.getElementById('review-name').textContent = formData.get('name') || 'Not provided';
      document.getElementById('review-email').textContent = formData.get('email') || 'Not provided';
      document.getElementById('review-phone').textContent = formData.get('phone') || 'Not provided';
      document.getElementById('review-service').textContent = formData.get('service') || 'Not selected';
      
      const dateValue = formData.get('date');
      if (dateValue) {
        const date = new Date(dateValue);
        document.getElementById('review-date').textContent = date.toLocaleDateString('en-IN', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      } else {
        document.getElementById('review-date').textContent = 'Not selected';
      }
      
      document.getElementById('review-notes').textContent = formData.get('notes') || 'No additional notes';
    }
    
    // Next button functionality
    nextButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
          if (currentStep < totalSteps) {
            currentStep++;
            updateStep(currentStep);
            
            if (currentStep === 4) {
              updateReview();
            }
          }
        }
      });
    });
    
    // Previous button functionality
    prevButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        if (currentStep > 1) {
          currentStep--;
          updateStep(currentStep);
        }
      });
    });
    
    // Service card selection
    const serviceCards = document.querySelectorAll('.service-card');
    serviceCards.forEach(card => {
      card.addEventListener('click', function() {
        serviceCards.forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
      });
    });
    
    // Handle URL parameters to pre-select service
    const urlParams = new URLSearchParams(window.location.search);
    const preSelectedService = urlParams.get('service');
    
    if (preSelectedService) {
      // Service name mapping for URL parameters
      const serviceMapping = {
        'hrudbasti': 'Hrudbasti',
        'katibasti': 'Kati Basti',
        'kati-basti': 'Kati Basti',
        'shirodhara': 'Shirodhara',
        'nasya': 'Nasya',
        'consultation': 'Ayurvedic Consultation',
        'pain-management': 'Massage & Wellness'
      };
      
      const serviceName = serviceMapping[preSelectedService] || preSelectedService;
      
      // Find and select the corresponding service card
      serviceCards.forEach(card => {
        const cardService = card.getAttribute('data-service');
        if (cardService && cardService.toLowerCase().includes(serviceName.toLowerCase())) {
          card.classList.add('selected');
          const radio = card.querySelector('input[type="radio"]');
          radio.checked = true;
        }
      });
    }
    
    // Form submission
    document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Get form data
      const formData = new FormData(this);
      
      // Get submit button and show loading state
      const submitBtn = this.querySelector('.btn-submit');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking...';
      
      try {
        // Send booking request
        const response = await fetch('booking.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.text();
        
        if (response.ok && result.includes('successfully')) {
          // Show success modal
          const modal = new bootstrap.Modal(document.getElementById('successModal'));
          modal.show();
          
          // Reset form and go back to step 1
          setTimeout(() => {
            this.reset();
            currentStep = 1;
            updateStep(currentStep);
          }, 2000);
          
        } else {
          // Show error
          throw new Error(result.replace(/[✅❌]/g, '').trim());
        }
        
      } catch (error) {
        console.error('Booking error:', error);
        alert('Error booking appointment: ' + error.message + '. Please try again.');
      } finally {
        // Reset submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
    
    // Social links hover effect
    document.querySelectorAll('.social-link').forEach(link => {
      link.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px) scale(1.05)';
      });
      link.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
      });
    });
    
    // Footer links hover effect
    document.querySelectorAll('.footer-link').forEach(link => {
      link.addEventListener('mouseenter', function() {
        this.style.color = 'var(--secondary-color)';
        this.style.transform = 'translateX(5px)';
      });
      link.addEventListener('mouseleave', function() {
        this.style.color = 'rgba(255, 255, 255, 0.8)';
        this.style.transform = 'translateX(0)';
      });
    });
  });
</script>

</body>
</html>
