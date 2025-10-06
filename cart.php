<?php
// Include authentication check
require_once 'auth_check.php';

// Require user to be logged in to access cart
requireLogin();

// Get current user info
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes">
<title>VEDAMRUT - Your Shopping Cart</title>

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

  /* Cart Page Header */
  .cart-header {
    background: var(--gradient-primary);
    padding: 120px 0 80px;
    position: relative;
    overflow: hidden;
    min-height: 35vh;
    display: flex;
    align-items: center;
  }

  .cart-header::before {
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

  .cart-header .container {
    position: relative;
    z-index: 2;
  }

  .cart-header h1 {
    color: white;
    font-size: 3rem;
    font-weight: 700;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    margin-bottom: 1rem;
    font-family: 'Cormorant Garamond', serif;
    letter-spacing: 2px;
  }

  .cart-header p {
    color: rgba(255,255,255,0.95);
    font-size: 1.2rem;
    margin-bottom: 0;
    font-weight: 400;
  }

  /* Main Cart Container */
  .cart-container {
    padding: 80px 0;
    min-height: calc(100vh - 300px);
  }

  .cart-content {
    background: var(--background-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-large);
    overflow: hidden;
    border: 1px solid rgba(44, 110, 73, 0.08);
  }

  /* Cart Item Cards */
  .cart-item {
    background: var(--background-white);
    border: 1px solid rgba(44, 110, 73, 0.08);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-soft);
    transition: var(--transition-smooth);
    position: relative;
  }

  .cart-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-medium);
    border-color: var(--primary-light);
  }

  .product-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 15px;
    border: 2px solid rgba(44, 110, 73, 0.1);
  }

  .product-details h5 {
    color: var(--text-dark);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
  }

  .product-price {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.1rem;
  }

  .product-total {
    color: var(--text-dark);
    font-weight: 600;
    font-size: 1.3rem;
  }

  /* Quantity Controls */
  .quantity-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--background-light);
    border-radius: 25px;
    padding: 0.3rem;
    border: 2px solid rgba(44, 110, 73, 0.1);
  }

  .quantity-btn {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: var(--transition-smooth);
    cursor: pointer;
  }

  .quantity-btn.minus {
    background: rgba(255, 107, 107, 0.1);
    color: var(--accent-color);
  }

  .quantity-btn.minus:hover {
    background: var(--accent-color);
    color: white;
    transform: scale(1.1);
  }

  .quantity-btn.plus {
    background: rgba(44, 110, 73, 0.1);
    color: var(--primary-color);
  }

  .quantity-btn.plus:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
  }

  .quantity-display {
    min-width: 40px;
    text-align: center;
    font-weight: 700;
    color: var(--text-dark);
    font-size: 1.1rem;
  }

  /* Remove Button */
  .remove-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 50%;
    background: rgba(255, 107, 107, 0.1);
    color: var(--accent-color);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition-smooth);
    cursor: pointer;
  }

  .remove-btn:hover {
    background: var(--accent-color);
    color: white;
    transform: scale(1.1);
  }

  /* Cart Summary */
  .cart-summary {
    background: var(--gradient-primary);
    color: white;
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-large);
    position: sticky;
    top: 120px;
  }

  .summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  }

  .summary-row:last-child {
    border-bottom: none;
    font-size: 1.3rem;
    font-weight: 700;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid rgba(255, 255, 255, 0.3);
  }

  .checkout-btn {
    width: 100%;
    padding: 1.2rem;
    background: var(--gradient-secondary);
    color: var(--text-dark);
    border: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--transition-smooth);
    margin-top: 1.5rem;
    box-shadow: var(--shadow-soft);
  }

  .checkout-btn:hover {
    background: #ffb347;
    transform: translateY(-3px);
    box-shadow: var(--shadow-medium);
  }

  .continue-shopping {
    width: 100%;
    padding: 0.8rem;
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 50px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--transition-smooth);
    margin-top: 1rem;
    text-decoration: none;
    display: inline-block;
    text-align: center;
  }

  .continue-shopping:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
    color: white;
    transform: translateY(-2px);
  }

  /* Empty Cart */
  .empty-cart {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--background-white);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-soft);
  }

  .empty-cart-icon {
    width: 120px;
    height: 120px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    color: white;
    font-size: 3rem;
  }

  .empty-cart h3 {
    color: var(--text-dark);
    margin-bottom: 1rem;
    font-weight: 600;
  }

  .empty-cart p {
    color: var(--text-light);
    margin-bottom: 2rem;
    font-size: 1.1rem;
  }

  .shop-now-btn {
    background: var(--gradient-primary);
    color: white;
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: var(--transition-smooth);
    text-decoration: none;
    display: inline-block;
    box-shadow: var(--shadow-soft);
  }

  .shop-now-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: var(--shadow-medium);
    color: white;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .header .navbar {
      display: none;
    }

    .cart-header h1 {
      font-size: 2.5rem;
    }

    .cart-item {
      padding: 1rem;
    }

    .product-image {
      width: 80px;
      height: 80px;
    }

    .cart-summary {
      position: static;
      margin-top: 2rem;
    }

    .empty-cart {
      padding: 2rem 1rem;
    }

    .empty-cart-icon {
      width: 80px;
      height: 80px;
      font-size: 2rem;
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

<!-- Cart Page Header -->
<section class="cart-header">
  <div class="container text-center">
    <h1 data-aos="fade-up">Your Shopping Cart</h1>
    <p data-aos="fade-up" data-aos-delay="150">Welcome, <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>. Review your selected items and proceed to checkout.</p>
  </div>
</section>

<!-- Cart Section -->
<section class="cart-container">
  <div class="container">
    <div class="row g-4">
      <!-- Items List -->
      <div class="col-lg-8">
        <div id="cartItems"></div>
      </div>
      <!-- Summary -->
      <div class="col-lg-4">
        <div class="cart-summary" data-aos="fade-left">
          <div class="summary-row"><span>Subtotal</span><span id="subtotal">₹0</span></div>
          <div class="summary-row"><span>Discount</span><span id="discount">₹0</span></div>
          <div class="summary-row"><span>Tax (5%)</span><span id="tax">₹0</span></div>
          <div class="summary-row"><span>Total</span><span id="total">₹0</span></div>
          <button class="checkout-btn" id="checkoutBtn"><i class="fas fa-lock me-2"></i>Secure Checkout</button>
          <a href="products.php" class="continue-shopping"><i class="fas fa-arrow-left me-2"></i>Continue Shopping</a>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const itemsContainer = document.getElementById('cartItems');
  const subtotalEl = document.getElementById('subtotal');
  const discountEl = document.getElementById('discount');
  const taxEl = document.getElementById('tax');
  const totalEl = document.getElementById('total');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const cartCountBadge = document.getElementById('cartCountBadge');

  function formatINR(value) {
    return '₹' + Number(value).toLocaleString('en-IN');
  }

  function getCart() {
    return JSON.parse(localStorage.getItem('vedalife_cart')) || [];
  }

  function setCart(cart) {
    localStorage.setItem('vedalife_cart', JSON.stringify(cart));
  }

  function renderEmpty() {
    itemsContainer.innerHTML = `
      <div class="empty-cart" data-aos="fade-up">
        <div class="empty-cart-icon"><i class="fas fa-shopping-bag"></i></div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything to your cart yet.</p>
        <a href="products.php" class="shop-now-btn"><i class="fas fa-store me-2"></i>Shop Now</a>
      </div>
    `;
    subtotalEl.textContent = formatINR(0);
    discountEl.textContent = formatINR(0);
    taxEl.textContent = formatINR(0);
    totalEl.textContent = formatINR(0);
    cartCountBadge.textContent = '0';
    checkoutBtn.disabled = true;
    checkoutBtn.style.opacity = '0.7';
    checkoutBtn.style.cursor = 'not-allowed';
  }

  function renderCart() {
    const cart = getCart();

    if (cart.length === 0) {
      renderEmpty();
      return;
    }

    let subtotal = 0;
    let count = 0;
    let html = '';

    cart.forEach((item, index) => {
      const itemTotal = item.price * item.quantity;
      subtotal += itemTotal;
      count += item.quantity;

      html += `
        <div class="cart-item d-flex align-items-start gap-3" data-aos="fade-up">
          <img src="${item.image}" alt="${item.name}" class="product-image" />
          <div class="product-details flex-grow-1">
            <h5>${item.name}</h5>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div>
                <div class="product-price">${formatINR(item.price)}</div>
                <div class="quantity-controls mt-2">
                  <button class="quantity-btn minus" data-index="${index}">-</button>
                  <span class="quantity-display">${item.quantity}</span>
                  <button class="quantity-btn plus" data-index="${index}">+</button>
                </div>
              </div>
              <div class="product-total">${formatINR(itemTotal)}</div>
            </div>
          </div>
          <button class="remove-btn" title="Remove" data-index="${index}"><i class="fas fa-times"></i></button>
        </div>
      `;
    });

    itemsContainer.innerHTML = html;

    // Pricing calculations
    const discount = subtotal > 2000 ? Math.round(subtotal * 0.05) : 0; // 5% discount over ₹2000
    const taxable = subtotal - discount;
    const tax = Math.round(taxable * 0.05);
    const total = taxable + tax;

    subtotalEl.textContent = formatINR(subtotal);
    discountEl.textContent = '-' + formatINR(discount);
    taxEl.textContent = formatINR(tax);
    totalEl.textContent = formatINR(total);
    cartCountBadge.textContent = String(count);

    // Bind controls
    document.querySelectorAll('.quantity-btn.plus').forEach(btn => {
      btn.addEventListener('click', () => updateQuantity(parseInt(btn.dataset.index), +1));
    });
    document.querySelectorAll('.quantity-btn.minus').forEach(btn => {
      btn.addEventListener('click', () => updateQuantity(parseInt(btn.dataset.index), -1));
    });
    document.querySelectorAll('.remove-btn').forEach(btn => {
      btn.addEventListener('click', () => removeItem(parseInt(btn.dataset.index)));
    });

    checkoutBtn.disabled = false;
    checkoutBtn.style.opacity = '1';
    checkoutBtn.style.cursor = 'pointer';
  }

  function updateQuantity(index, delta) {
    const cart = getCart();
    if (!cart[index]) return;
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) cart.splice(index, 1);
    setCart(cart);
    renderCart();
  }

  function removeItem(index) {
    const cart = getCart();
    cart.splice(index, 1);
    setCart(cart);
    renderCart();
  }

  // Navbar scroll effect
  window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 100) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
  });

  // Checkout click - redirect to checkout page
  checkoutBtn.addEventListener('click', function() {
    const cart = getCart();
    if (cart.length === 0) {
      alert('Your cart is empty. Please add some items before checkout.');
      return;
    }
    
    // Show brief loading message
    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting...';
    this.disabled = true;
    
    // Redirect to checkout page
    setTimeout(() => {
      window.location.href = 'checkout.php';
    }, 500);
  });

  // Initial render
  renderCart();
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 800, once: true, offset: 100 });
</script>

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
</body>
</html>
