<?php
// Include authentication check
require_once 'auth_check.php';

// Require user to be logged in to access checkout
requireLogin();

// Get current user info
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure Checkout - VEDAMRUT</title>
  
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
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      line-height: 1.6;
      color: var(--text-dark);
      background: var(--background-light);
      overflow-x: hidden;
      font-size: 13.6px; /* 16px * 0.85 */
    }

    /* Enhanced Navbar - Matching Other Pages */
    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.85rem 9%; /* 1rem * 0.85 */
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--transition-smooth);
    }

    .header.scrolled {
      padding: 0.68rem 9%; /* 0.8rem * 0.85 */
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .header .logo {
      font-size: 1.53rem; /* 1.8rem * 0.85 */
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
      font-size: 0.765rem; /* 0.9rem * 0.85 */
      margin: 0 1.275rem; /* 1.5rem * 0.85 */
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
      padding: 0.595rem 1.275rem; /* 0.7rem 1.5rem * 0.85 */
      font-size: 0.7225rem; /* 0.85rem * 0.85 */
      border-radius: 42.5px; /* 50px * 0.85 */
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: var(--transition-smooth);
      border: 2px solid transparent;
      text-decoration: none;
      position: relative;
    }

    .nav-buttons .btn:not(.btn-danger) {
      background: transparent;
      color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .nav-buttons .btn:not(.btn-danger):hover {
      background: var(--primary-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: var(--shadow-soft);
    }

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

    /* Checkout Header */
    .checkout-header {
      background: var(--gradient-primary);
      padding: 119px 0 68px; /* 140px 0 80px * 0.85 */
      position: relative;
      overflow: hidden;
      margin-bottom: 68px; /* 80px * 0.85 */
    }

    .checkout-header::before {
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

    .checkout-header .container {
      position: relative;
      z-index: 2;
    }

    .checkout-header h1 {
      color: white;
      font-size: 2.975rem; /* 3.5rem * 0.85 */
      font-weight: 700;
      text-shadow: 0 4px 20px rgba(0,0,0,0.3);
      margin-bottom: 1rem;
      font-family: 'Cormorant Garamond', serif;
      text-align: center;
    }

    .checkout-header p {
      color: rgba(255,255,255,0.9);
      font-size: 1.02rem; /* 1.2rem * 0.85 */
      text-align: center;
      max-width: 600px;
      margin: 0 auto;
    }

    /* Form Styling */
    .form-section {
      background: white;
      padding: 3rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-medium);
      margin-bottom: 2rem;
    }

    .form-section h3 {
      color: var(--primary-dark);
      font-weight: 600;
      margin-bottom: 2rem;
      font-size: 1.5rem;
    }

    .form-label {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 0.8rem 1rem;
      transition: var(--transition-smooth);
      font-size: 0.95rem;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(44, 110, 73, 0.25);
    }

    .payment-methods {
      border: 2px solid #e9ecef;
      border-radius: 15px;
      padding: 1.5rem;
      background: #f8fff9;
    }

    .payment-option {
      padding: 1rem;
      margin-bottom: 1rem;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      background: white;
      transition: var(--transition-smooth);
      cursor: pointer;
    }

    .payment-option:hover {
      border-color: var(--primary-color);
      box-shadow: var(--shadow-soft);
      transform: translateY(-2px);
    }

    .payment-option:has(.form-check-input:checked) {
      border-color: var(--primary-color);
      background: var(--background-light);
      box-shadow: var(--shadow-soft);
    }

    .payment-option .form-check-input:checked + .form-check-label {
      color: var(--primary-dark);
      font-weight: 600;
    }

    /* Order Summary */
    .order-summary {
      background: white;
      padding: 2rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-medium);
      border-left: 4px solid var(--primary-color);
    }

    .order-summary h5 {
      color: var(--primary-dark);
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .order-totals {
      background: var(--background-light);
      padding: 1.5rem;
      border-radius: 12px;
      margin-top: 1rem;
    }

    /* Enhanced Button */
    .btn-checkout {
      background: var(--gradient-primary);
      color: white;
      padding: 1rem 2rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 1.1rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: var(--transition-smooth);
      border: none;
      width: 100%;
      margin-top: 1rem;
    }

    .btn-checkout:hover {
      background: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: var(--shadow-large);
      color: white;
    }

    .btn-checkout:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Success Alert */
    .welcome-alert {
      background: var(--gradient-secondary);
      border: none;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      margin-bottom: 2rem;
      color: var(--text-dark);
      font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .header {
        padding: 1rem 5%;
      }
      
      .header .navbar {
        display: none;
      }
      
      .checkout-header h1 {
        font-size: 2.5rem;
      }
      
      .form-section {
        padding: 2rem 1.5rem;
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
      <a href="index.php#pricing">Packages</a>
      <a href="index.php#testimonials">Testimonials</a>
      <a href="index.php#contact">Contact</a>
    </nav>
    
    <div class="nav-buttons">
      <a href="profile.php" class="btn"><i class="fas fa-user"></i></a>
      <a href="cart.php" class="btn position-relative">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-badge" id="cartCountBadge">0</span>
      </a>
      <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </header>

  <!-- Checkout Header -->
  <section class="checkout-header">
    <div class="container">
      <h1 data-aos="fade-up">🛒 Secure Checkout</h1>
      <p data-aos="fade-up" data-aos-delay="150">Complete your order securely. Welcome, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>!</p>
    </div>
  </section>

  <!-- Checkout Content -->
  <div class="container">
    <div class="row g-5">

      <!-- Billing Information -->
      <div class="col-lg-7">
        <div class="form-section" data-aos="fade-up">
          <h3><i class="fas fa-user-edit text-primary me-2"></i>Billing Information</h3>
          <form id="checkoutForm">
            <div class="row">
              <div class="col-md-12 mb-4">
                <label class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                <input type="text" class="form-control" required id="fullName" value="<?php echo htmlspecialchars($currentUser['username']); ?>" placeholder="Enter your full name" />
              </div>
              <div class="col-md-12 mb-4">
                <label class="form-label"><i class="fas fa-phone me-2"></i>Phone Number</label>
                <input type="tel" class="form-control" required id="phone" placeholder="Enter 10-digit mobile number" />
                <div class="form-text">We'll use this number for delivery updates</div>
              </div>
              <div class="col-md-12 mb-4">
                <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</label>
                <textarea class="form-control" rows="4" required id="address" placeholder="House/Flat No., Street, Area, Landmark, City, State, Pincode"></textarea>
                <div class="form-text text-success">💡 Please provide a complete address with pincode for accurate delivery</div>
              </div>
              <div class="col-md-12 mb-4">
                <label class="form-label"><i class="fas fa-credit-card me-2"></i>Payment Method</label>
                <div class="payment-methods">
                  <div class="form-check payment-option">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="cod" value="cod" checked>
                    <label class="form-check-label w-100" for="cod">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-money-bills text-success me-3 fa-lg"></i>
                        <div>
                          <strong>Cash on Delivery (COD)</strong>
                          <small class="text-muted d-block">💰 Pay when your order is delivered to your doorstep</small>
                        </div>
                      </div>
                    </label>
                  </div>
                  <div class="form-check payment-option">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="upi" value="upi">
                    <label class="form-check-label w-100" for="upi">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-mobile-alt text-primary me-3 fa-lg"></i>
                        <div>
                          <strong>UPI Payment</strong>
                          <small class="text-muted d-block">📱 Pay instantly using PhonePe, GPay, Paytm, etc.</small>
                        </div>
                      </div>
                    </label>
                  </div>
                  <div class="form-check payment-option">
                    <input class="form-check-input" type="radio" name="paymentMethod" id="card" value="card">
                    <label class="form-check-label w-100" for="card">
                      <div class="d-flex align-items-center">
                        <i class="fas fa-credit-card text-info me-3 fa-lg"></i>
                        <div>
                          <strong>Card Payment</strong>
                          <small class="text-muted d-block">💳 Secure payment using Debit/Credit Card</small>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
              <div class="col-md-12 mb-4">
                <label class="form-label"><i class="fas fa-sticky-note me-2"></i>Order Notes <span class="text-muted">(Optional)</span></label>
                <textarea class="form-control" rows="3" id="notes" placeholder="Any special instructions for delivery, preferred time slot, etc."></textarea>
              </div>
            </div>
            <button type="submit" class="btn-checkout">
              <i class="fas fa-shield-alt me-2"></i>Place Order Securely
            </button>
          </form>
        </div>
      </div>

      <!-- Order Summary -->
      <div class="col-lg-5">
        <div class="order-summary" data-aos="fade-up" data-aos-delay="200">
          <h5><i class="fas fa-shopping-bag text-primary me-2"></i>Order Summary</h5>
          <div id="summaryList" class="mb-4"></div>
          <div class="order-totals">
            <div class="d-flex justify-content-between mb-3">
              <span><i class="fas fa-calculator me-2 text-muted"></i>Subtotal:</span>
              <span id="subtotalAmount" class="fw-semibold">₹0</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span><i class="fas fa-truck me-2 text-muted"></i>Delivery:</span>
              <span id="deliveryCharges" class="fw-semibold text-success">FREE</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span><i class="fas fa-receipt me-2 text-muted"></i>GST (5%):</span>
              <span id="gstAmount" class="fw-semibold">₹0</span>
            </div>
            <hr style="border-color: var(--primary-color); margin: 1.5rem 0;">
            <div class="d-flex justify-content-between fs-4 fw-bold text-primary">
              <span><i class="fas fa-rupee-sign me-2"></i>Total Amount:</span>
              <span id="totalSummary">₹0</span>
            </div>
            
            <!-- Security & Trust Badges -->
            <div class="mt-4 pt-3 border-top">
              <div class="row text-center g-2">
                <div class="col-4">
                  <i class="fas fa-shield-alt text-success fa-lg mb-1"></i>
                  <small class="d-block text-muted">Secure</small>
                </div>
                <div class="col-4">
                  <i class="fas fa-truck text-primary fa-lg mb-1"></i>
                  <small class="d-block text-muted">Fast Delivery</small>
                </div>
                <div class="col-4">
                  <i class="fas fa-undo text-warning fa-lg mb-1"></i>
                  <small class="d-block text-muted">Easy Returns</small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Continue Shopping -->
        <div class="mt-4">
          <a href="products.php" class="btn btn-outline-success w-100">
            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
          </a>
        </div>
      </div>
    </div>
  </div>

<!-- Success Modal -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-labelledby="orderSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="orderSuccessModalLabel">🎉 Order Placed!</h5>
      </div>
      <div class="modal-body">
        <p>Thank you for shopping with <strong>VEDALIFE</strong>!</p>
        <a href="products.php" class="btn btn-success">Continue Shopping</a>
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
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link"><i class="fab fa-facebook-f"></i></a>
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link"><i class="fab fa-instagram"></i></a>
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: var(--transition-smooth);" class="social-link"><i class="fab fa-youtube"></i></a>
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
        <h5 style="color: var(--secondary-color); margin-bottom: 1.5rem; font-weight: 600;">Our Products</h5>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 0.8rem;"><a href="products.php?category=oils" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Herbal Oils</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="products.php?category=supplements" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Supplements</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="products.php?category=powders" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Ayurvedic Powders</a></li>
          <li style="margin-bottom: 0.8rem;"><a href="products.php?category=teas" style="color: rgba(255, 255, 255, 0.8); text-decoration: none; transition: var(--transition-smooth);" class="footer-link">Herbal Teas</a></li>
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
        <p style="margin: 0; color: rgba(255, 255, 255, 0.6); font-size: 0.9rem;">&copy; 2025 VEDAMRUT. All rights reserved. | Privacy Policy | Terms of Service</p>
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
<script>
  // Initialize AOS
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100
  });

  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  // Social link hover effects
  document.querySelectorAll('.social-link').forEach(link => {
    link.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-3px) scale(1.05)';
      this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
    });
    link.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
      this.style.boxShadow = 'none';
    });
  });

  // Footer links hover effects
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

  document.addEventListener("DOMContentLoaded", function () {
    // Initialize cart counter on page load
    updateCartCounter();
    
    const cart = JSON.parse(localStorage.getItem("vedalife_cart")) || [];
    const summaryList = document.getElementById("summaryList");
    const subtotalEl = document.getElementById("subtotalAmount");
    const deliveryEl = document.getElementById("deliveryCharges");
    const gstEl = document.getElementById("gstAmount");
    const totalSummary = document.getElementById("totalSummary");

    let subtotal = 0;

    if (cart.length === 0) {
      summaryList.innerHTML = `<li class="text-center text-muted py-3">
        <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
        Your cart is empty
      </li>`;
      subtotalEl.textContent = "₹0";
      gstEl.textContent = "₹0";
      totalSummary.textContent = "₹0";
    } else {
      cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        const li = document.createElement("li");
        li.className = "d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded";
        li.innerHTML = `
          <div>
            <strong>${item.name}</strong>
            <small class="text-muted d-block">₹${item.price} × ${item.quantity}</small>
          </div>
          <span class="fw-bold">₹${itemTotal}</span>
        `;
        summaryList.appendChild(li);
      });

      // Calculate charges
      const deliveryCharges = subtotal >= 500 ? 0 : 50; // Free delivery above ₹500
      const gst = Math.round(subtotal * 0.05); // 5% GST
      const total = subtotal + deliveryCharges + gst;

      // Update display
      subtotalEl.textContent = `₹${subtotal}`;
      deliveryEl.innerHTML = deliveryCharges === 0 ? 
        `<span class="text-success">FREE</span>` : 
        `₹${deliveryCharges}`;
      gstEl.textContent = `₹${gst}`;
      totalSummary.textContent = `₹${total}`;
      
      // Store total for form submission
      window.orderTotal = total;
    }

    // Handle form submission
    document.getElementById("checkoutForm").addEventListener("submit", async function (e) {
      e.preventDefault();

      const name = document.getElementById("fullName").value.trim();
      const phone = document.getElementById("phone").value.trim();
      const address = document.getElementById("address").value.trim();
      const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
      const notes = document.getElementById("notes").value.trim();

      // Enhanced validation
      if (!name || name.length < 2) {
        alert("Please enter a valid name (at least 2 characters).");
        document.getElementById("fullName").focus();
        return;
      }

      if (!phone || !/^[6-9]\d{9}$/.test(phone)) {
        alert("Please enter a valid 10-digit Indian mobile number.");
        document.getElementById("phone").focus();
        return;
      }

      if (!address || address.length < 10) {
        alert("Please enter a complete delivery address (at least 10 characters).");
        document.getElementById("address").focus();
        return;
      }

      if (!paymentMethod) {
        alert("Please select a payment method.");
        return;
      }

      if (cart.length === 0) {
        alert("Your cart is empty. Please add items before checkout.");
        return;
      }

      // Disable submit button to prevent double submission
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

      try {
        // Show processing alert for user feedback
        console.log('🚀 Starting order processing...');
        
        // Prepare order data
        const orderData = {
          fullName: name,
          phone: phone,
          address: address,
          paymentMethod: paymentMethod,
          notes: notes,
          cart: cart,
          subtotal: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0),
          total: window.orderTotal || cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) + (cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) >= 500 ? 0 : 50) + Math.round(cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 0.05)
        };
        
        console.log('📋 Order data:', orderData);

        // Send order to backend
        console.log('📡 Sending request to process_order.php...');
        const response = await fetch('process_order.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(orderData)
        });
        
        console.log('📨 Response received:', response.status, response.statusText);

        // Check if response is ok
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('✅ Parsed response:', result);

        if (result.success) {
          console.log('🎉 Order placed successfully!');
          
          // Show success alert
          alert('🎉 Order placed successfully! Order #' + result.order_number);
          // Clear cart on successful order
          localStorage.removeItem("vedalife_cart");
          
          // Update cart counter after clearing cart
          updateCartCounter();
          
          // Update modal with order details
          document.querySelector('#orderSuccessModal .modal-body').innerHTML = `
            <div class="text-center">
              <i class="fa-solid fa-check-circle text-success fa-3x mb-3"></i>
              <h5>Order Placed Successfully!</h5>
              <p>Order Number: <strong>${result.order_number}</strong></p>
              <p>Thank you for shopping with <strong>VEDALIFE</strong>!</p>
              <div class="mt-3">
                <a href="my_orders.php" class="btn btn-success me-2">View My Orders</a>
                <a href="products.php" class="btn btn-outline-success">Continue Shopping</a>
              </div>
            </div>
          `;
          
          const modal = new bootstrap.Modal(document.getElementById("orderSuccessModal"));
          modal.show();
        } else {
          console.log('❌ Order failed:', result.message);
          alert('❌ Order failed: ' + (result.message || 'Unknown error. Please try again.'));
          throw new Error(result.message || 'Failed to place order');
        }
      } catch (error) {
        console.error('🚑 Order placement failed:', error);
        alert('❌ Failed to place order: ' + error.message + '. Please try again.');
      } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  });
</script>

<!-- Enhanced scripts integrated above for seamless experience -->

</body>
</html>
