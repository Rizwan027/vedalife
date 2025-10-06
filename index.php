<?php
session_start();

// Check if user is logged in for cart functionality
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes">
  <title>VEDAMRUT - Premium Ayurvedic Wellness</title>

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
      
      /* Base font size for consistent scaling */
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
      -ms-text-size-adjust: 100%;
      text-size-adjust: 100%;
      font-size: 100%;
    }

    body {
      font-family: 'Poppins', sans-serif;
      line-height: 1.6;
      color: var(--text-dark);
      background: var(--background-light);
      overflow-x: hidden;
      font-size: 1rem; /* Standard 16px base */
      -webkit-text-size-adjust: 100%;
      -moz-text-size-adjust: 100%;
      text-size-adjust: 100%;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      font-kerning: auto;
      font-variant-ligatures: common-ligatures;
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
      padding: 0.8rem 7%;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--transition-smooth);
    }

    .header.scrolled {
      padding: 0.6rem 7%;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.12);
    }

    .header .logo {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-dark);
      font-family: 'Cormorant Garamond', serif;
      letter-spacing: 0.8px;
      text-decoration: none;
    }

    .header .navbar {
      display: flex;
      align-items: center;
    }

    .header .navbar a {
      position: relative;
      font-size: 0.8rem; /* Smaller for better proportion */
      margin: 0 1.2rem;
      color: var(--text-dark);
      text-decoration: none;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.4px;
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
      padding: 0.6rem 1.2rem;
      font-size: 0.75rem;
      border-radius: 25px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.4px;
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
    }
    
    /* Cart Badge */
    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: var(--accent-color);
      color: white;
      font-size: 0.75rem;
      font-weight: bold;
      padding: 0.25rem 0.5rem;
      border-radius: 50px;
      min-width: 20px;
      text-align: center;
      line-height: 1;
    }
    
    /* Logout Button */
    .nav-buttons .btn-danger {
      background: var(--accent-color);
      color: white;
      border-color: var(--accent-color);
    }
    
    .nav-buttons .btn-danger:hover {
      background: #ff5252;
      border-color: #ff5252;
      color: white;
      transform: translateY(-2px);
      box-shadow: var(--shadow-soft);
    }

    /* Hero Section */
    .hero-section {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, rgba(44, 110, 73, 0.95) 0%, rgba(76, 149, 108, 0.85) 100%), url('images/background2.jpg') center/cover fixed;
      color: white;
      text-align: left;
      overflow: hidden;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 1200px;
      width: 100%;
      padding: 2rem;
    }

    .hero-subtitle {
      display: inline-block;
      background: var(--gradient-secondary);
      color: var(--text-dark);
      padding: 0.4rem 1.2rem;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 1.5rem;
    }

    .hero-title {
      font-size: clamp(2rem, 4vw, 3.2rem);
      font-weight: 700;
      line-height: 1.3;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #ffffff 0%, #e8f5ff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-text {
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 2rem;
      opacity: 0.95;
      max-width: 550px;
    }

    .hero-btn {
      padding: 0.8rem 2rem;
      font-size: 0.9rem;
      font-weight: 600;
      border-radius: 30px;
      text-decoration: none;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      transition: var(--transition-smooth);
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      margin: 0.4rem;
    }

    .hero-btn.primary {
      background: var(--gradient-secondary);
      color: var(--text-dark);
      border: 2px solid var(--secondary-color);
    }

    .hero-btn.primary:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-large);
      color: var(--text-dark);
    }

    /* Section Styling */
    .section {
      padding: 5rem 0;
    }

    .section-title {
      text-align: center;
      margin-bottom: 3rem;
    }

    .section-title h2 {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      color: var(--text-dark);
      margin-bottom: 1rem;
      position: relative;
    }

    .section-title h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--gradient-primary);
      border-radius: 2px;
    }

    .section-subtitle {
      font-size: 1.1rem;
      color: var(--text-light);
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.7;
    }

    .service-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      transition: var(--transition-smooth);
      border: 1px solid rgba(44, 110, 73, 0.1);
      height: 100%;
    }

    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-large);
    }

    .service-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }

    .service-content {
      padding: 2rem;
      text-align: center;
    }

    .service-content h3 {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 1rem;
    }

    .service-content p {
      color: var(--text-light);
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    .view-all-btn {
      display: inline-block;
      background: var(--gradient-primary);
      color: white;
      padding: 0.7rem 2rem;
      border-radius: 30px;
      font-weight: 600;
      font-size: 0.85rem;
      text-decoration: none;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      transition: var(--transition-smooth);
      margin-top: 1.5rem;
    }

    .view-all-btn:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-medium);
      color: white;
    }

    /* Footer */
    .footer {
      background: linear-gradient(135deg, var(--text-dark) 0%, #1a1a2e 100%);
      color: white;
      padding: 3rem 0 1.5rem;
    }

    .footer h3 {
      color: var(--secondary-color);
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.8rem;
    }

    .footer p {
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.7;
    }

    .social-link {
      width: 45px;
      height: 45px;
      background: var(--gradient-primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-decoration: none;
      transition: var(--transition-smooth);
      margin-right: 1rem;
    }

    .social-link:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: var(--shadow-medium);
    }

    /* Section Styles */
    .section {
      padding: 60px 0;
      position: relative;
    }
    
    .section-title {
      text-align: center;
      margin-bottom: 3rem;
    }
    
    .section-title h2 {
      font-size: clamp(1.8rem, 3.5vw, 2.2rem);
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Cormorant Garamond', serif;
      margin-bottom: 0.8rem;
    }
    
    .section-subtitle {
      font-size: 1rem;
      color: var(--text-light);
      max-width: 550px;
      margin: 0 auto;
    }
    
    /* Enhanced Service Cards */
    .service-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      transition: var(--transition-smooth);
      border: 1px solid rgba(44, 110, 73, 0.1);
      height: 100%;
      position: relative;
    }
    
    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: var(--gradient-primary);
      opacity: 0;
      transition: var(--transition-smooth);
      z-index: 1;
    }
    
    .service-card:hover {
      transform: translateY(-15px);
      box-shadow: var(--shadow-large);
    }
    
    .service-card:hover::before {
      opacity: 0.03;
    }
    
    .service-image {
      position: relative;
      height: 250px;
      overflow: hidden;
    }
    
    .service-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition-smooth);
    }
    
    .service-card:hover .service-image img {
      transform: scale(1.1);
    }
    
    .service-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(44, 110, 73, 0.7) 0%, rgba(76, 149, 108, 0.5) 100%);
      opacity: 0;
      transition: var(--transition-smooth);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .service-card:hover .service-overlay {
      opacity: 1;
    }
    
    .quick-view-btn {
      background: white;
      color: var(--primary-color);
      padding: 0.8rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      text-decoration: none;
      transform: translateY(20px);
      transition: var(--transition-smooth);
    }
    
    .service-card:hover .quick-view-btn {
      transform: translateY(0);
    }
    
    .service-content {
      padding: 1.5rem;
      position: relative;
      z-index: 2;
    }
    
    .service-content h3 {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.6rem;
      line-height: 1.3;
    }
    
    .service-content p {
      color: var(--text-light);
      margin-bottom: 1rem;
      line-height: 1.6;
      font-size: 0.9rem;
    }
    
    /* Global text scaling improvements */
    h1, h2, h3, h4, h5, h6 {
      font-size-adjust: none;
    }
    
    /* Ensure all text elements respect zoom levels */
    p, div, span, li, a {
      font-size-adjust: none;
    }
    
    @media (max-width: 768px) {
      .header .navbar {
        display: none;
      }
      
      .hero-content {
        text-align: center;
      }
      
      .hero-btn {
        display: block;
        margin: 1rem auto;
        text-align: center;
      }
      
      .section {
        padding: 40px 0;
      }
      
      .section-title h2 {
        font-size: 1.6rem;
      }
      
      .service-card {
        margin-bottom: 2rem;
      }
    }
  </style>
</head>
<body>

<!-- Enhanced Navbar -->
<header class="header" id="navbar">
  <a href="index.php" class="logo">🌿VEDAMRUT</a>
  
  <nav class="navbar">
    <a href="#home" class="active">Home</a>
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

<!-- Enhanced Hero Section -->
<section id="home" class="hero-section">
  <div class="hero-content" data-aos="fade-up">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="hero-subtitle" data-aos="fade-up" data-aos-delay="200">PREMIUM AYURVEDIC WELLNESS</span>
        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="400">Transform Your Life with Ancient Wisdom</h1>
        <p class="hero-text" data-aos="fade-up" data-aos-delay="600">Experience authentic Ayurvedic healing that harmonizes mind, body, and spirit. Our premium treatments and natural products bring centuries-old wellness traditions to modern life.</p>
        
        <div data-aos="fade-up" data-aos-delay="800">
           <a href="appointment.php" class="hero-btn primary">
             <i class="fas fa-calendar-check"></i> Book Consultation
            </a>
        </div>
      </div>
      
      <div class="col-lg-4 d-none d-lg-block">
        <div class="position-relative" data-aos="fade-left" data-aos-delay="600">
          <img src="images/sir.jpg" alt="Ayurveda Expert" class="img-fluid" style="border-radius: var(--border-radius); box-shadow: var(--shadow-large); max-width: 400px;">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Services Section -->
<section id="services" class="section bg-white">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h2>Our Premium Services</h2>
      <p class="section-subtitle">Experience authentic Ayurvedic treatments designed to restore balance and promote wellness.</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="service-card">
          <div class="service-image">
            <img src="images/kati basti.jpg" alt="Kati Basti">
            <div class="service-overlay">
              <a href="services.php" class="quick-view-btn">Learn More</a>
            </div>
          </div>
          <div class="service-content">
            <h3>Kati Basti</h3>
            <p>Specialized lower back treatment using warm herbal oils to alleviate pain and improve spinal health naturally.</p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="service-card">
          <div class="service-image">
            <img src="images/hrudbasti.jpg" alt="Hrudbasti">
            <div class="service-overlay">
              <a href="services.php" class="quick-view-btn">Learn More</a>
            </div>
          </div>
          <div class="service-content">
            <h3>Hrudbasti</h3>
            <p>Heart-focused therapy using medicated oils to strengthen cardiac muscles and improve circulation.</p>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="service-card">
          <div class="service-image">
            <img src="images/nasya.jpg" alt="Nasya">
            <div class="service-overlay">
              <a href="services.php" class="quick-view-btn">Learn More</a>
            </div>
          </div>
          <div class="service-content">
            <h3>Nasya Therapy</h3>
            <p>Nasal administration of medicated oils to cleanse and strengthen the head and neck region.</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="text-center">
      <a href="services.php" class="view-all-btn" data-aos="fade-up" data-aos-delay="400">
        <i class="fas fa-spa"></i> View All Services
      </a>
    </div>
  </div>
</section>

<!-- Products Section -->
<section id="products" class="section" style="background: var(--background-light);">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h2>Premium Ayurvedic Products</h2>
      <p class="section-subtitle">Discover our curated collection of authentic Ayurvedic products for holistic wellness.</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="service-card" style="background: var(--background-white); border: 1px solid rgba(44, 110, 73, 0.1);">
          <img src="images/milk.png" alt="Herbal Oil" style="height: 200px; object-fit: cover;">
          <div class="service-content" style="text-align: center;">
            <h3>Premium Herbal Oils</h3>
            <p>Authentic massage oils made with traditional recipes for deep relaxation and healing.</p>
            <div style="color: var(--primary-color); font-weight: bold; font-size: 1.2rem; margin-top: 1rem;">Starting ₹299</div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="service-card" style="background: var(--background-white); border: 1px solid rgba(44, 110, 73, 0.1);">
          <img src="images/chyawanprash.png" alt="Chyawanprash" style="height: 200px; object-fit: cover;">
          <div class="service-content" style="text-align: center;">
            <h3>Ayurvedic Supplements</h3>
            <p>Premium quality supplements including Chyawanprash, Ashwagandha, and herbal powders.</p>
            <div style="color: var(--primary-color); font-weight: bold; font-size: 1.2rem; margin-top: 1rem;">Starting ₹199</div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="service-card" style="background: var(--background-white); border: 1px solid rgba(44, 110, 73, 0.1);">
          <img src="images/tulsi.png" alt="Herbal Tea" style="height: 200px; object-fit: cover;">
          <div class="service-content" style="text-align: center;">
            <h3>Herbal Teas & Wellness</h3>
            <p>Organic herbal teas and wellness products to support your daily health routine naturally.</p>
            <div style="color: var(--primary-color); font-weight: bold; font-size: 1.2rem; margin-top: 1rem;">Starting ₹149</div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="text-center">
      <a href="products.php" class="view-all-btn" data-aos="fade-up" data-aos-delay="400">
        <i class="fas fa-shopping-bag"></i> Shop All Products
      </a>
    </div>
  </div>
</section>

<!-- Packages Section -->
<section id="pricing" class="section bg-white">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h2>Wellness Packages</h2>
      <p class="section-subtitle">Choose from our comprehensive wellness packages designed for your specific needs.</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="service-card" style="background: var(--background-white); border: 2px solid var(--primary-light); position: relative; text-align: center;">
          <div style="background: var(--gradient-secondary); color: var(--text-dark); padding: 0.6rem 1.2rem; border-radius: 25px; position: absolute; top: -15px; left: 50%; transform: translateX(-50%); font-weight: bold; font-size: 1rem; box-shadow: var(--shadow-soft);">BASIC</div>
          <div class="service-content" style="padding-top: 2rem;">
            <h3>Essential Wellness</h3>
            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin: 1rem 0;">₹2,999</div>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">Perfect for beginners to Ayurvedic wellness</p>
            <ul style="list-style: none; padding: 0; text-align: left;">
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Initial Consultation</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>2 Basic Treatments</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Herbal Medicine Kit</li>
              <li style="padding: 0.5rem 0;"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Follow-up Session</li>
            </ul>
            <a href="appointment.php" style="display: inline-block; background: var(--gradient-primary); color: white; padding: 0.8rem 2rem; border-radius: 50px; text-decoration: none; margin-top: 1.5rem; transition: var(--transition-smooth);">Get Started</a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="service-card" style="background: var(--background-white); border: 3px solid var(--primary-color); position: relative; text-align: center; transform: scale(1.05);">
          <div style="background: var(--gradient-primary); color: white; padding: 0.6rem 1.2rem; border-radius: 25px; position: absolute; top: -15px; left: 50%; transform: translateX(-50%); font-weight: bold; font-size: 1rem; box-shadow: var(--shadow-soft);">POPULAR</div>
          <div class="service-content" style="padding-top: 2rem;">
            <h3>Complete Wellness</h3>
            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin: 1rem 0;">₹5,999</div>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">Most popular comprehensive package</p>
            <ul style="list-style: none; padding: 0; text-align: left;">
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Detailed Consultation</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>5 Premium Treatments</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Complete Medicine Kit</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Diet & Lifestyle Plan</li>
              <li style="padding: 0.5rem 0;"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>3 Follow-up Sessions</li>
            </ul>
            <a href="appointment.php" style="display: inline-block; background: var(--gradient-primary); color: white; padding: 0.8rem 2rem; border-radius: 50px; text-decoration: none; margin-top: 1.5rem; transition: var(--transition-smooth);">Choose Plan</a>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="service-card" style="background: var(--background-white); border: 2px solid var(--secondary-color); position: relative; text-align: center;">
          <div style="background: var(--gradient-secondary); color: var(--text-dark); padding: 0.6rem 1.2rem; border-radius: 25px; position: absolute; top: -15px; left: 50%; transform: translateX(-50%); font-weight: bold; font-size: 1rem; box-shadow: var(--shadow-soft);">PREMIUM</div>
          <div class="service-content" style="padding-top: 2rem;">
            <h3>Luxury Wellness</h3>
            <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color); margin: 1rem 0;">₹9,999</div>
            <p style="color: var(--text-light); margin-bottom: 1.5rem;">Ultimate luxury wellness experience</p>
            <ul style="list-style: none; padding: 0; text-align: left;">
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>VIP Consultation</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>10 Luxury Treatments</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Premium Product Bundle</li>
              <li style="padding: 0.5rem 0; border-bottom: 1px solid rgba(44, 110, 73, 0.1);"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>Personal Wellness Coach</li>
              <li style="padding: 0.5rem 0;"><i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>6 Months Support</li>
            </ul>
            <a href="appointment.php" style="display: inline-block; background: var(--gradient-primary); color: white; padding: 0.8rem 2rem; border-radius: 50px; text-decoration: none; margin-top: 1.5rem; transition: var(--transition-smooth);">Go Premium</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="section" style="background: var(--background-light);">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h2>What Our Clients Say</h2>
      <p class="section-subtitle">Real experiences from people who transformed their lives with our Ayurvedic treatments.</p>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="service-card" style="background: var(--background-white); border: 1px solid rgba(44, 110, 73, 0.1); text-align: center; position: relative; padding: 2rem;">
          <div style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-user" style="color: white; font-size: 1.5rem;"></i>
          </div>
          <div style="margin-top: 2rem;">
            <div style="color: var(--secondary-color); font-size: 1.5rem; margin-bottom: 1rem;">⭐⭐⭐⭐⭐</div>
            <p style="font-style: italic; color: var(--text-light); margin-bottom: 1.5rem;">"VedaLife's Panchakarma treatment completely transformed my health. I feel more energetic and balanced than I have in years. The doctors are incredibly knowledgeable and caring."</p>
            <h5 style="color: var(--primary-color); margin-bottom: 0.5rem;">Priya Sharma</h5>
            <small style="color: var(--text-light);">Mumbai, Maharashtra</small>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="service-card" style="background: var(--background-white); border: 1px solid rgba(44, 110, 73, 0.1); text-align: center; position: relative; padding: 2rem;">
          <div style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-user" style="color: white; font-size: 1.5rem;"></i>
          </div>
          <div style="margin-top: 2rem;">
            <div style="color: var(--secondary-color); font-size: 1.5rem; margin-bottom: 1rem;">⭐⭐⭐⭐⭐</div>
            <p style="font-style: italic; color: var(--text-light); margin-bottom: 1.5rem;">"The Shirodhara therapy helped me overcome chronic stress and anxiety. The entire team is professional and the treatments are authentic. Highly recommended!"</p>
            <h5 style="color: var(--primary-color); margin-bottom: 0.5rem;">Rajesh Kumar</h5>
            <small style="color: var(--text-light);">Delhi, NCR</small>
          </div>
        </div>
      </div>
      
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="service-card" style="background: var(--background-white); border: 1px solid rgba(44, 110, 73, 0.1); text-align: center; position: relative; padding: 2rem;">
          <div style="position: absolute; top: -20px; left: 50%; transform: translateX(-50%); width: 60px; height: 60px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-user" style="color: white; font-size: 1.5rem;"></i>
          </div>
          <div style="margin-top: 2rem;">
            <div style="color: var(--secondary-color); font-size: 1.5rem; margin-bottom: 1rem;">⭐⭐⭐⭐⭐</div>
            <p style="font-style: italic; color: var(--text-light); margin-bottom: 1.5rem;">"Amazing experience with their herbal products and consultations. My digestive issues are completely resolved, and I feel healthier overall. Thank you VedaLife team!"</p>
            <h5 style="color: var(--primary-color); margin-bottom: 0.5rem;">Anita Mehta</h5>
            <small style="color: var(--text-light);">Bangalore, Karnataka</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section bg-white">
  <div class="container">
    <div class="section-title" data-aos="fade-up">
      <h2>Get In Touch</h2>
      <p class="section-subtitle">Ready to start your wellness journey? Contact us today for a personalized consultation.</p>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-lg-8 col-xl-7" data-aos="fade-up" data-aos-delay="100">
        <div style="background: var(--background-light); padding: 3rem; border-radius: var(--border-radius); border: 1px solid rgba(44, 110, 73, 0.1); box-shadow: var(--shadow-soft);">
          <h4 style="color: var(--primary-color); margin-bottom: 2rem; text-align: center; font-size: 1.5rem;">Send Us a Message</h4>
          <form>
            <div class="row g-3">
              <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Your Name" style="padding: 1rem; border-radius: 15px; border: 2px solid rgba(44, 110, 73, 0.1); font-size: 1rem;" required>
              </div>
              <div class="col-md-6">
                <input type="email" class="form-control" placeholder="Your Email" style="padding: 1rem; border-radius: 15px; border: 2px solid rgba(44, 110, 73, 0.1); font-size: 1rem;" required>
              </div>
              <div class="col-12">
                <input type="text" class="form-control" placeholder="Subject" style="padding: 1rem; border-radius: 15px; border: 2px solid rgba(44, 110, 73, 0.1); font-size: 1rem;" required>
              </div>
              <div class="col-12">
                <textarea class="form-control" rows="5" placeholder="Your Message" style="padding: 1rem; border-radius: 15px; border: 2px solid rgba(44, 110, 73, 0.1); resize: vertical; font-size: 1rem;" required></textarea>
              </div>
              <div class="col-12 text-center">
                <button type="submit" style="background: var(--gradient-primary); color: white; border: none; padding: 1.2rem 3.5rem; border-radius: 50px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; transition: var(--transition-smooth); font-size: 1rem; box-shadow: var(--shadow-soft);">Send Message</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <h3>🌿VEDAMRUT</h3>
        <p class="mb-4">Transform your life with authentic Ayurvedic healing. Experience ancient wisdom through our premium treatments and natural products.</p>
        
        <div class="d-flex">
          <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      
      <div class="col-lg-2 col-md-6">
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Quick Links</h5>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 0.8rem;"><a href="#home" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Home</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Services</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="products.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Products</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="#contact" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Contact</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Our Services</h5>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Ayurvedic Consultation</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Therapeutic Treatments</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="services.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Wellness Programs</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="products.php" style="color: rgba(255, 255, 255, 0.8); text-decoration: none;">Natural Products</a></li>
        </ul>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem;">Contact Info</h5>
        <div style="color: rgba(255, 255, 255, 0.8);">
          <div style="margin-bottom: 1rem; display: flex; align-items: flex-start; gap: 1rem;">
            <i class="fas fa-map-marker-alt" style="color: var(--primary-light); margin-top: 0.2rem;"></i>
            <div>
              <strong>Address:</strong><br>
              123 Wellness Lane<br>
              Ayurveda District, Health City
            </div>
          </div>
          
          <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-envelope" style="color: var(--primary-light);"></i>
            <div>
              <strong>Email:</strong><br>
              <a href="mailto:info@vedamrut.com" style="color: var(--secondary-color); text-decoration: none;">info@vedamrut.com</a>
            </div>
          </div>
          
          <div style="display: flex; align-items: center; gap: 1rem;">
            <i class="fas fa-phone" style="color: var(--primary-light);"></i>
            <div>
              <strong>Phone:</strong><br>
              <a href="tel:+917382947582" style="color: var(--secondary-color); text-decoration: none;">+91 73829 47582</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 3rem 0 2rem;">
    
    <div class="row align-items-center">
      <div class="col-md-6">
        <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">&copy; 2024 VEDAMRUT. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">Designed with ❤️ for holistic wellness</p>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- JavaScript -->
<script>
  // Initialize AOS
  AOS.init({
    duration: 1000,
    once: true,
    offset: 100
  });
  
  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 100) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });
  
  // Smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
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
  
  // Initialize cart badge on page load
  document.addEventListener('DOMContentLoaded', updateCartCounter);
</script>

</body>
</html>