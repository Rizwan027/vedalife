<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes"/>
  <title>Premium Ayurvedic Services - VEDAMRUT</title>
  
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- AOS Animation Library -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  
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
      --green: #28a745;
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
      font-size: 0.8rem;
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
    }

    .cart-badge {
      position: absolute;
      top: -5px;
      right: -10px;
      background: var(--accent-color);
      color: white;
      font-size: 0.7rem;
      padding: 0.2rem 0.5rem;
      border-radius: 50%;
      font-weight: bold;
      min-width: 20px;
      text-align: center;
    }

    /* Hero Section */
    .hero-section {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, rgba(44, 110, 73, 0.9) 0%, rgba(76, 149, 108, 0.8) 100%), url('images/background3.jpg') center/cover;
      color: white;
      text-align: center;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(44, 110, 73, 0.1) 0%, rgba(76, 149, 108, 0.1) 100%);
      z-index: 1;
    }

    .hero-content {
      position: relative;
      z-index: 2;
      max-width: 800px;
      padding: 2rem;
    }

    .hero-content h1 {
      font-size: clamp(2.5rem, 5vw, 4rem);
      font-weight: 800;
      margin-bottom: 1.5rem;
      background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1.2;
    }

    .hero-content p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
      opacity: 0.95;
      font-weight: 400;
    }

    .hero-stats {
      display: flex;
      justify-content: center;
      gap: 3rem;
      margin-top: 3rem;
      flex-wrap: wrap;
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      color: var(--secondary-color);
      display: block;
    }

    .stat-label {
      font-size: 0.9rem;
      opacity: 0.9;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* Floating particles */
    .floating-elements {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 1;
    }

    .floating-elements .element {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Service Categories */
    .service-categories {
      padding: 5rem 0;
      background: white;
    }

    .category-filters {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 3rem;
      flex-wrap: wrap;
    }

    .filter-btn {
      padding: 0.8rem 2rem;
      background: transparent;
      border: 2px solid var(--primary-light);
      color: var(--primary-color);
      border-radius: 50px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition-smooth);
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }

    .filter-btn.active,
    .filter-btn:hover {
      background: var(--gradient-primary);
      color: white;
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: var(--shadow-soft);
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
      padding: 2rem;
      position: relative;
      z-index: 2;
    }

    .service-badge {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: var(--gradient-secondary);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      z-index: 3;
    }

    .service-content h3 {
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 0.8rem;
      line-height: 1.3;
    }

    .service-content p {
      color: var(--text-light);
      margin-bottom: 1.5rem;
      line-height: 1.6;
      font-size: 0.95rem;
    }

    .service-features {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .feature-tag {
      background: rgba(44, 110, 73, 0.1);
      color: var(--primary-color);
      padding: 0.3rem 0.8rem;
      border-radius: 15px;
      font-size: 0.75rem;
      font-weight: 500;
    }

    .service-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding: 1rem;
      background: var(--background-light);
      border-radius: 12px;
      border: 1px solid rgba(44, 110, 73, 0.1);
    }

    .service-price {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--primary-color);
    }

    .service-duration {
      color: var(--text-light);
      font-size: 0.85rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .service-rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--secondary-color);
      font-size: 0.85rem;
    }

    .book-now-btn {
      display: block;
      width: 100%;
      background: var(--gradient-primary);
      color: white;
      padding: 1rem 2rem;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 600;
      text-align: center;
      transition: var(--transition-smooth);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      position: relative;
      overflow: hidden;
    }

    .book-now-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transition: var(--transition-smooth);
    }

    .book-now-btn:hover::before {
      left: 100%;
    }

    .book-now-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
      color: white;
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
  </style>
</head>
<body>
  <!-- Enhanced Navbar -->
  <header class="header" id="navbar">
    <a href="index.php" class="logo">🌿VEDAMRUT</a>
    
    <nav class="navbar">
      <a href="index.php">Home</a>
      <a href="services.php" class="active">Services</a>
      <a href="products.php">Products</a>
      <a href="index.php#pricing">Packages</a>
      <a href="index.php#testimonials">Testimonials</a>
      <a href="index.php#contact">Contact</a>
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

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="floating-elements">
      <div class="element" style="width: 60px; height: 60px; top: 20%; left: 10%; animation-delay: 0s;"></div>
      <div class="element" style="width: 40px; height: 40px; top: 60%; left: 85%; animation-delay: 2s;"></div>
      <div class="element" style="width: 80px; height: 80px; top: 80%; left: 15%; animation-delay: 4s;"></div>
      <div class="element" style="width: 30px; height: 30px; top: 30%; left: 80%; animation-delay: 1s;"></div>
    </div>
    
    <div class="hero-content" data-aos="fade-up">
      <h1>Premium Ayurvedic Services</h1>
      <p>Experience authentic healing through time-tested Ayurvedic treatments designed to restore balance, promote wellness, and rejuvenate your mind, body, and soul.</p>
      
      <div class="hero-stats" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-item">
          <span class="stat-number">500+</span>
          <span class="stat-label">Happy Clients</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">15+</span>
          <span class="stat-label">Treatments</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">10+</span>
          <span class="stat-label">Years Experience</span>
        </div>
        <div class="stat-item">
          <span class="stat-number">98%</span>
          <span class="stat-label">Success Rate</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Service Categories with Filtering -->
  <section class="service-categories section">
    <div class="container">
      <div class="section-title" data-aos="fade-up">
        <h2>Our Premium Services</h2>
        <p class="section-subtitle">Choose from our comprehensive range of authentic Ayurvedic treatments, each designed to address specific health concerns and promote overall wellness.</p>
      </div>
      
      <!-- Category Filters -->
      <div class="category-filters" data-aos="fade-up" data-aos-delay="200">
        <button class="filter-btn active" data-filter="all">All Services</button>
        <button class="filter-btn" data-filter="therapeutic">Therapeutic</button>
        <button class="filter-btn" data-filter="relaxation">Relaxation</button>
        <button class="filter-btn" data-filter="consultation">Consultation</button>
        <button class="filter-btn" data-filter="specialized">Specialized</button>
      </div>
      
      <div class="row g-4" id="services-container">
        <!-- Service 1: Hrudbasti -->
        <div class="col-lg-4 col-md-6 service-item" data-category="therapeutic">
          <div class="service-card" data-aos="fade-up">
            <div class="service-image">
              <img src="images/hrudbasti.jpg" alt="Hrudbasti Heart Therapy">
              <div class="service-overlay">
                <a href="#" class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="showServiceDetails('hrudbasti')">Quick View</a>
              </div>
              <div class="service-badge">Therapeutic</div>
            </div>
            <div class="service-content">
              <h3>Hrudbasti</h3>
              <p>Specialized heart therapy using warm medicated oils to strengthen cardiac muscles and improve circulation. Effective for heart-related ailments and stress relief.</p>
              
              <div class="service-features">
                <span class="feature-tag">Heart Health</span>
                <span class="feature-tag">Circulation</span>
                <span class="feature-tag">Stress Relief</span>
              </div>
              
              <div class="service-meta">
                <div>
                  <div class="service-price">₹2,500</div>
                  <div class="service-duration"><i class="fas fa-clock"></i> 60 mins</div>
                </div>
                <div class="service-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span>4.9</span>
                </div>
              </div>
              
               <a href="appointment.php?service=hrudbasti" class="book-now-btn">
                <i class="fas fa-calendar-alt"></i> Book Now
              </a>
            </div>
          </div>
        </div>
        
        <!-- Service 2: Kati Basti -->
        <div class="col-lg-4 col-md-6 service-item" data-category="therapeutic">
          <div class="service-card" data-aos="fade-up" data-aos-delay="100">
            <div class="service-image">
              <img src="images/kati basti.jpg" alt="Kati Basti Lower Back Therapy">
              <div class="service-overlay">
                <a href="#" class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="showServiceDetails('katibasti')">Quick View</a>
              </div>
              <div class="service-badge">Popular</div>
            </div>
            <div class="service-content">
              <h3>Kati Basti</h3>
              <p>Targeted lower back treatment using warm herbal oils to alleviate pain, stiffness, and improve spinal health. Ideal for chronic back pain and sciatica.</p>
              
              <div class="service-features">
                <span class="feature-tag">Back Pain</span>
                <span class="feature-tag">Sciatica</span>
                <span class="feature-tag">Spine Health</span>
              </div>
              
              <div class="service-meta">
                <div>
                  <div class="service-price">₹2,200</div>
                  <div class="service-duration"><i class="fas fa-clock"></i> 45 mins</div>
                </div>
                <div class="service-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span>4.8</span>
                </div>
              </div>
              
               <a href="appointment.php?service=katibasti" class="book-now-btn">
                <i class="fas fa-calendar-alt"></i> Book Now
              </a>
            </div>
          </div>
        </div>
        
        <!-- Service 3: Shirodhara -->
        <div class="col-lg-4 col-md-6 service-item" data-category="relaxation">
          <div class="service-card" data-aos="fade-up" data-aos-delay="200">
            <div class="service-image">
              <img src="images/shirodhara.jpg" alt="Shirodhara Relaxation Therapy">
              <div class="service-overlay">
                <a href="#" class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="showServiceDetails('shirodhara')">Quick View</a>
              </div>
              <div class="service-badge">Most Popular</div>
            </div>
            <div class="service-content">
              <h3>Shirodhara</h3>
              <p>Ultimate relaxation therapy where warm herbal oil flows continuously over the forehead. Promotes deep relaxation, mental clarity, and stress relief.</p>
              
              <div class="service-features">
                <span class="feature-tag">Stress Relief</span>
                <span class="feature-tag">Mental Clarity</span>
                <span class="feature-tag">Relaxation</span>
              </div>
              
              <div class="service-meta">
                <div>
                  <div class="service-price">₹3,000</div>
                  <div class="service-duration"><i class="fas fa-clock"></i> 75 mins</div>
                </div>
                <div class="service-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span>5.0</span>
                </div>
              </div>
              
               <a href="appointment.php?service=shirodhara" class="book-now-btn">
                <i class="fas fa-calendar-alt"></i> Book Now
              </a>
            </div>
          </div>
        </div>
        
        <!-- Service 4: Nasya -->
        <div class="col-lg-4 col-md-6 service-item" data-category="therapeutic">
          <div class="service-card" data-aos="fade-up" data-aos-delay="300">
            <div class="service-image">
              <img src="images/nasya.jpg" alt="Nasya Nasal Therapy">
              <div class="service-overlay">
                <a href="#" class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="showServiceDetails('nasya')">Quick View</a>
              </div>
            </div>
            <div class="service-content">
              <h3>Nasya Therapy</h3>
              <p>Nasal administration of medicated oils to cleanse and strengthen the head and neck region. Effective for sinusitis, migraines, and respiratory issues.</p>
              
              <div class="service-features">
                <span class="feature-tag">Sinusitis</span>
                <span class="feature-tag">Migraines</span>
                <span class="feature-tag">Respiratory</span>
              </div>
              
              <div class="service-meta">
                <div>
                  <div class="service-price">₹1,800</div>
                  <div class="service-duration"><i class="fas fa-clock"></i> 30 mins</div>
                </div>
                <div class="service-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
                  <span>4.7</span>
                </div>
              </div>
              
               <a href="appointment.php?service=nasya" class="book-now-btn">
                <i class="fas fa-calendar-alt"></i> Book Now
              </a>
            </div>
          </div>
        </div>
        
        <!-- Service 5: Pain Management -->
        <div class="col-lg-4 col-md-6 service-item" data-category="specialized">
          <div class="service-card" data-aos="fade-up" data-aos-delay="400">
            <div class="service-image">
              <img src="images/pain.png" alt="Pain Management Therapy">
              <div class="service-overlay">
                <a href="#" class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="showServiceDetails('pain-management')">Quick View</a>
              </div>
              <div class="service-badge">New</div>
            </div>
            <div class="service-content">
              <h3>Pain Management</h3>
              <p>Comprehensive pain relief program combining multiple Ayurvedic therapies to address chronic pain conditions and improve mobility naturally.</p>
              
              <div class="service-features">
                <span class="feature-tag">Chronic Pain</span>
                <span class="feature-tag">Mobility</span>
                <span class="feature-tag">Natural Relief</span>
              </div>
              
              <div class="service-meta">
                <div>
                  <div class="service-price">₹3,500</div>
                  <div class="service-duration"><i class="fas fa-clock"></i> 90 mins</div>
                </div>
                <div class="service-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span>4.9</span>
                </div>
              </div>
              
               <a href="appointment.php?service=pain-management" class="book-now-btn">
                <i class="fas fa-calendar-alt"></i> Book Now
              </a>
            </div>
          </div>
        </div>
        
        <!-- Service 6: Ayurvedic Consultation -->
        <div class="col-lg-4 col-md-6 service-item" data-category="consultation">
          <div class="service-card" data-aos="fade-up" data-aos-delay="500">
            <div class="service-image">
              <img src="images/clinic.jpg" alt="Ayurvedic Consultation">
              <div class="service-overlay">
                <a href="#" class="quick-view-btn" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="showServiceDetails('consultation')">Quick View</a>
              </div>
              <div class="service-badge">Essential</div>
            </div>
            <div class="service-content">
              <h3>Ayurvedic Consultation</h3>
              <p>Comprehensive health assessment by certified Ayurvedic practitioners. Get personalized treatment plans and lifestyle recommendations.</p>
              
              <div class="service-features">
                <span class="feature-tag">Health Assessment</span>
                <span class="feature-tag">Personalized Plan</span>
                <span class="feature-tag">Expert Guidance</span>
              </div>
              
              <div class="service-meta">
                <div>
                  <div class="service-price">₹1,200</div>
                  <div class="service-duration"><i class="fas fa-clock"></i> 45 mins</div>
                </div>
                <div class="service-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <span>4.8</span>
                </div>
              </div>
              
               <a href="appointment.php?service=consultation" class="book-now-btn">
                <i class="fas fa-calendar-alt"></i> Book Now
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Service Details Modal -->
  <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: var(--shadow-large);">
        <div class="modal-header" style="border-bottom: 1px solid rgba(44, 110, 73, 0.1); background: var(--gradient-primary); color: white; border-radius: 20px 20px 0 0;">
          <h5 class="modal-title" id="serviceModalLabel">Service Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="serviceModalBody" style="padding: 2rem;">
          <!-- Service details will be populated here -->
        </div>
        <div class="modal-footer" style="border-top: 1px solid rgba(44, 110, 73, 0.1); padding: 1.5rem 2rem;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 50px;">Close</button>
          <a href="#" id="modalBookBtn" class="btn" style="background: var(--gradient-primary); color: white; border-radius: 50px; padding: 0.8rem 2rem;">Book Now</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Why Choose Us Section -->
  <section class="section" style="background: var(--background-light);">
    <div class="container">
      <div class="section-title" data-aos="fade-up">
        <h2>Why Choose VEDAMRUT?</h2>
        <p class="section-subtitle">Experience the difference with our authentic Ayurvedic approach to wellness and healing.</p>
      </div>
      
      <div class="row g-4">
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
          <div class="text-center p-4" style="background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft); height: 100%;">
            <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
              <i class="fas fa-user-md"></i>
            </div>
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Expert Practitioners</h4>
            <p style="color: var(--text-light); font-size: 0.95rem;">Certified Ayurvedic doctors with years of experience in traditional healing methods.</p>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="text-center p-4" style="background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft); height: 100%;">
            <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
              <i class="fas fa-leaf"></i>
            </div>
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Natural Ingredients</h4>
            <p style="color: var(--text-light); font-size: 0.95rem;">Only the finest organic herbs and oils sourced from trusted suppliers.</p>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
          <div class="text-center p-4" style="background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft); height: 100%;">
            <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
              <i class="fas fa-heart"></i>
            </div>
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Personalized Care</h4>
            <p style="color: var(--text-light); font-size: 0.95rem;">Customized treatment plans tailored to your unique constitution and health needs.</p>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
          <div class="text-center p-4" style="background: white; border-radius: var(--border-radius); box-shadow: var(--shadow-soft); height: 100%;">
            <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: white; font-size: 2rem;">
              <i class="fas fa-spa"></i>
            </div>
            <h4 style="color: var(--text-dark); margin-bottom: 1rem;">Holistic Approach</h4>
            <p style="color: var(--text-light); font-size: 0.95rem;">Complete wellness solutions addressing mind, body, and spirit harmony.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Enhanced Footer -->
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
            <li style="margin-bottom: 0.8rem;"><a href="#" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link"><i class="fas fa-envelope" style="margin-right: 0.5rem; color: var(--primary-light);"></i>Contact</a></li>
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
                <a href="tel:+1234567890" style="color: var(--secondary-color); text-decoration: none;">+1 (234) 567-8900</a>
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

  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- AOS Animation -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  
  <!-- Enhanced JavaScript -->
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

    // Service filtering
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize cart counter on page load
      updateCartCounter();
      
      const filterBtns = document.querySelectorAll('.filter-btn');
      const serviceItems = document.querySelectorAll('.service-item');
      
      filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const filter = this.getAttribute('data-filter');
          
          // Update active filter button
          filterBtns.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          
          // Filter service items
          serviceItems.forEach(item => {
            const category = item.getAttribute('data-category');
            
            if (filter === 'all' || category === filter) {
              item.style.display = 'block';
              item.style.animation = 'fadeInUp 0.6s ease';
            } else {
              item.style.display = 'none';
            }
          });
        });
      });
    });
    
    // Service details data
    const serviceDetails = {
      hrudbasti: {
        title: 'Hrudbasti - Heart Therapy',
        image: 'images/hrudbasti.jpg',
        price: '₹2,500',
        duration: '60 minutes',
        description: 'Hrudbasti is a specialized Ayurvedic therapy that focuses on strengthening the heart and improving cardiovascular health. This treatment involves creating a reservoir of warm medicated oil over the heart region.',
        benefits: [
          'Strengthens heart muscles',
          'Improves blood circulation',
          'Reduces stress and anxiety',
          'Balances heart chakra',
          'Prevents cardiac ailments'
        ],
        process: [
          'Initial consultation and pulse diagnosis',
          'Preparation of medicated oils',
          'Creating oil reservoir over heart region',
          'Gentle massage and oil retention',
          'Post-treatment rest and guidance'
        ],
         bookingUrl: 'appointment.php?service=hrudbasti'
      },
      katibasti: {
        title: 'Kati Basti - Lower Back Therapy',
        image: 'images/kati basti.jpg',
        price: '₹2,200',
        duration: '45 minutes',
        description: 'Kati Basti is an effective treatment for lower back pain and spinal disorders. Warm medicated oil is retained over the lower back region to provide deep healing.',
        benefits: [
          'Relieves chronic back pain',
          'Treats sciatica naturally',
          'Improves spinal flexibility',
          'Strengthens back muscles',
          'Reduces inflammation'
        ],
        process: [
          'Assessment of back condition',
          'Selection of appropriate medicated oils',
          'Creating oil pool over affected area',
          'Oil retention therapy',
          'Gentle massage and aftercare'
        ],
         bookingUrl: 'appointment.php?service=katibasti'
      },
      shirodhara: {
        title: 'Shirodhara - Ultimate Relaxation',
        image: 'images/shirodhara.jpg',
        price: '₹3,000',
        duration: '75 minutes',
        description: 'Shirodhara is the crown jewel of Ayurvedic treatments. Warm herbal oil flows continuously over the forehead, inducing deep relaxation and mental clarity.',
        benefits: [
          'Deep mental relaxation',
          'Reduces stress and anxiety',
          'Improves sleep quality',
          'Enhances mental clarity',
          'Balances nervous system'
        ],
        process: [
          'Pre-treatment consultation',
          'Preparation of herbal oils',
          'Comfortable positioning',
          'Continuous oil pouring therapy',
          'Post-treatment meditation time'
        ],
         bookingUrl: 'appointment.php?service=shirodhara'
      },
      nasya: {
        title: 'Nasya - Nasal Therapy',
        image: 'images/nasya.jpg',
        price: '₹1,800',
        duration: '30 minutes',
        description: 'Nasya therapy involves administration of medicated oils through the nasal passages to cleanse and strengthen the head and neck region.',
        benefits: [
          'Clears nasal congestion',
          'Treats sinusitis effectively',
          'Relieves headaches and migraines',
          'Improves breathing',
          'Enhances mental clarity'
        ],
        process: [
          'Nasal examination',
          'Steam inhalation preparation',
          'Gentle nasal oil administration',
          'Therapeutic massage',
          'Post-treatment care instructions'
        ],
         bookingUrl: 'appointment.php?service=nasya'
      },
      'pain-management': {
        title: 'Comprehensive Pain Management',
        image: 'images/pain.png',
        price: '₹3,500',
        duration: '90 minutes',
        description: 'Our comprehensive pain management program combines multiple Ayurvedic therapies to address chronic pain conditions naturally and effectively.',
        benefits: [
          'Natural pain relief',
          'Improved mobility',
          'Reduced inflammation',
          'Better quality of life',
          'No side effects'
        ],
        process: [
          'Detailed pain assessment',
          'Customized treatment plan',
          'Multi-therapy session',
          'Progress monitoring',
          'Lifestyle recommendations'
        ],
         bookingUrl: 'appointment.php?service=pain-management'
      },
      consultation: {
        title: 'Ayurvedic Consultation',
        image: 'images/clinic.jpg',
        price: '₹1,200',
        duration: '45 minutes',
        description: 'Get personalized health guidance from our certified Ayurvedic practitioners. Comprehensive assessment and customized wellness plan included.',
        benefits: [
          'Personalized health assessment',
          'Constitution analysis (Prakriti)',
          'Customized treatment plan',
          'Lifestyle recommendations',
          'Follow-up support'
        ],
        process: [
          'Detailed health history',
          'Pulse diagnosis',
          'Constitution assessment',
          'Treatment planning',
          'Wellness guidance'
        ],
         bookingUrl: 'appointment.php?service=consultation'
      }
    };
    
    // Show service details in modal
    function showServiceDetails(serviceId) {
      const service = serviceDetails[serviceId];
      if (!service) return;
      
      const modalBody = document.getElementById('serviceModalBody');
      const modalTitle = document.getElementById('serviceModalLabel');
      const bookBtn = document.getElementById('modalBookBtn');
      
      modalTitle.textContent = service.title;
      bookBtn.href = service.bookingUrl;
      
      modalBody.innerHTML = `
        <div class="row">
          <div class="col-md-5">
            <img src="${service.image}" alt="${service.title}" class="img-fluid" style="border-radius: 15px; width: 100%; height: 250px; object-fit: cover;">
            <div class="mt-3 p-3" style="background: var(--background-light); border-radius: 12px;">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h4 style="color: var(--primary-color); margin: 0;">${service.price}</h4>
                  <small style="color: var(--text-light);">Per session</small>
                </div>
                <div class="text-end">
                  <div style="color: var(--text-dark); font-weight: 600;"><i class="fas fa-clock"></i> ${service.duration}</div>
                  <small style="color: var(--text-light);">Duration</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <p style="color: var(--text-light); line-height: 1.7; margin-bottom: 2rem;">${service.description}</p>
            
            <div class="mb-4">
              <h5 style="color: var(--primary-color); margin-bottom: 1rem;"><i class="fas fa-star"></i> Benefits</h5>
              <ul style="list-style: none; padding: 0;">
                ${service.benefits.map(benefit => `
                  <li style="padding: 0.3rem 0; color: var(--text-dark);">
                    <i class="fas fa-check" style="color: var(--primary-color); margin-right: 0.5rem;"></i>
                    ${benefit}
                  </li>
                `).join('')}
              </ul>
            </div>
            
            <div>
              <h5 style="color: var(--primary-color); margin-bottom: 1rem;"><i class="fas fa-list-ol"></i> Treatment Process</h5>
              <ol style="color: var(--text-dark); padding-left: 1.5rem;">
                ${service.process.map(step => `<li style="padding: 0.2rem 0;">${step}</li>`).join('')}
              </ol>
            </div>
          </div>
        </div>
      `;
    }
    
    // Enhanced animations
    document.addEventListener('DOMContentLoaded', function() {
      // Add fade-in animation keyframes
      const style = document.createElement('style');
      style.textContent = `
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
        
        .social-link:hover {
          transform: translateY(-3px) scale(1.05);
          box-shadow: var(--shadow-medium);
        }
        
        .footer-link:hover {
          color: var(--secondary-color) !important;
          padding-left: 0.5rem;
        }
      `;
      document.head.appendChild(style);
      
      // Smooth scrolling for anchor links
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
    });
  </script>
</body>
</html>
