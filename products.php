<?php
session_start();

// Check if user is logged in for cart functionality
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Fetch active products from database
$products = [];
$mysqli = @new mysqli('localhost', 'root', '', 'vedalife');
if (!$mysqli->connect_error) {
  $result = $mysqli->query("SELECT id, name, description, price, image, category, stock, created_at FROM products WHERE is_active = 1 ORDER BY name ASC");
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $products[] = $row;
    }
    $result->free();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=3.0, user-scalable=yes">
  <title>VEDAMRUT - Premium Ayurvedic Products</title>

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
      line-height: 1.5;
      color: var(--text-dark);
      background: var(--background-light);
      overflow-x: hidden;
      font-size: 0.9rem;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
    
    /* Enhanced Header */
    .product-header {
      position: relative;
      background: var(--gradient-primary);
      padding: 120px 0 80px;
      margin-bottom: 80px;
      overflow: hidden;
      min-height: 60vh;
      display: flex;
      align-items: center;
    }

    .product-header::before {
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

    .product-header::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, transparent 30%, rgba(255, 193, 69, 0.1) 70%);
      z-index: 1;
    }

    .product-header .container {
      position: relative;
      z-index: 2;
    }

    .product-header h1 {
      color: white;
      font-size: clamp(3.5rem, 5vw, 4.5rem);
      font-weight: 700;
      text-shadow: 0 4px 20px rgba(0,0,0,0.3);
      margin-bottom: 1.25rem;
      font-family: 'Cormorant Garamond', serif;
      letter-spacing: 2px;
      position: relative;
    }
    
    .product-header p {
      color: rgba(255,255,255,0.95);
      font-size: 1.25rem;
      max-width: 600px;
      margin: 0 auto 2rem;
      font-weight: 400;
      line-height: 1.8;
    }

    .header-stats {
      display: flex;
      justify-content: center;
      gap: 3rem;
      margin-top: 3rem;
    }

    .stat-item {
      text-align: center;
      color: white;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      display: block;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      opacity: 0.9;
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
      padding: 0.6rem 7%;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--transition-smooth);
    }

    .header.scrolled {
      padding: 0.5rem 7%;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
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
      font-size: 0.75rem;
      margin: 0 1.2rem;
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
    /* Enhanced Product Cards */
    .product-card {
      background: var(--background-white);
      border-radius: var(--border-radius);
      overflow: hidden;
      transition: var(--transition-smooth);
      border: 1px solid rgba(44, 110, 73, 0.08);
      box-shadow: var(--shadow-soft);
      position: relative;
    }
    
    .product-card:hover {
      transform: translateY(-15px);
      box-shadow: var(--shadow-large);
      border-color: var(--primary-color);
    }

    .product-image-wrapper {
      height: 280px;
      overflow: hidden;
      position: relative;
      background: linear-gradient(45deg, #f8fffe 0%, #ffffff 100%);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .product-image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: var(--transition-smooth);
      padding: 1rem;
    }
    
    .product-card:hover .product-image-wrapper img {
      transform: scale(1.08);
    }

    .product-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      background: var(--gradient-secondary);
      color: var(--text-dark);
      padding: 0.4rem 1rem;
      border-radius: 25px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      z-index: 10;
      box-shadow: var(--shadow-medium);
      border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .product-badge.bestseller {
      background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);
      color: white;
    }

    .product-badge.new {
      background: linear-gradient(135deg, #4c956c 0%, #2c6e49 100%);
      color: white;
    }

    .product-content {
      padding: 1.8rem;
      background: var(--background-white);
    }
    
    .product-title {
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
      font-size: 1.1rem;
      line-height: 1.3;
      transition: var(--transition-smooth);
      font-family: 'Cormorant Garamond', serif;
    }

    .product-card:hover .product-title {
      color: var(--primary-color);
    }
    
    .product-description {
      color: var(--text-light);
      margin-bottom: 0.75rem;
      font-size: 0.8rem;
      line-height: 1.4;
      display: -webkit-box;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(44, 110, 73, 0.1);
    }
    
    .product-price {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--primary-color);
      font-family: 'Poppins', sans-serif;
    }

    .price-original {
      font-size: 1.1rem;
      color: var(--text-light);
      text-decoration: line-through;
      margin-left: 0.5rem;
      font-weight: 400;
    }
    
    .product-rating {
      display: flex;
      align-items: center;
      gap: 0.3rem;
    }
    
    .product-rating i {
      color: #ffc145;
      font-size: 0.9rem;
    }
    
.product-rating span {
      color: var(--text-light);
      font-size: 0.85rem;
      font-weight: 600;
      margin-left: 0.5rem;
    }

    /* Stock availability indicator */
    .stock-indicator {
      font-size: 0.75rem;
      padding: 0.2rem 0.6rem;
      border-radius: 12px;
      font-weight: 600;
      display: inline-block;
      margin-top: 0.3rem;
    }
    .stock-good { background: #e6f4ea; color: #1e7e34; border: 1px solid #cdebd6; }
    .stock-low { background: #fff3cd; color: #856404; border: 1px solid #ffe8a1; }
    .stock-out { background: #fdecea; color: #842029; border: 1px solid #f5c2c7; }

    .product-actions {
      display: flex;
      gap: 0.8rem;
    }
    
    .product-actions .btn {
      padding: 0.4rem 0.8rem;
      font-size: 0.75rem;
      border-radius: 50px;
      font-weight: 500;
      transition: var(--transition-smooth);
    }
    
    .btn-add-cart {
      flex: 1;
      background: var(--gradient-primary);
      color: white;
      border: none;
      padding: 0.9rem 1.5rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: var(--transition-smooth);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: var(--shadow-soft);
    }
    
    .btn-add-cart:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-large);
      background: var(--primary-dark);
      color: white;
    }
    
    .btn-wishlist {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--background-light);
      border: 2px solid rgba(44, 110, 73, 0.1);
      border-radius: 50%;
      transition: var(--transition-smooth);
      color: var(--text-light);
    }
    
    .btn-wishlist:hover {
      background: rgba(255, 107, 107, 0.1);
      color: var(--accent-color);
      border-color: var(--accent-color);
      transform: scale(1.1);
    }

    /* Enhanced Search and Filter Section */
    .filter-section {
      background: var(--background-white);
      border-radius: var(--border-radius);
      padding: 2rem;
      margin-bottom: 3rem;
      box-shadow: var(--shadow-soft);
      border: 1px solid rgba(44, 110, 73, 0.08);
    }

    .search-box {
      position: relative;
      max-width: 400px;
      margin: 0 auto 2rem;
    }

    .search-input {
      width: 100%;
      padding: 1rem 3rem 1rem 1.5rem;
      border: 2px solid rgba(44, 110, 73, 0.1);
      border-radius: 50px;
      font-size: 1rem;
      transition: var(--transition-smooth);
      background: var(--background-light);
    }

    .search-input:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(44, 110, 73, 0.1);
    }

    .search-icon {
      position: absolute;
      right: 1.5rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--primary-color);
    }
    
    .category-filter {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      justify-content: center;
      margin-bottom: 1rem;
    }
    
    .category-btn {
      padding: 0.8rem 1.5rem;
      border-radius: 50px;
      background: var(--background-light);
      border: 2px solid rgba(44, 110, 73, 0.1);
      color: var(--text-light);
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: var(--transition-smooth);
      cursor: pointer;
    }
    
    .category-btn:hover, .category-btn.active {
      background: var(--gradient-primary);
      color: white;
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: var(--shadow-soft);
    }

    .sort-options {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 1.5rem;
    }

    .sort-select {
      padding: 0.7rem 1.2rem;
      border: 2px solid rgba(44, 110, 73, 0.1);
      border-radius: 25px;
      background: var(--background-light);
      font-weight: 500;
      transition: var(--transition-smooth);
    }

    .sort-select:focus {
      outline: none;
      border-color: var(--primary-color);
    }
    
    /* Enhanced Footer - Matching Services and Home */
    .footer {
      background: linear-gradient(135deg, var(--text-dark) 0%, #1a1a2e 100%);
      color: white;
      padding: 4rem 0 2rem;
      margin-top: 5rem;
    }

    .footer h3 {
      color: var(--secondary-color);
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1rem;
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
    }

    .social-link:hover {
      transform: translateY(-3px) scale(1.05);
      color: white;
    }

    .footer-link:hover {
      color: var(--secondary-color) !important;
      transform: translateX(5px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .product-header h1 {
        font-size: 2.5rem;
      }
      
      .header-stats {
        flex-direction: column;
        gap: 1.5rem;
      }
      
      .category-filter {
        gap: 0.5rem;
      }
      
      .category-btn {
        padding: 0.6rem 1rem;
        font-size: 0.8rem;
      }
      
      .product-card {
        margin-bottom: 2rem;
      }
      
      .footer-links {
        flex-direction: column;
        gap: 1rem;
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
    <a href="products.php" class="active">Products</a>
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

<!-- Enhanced Product Header -->
<div class="product-header">
  <div class="container text-center">
    <h1 data-aos="fade-up">Premium Ayurvedic Products</h1>
    <p data-aos="fade-up" data-aos-delay="200">Discover our authentic collection of traditional herbs and modern wellness solutions, crafted with centuries-old wisdom</p>
    
    <div class="header-stats" data-aos="fade-up" data-aos-delay="400">
      <div class="stat-item">
        <span class="stat-number">500+</span>
        <span class="stat-label">Products</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">50K+</span>
        <span class="stat-label">Happy Customers</span>
      </div>
      <div class="stat-item">
        <span class="stat-number">100%</span>
        <span class="stat-label">Natural</span>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">
  <!-- Enhanced Search and Filter Section -->
  <div class="filter-section" data-aos="fade-up">
    <div class="search-box">
      <input type="text" class="search-input" placeholder="Search products..." id="searchInput">
      <i class="fas fa-search search-icon"></i>
    </div>
    
    <div class="category-filter">
      <button class="category-btn active" data-category="all">All Products</button>
      <button class="category-btn" data-category="oils">Herbal Oils</button>
      <button class="category-btn" data-category="haircare">Haircare</button>
      <button class="category-btn" data-category="skincare">Skincare</button>
      <button class="category-btn" data-category="supplements">Supplements</button>
      <button class="category-btn" data-category="food">Food</button>
    </div>
    
    <div class="sort-options">
      <select class="sort-select" id="sortProducts">
        <option value="name">Sort by Name</option>
        <option value="price-low">Price: Low to High</option>
        <option value="price-high">Price: High to Low</option>
        <option value="rating">Highest Rated</option>
        <option value="popular">Most Popular</option>
      </select>
    </div>
  </div>

  <div class="row g-4" id="productsGrid">
<?php if (!empty($products)): ?>
      <?php $delay = 100; foreach ($products as $p): $delay += 50; ?>
        <?php
          $name = htmlspecialchars($p['name']);
          $desc = htmlspecialchars($p['description']);
          $price = number_format((float)$p['price'], 0);
          $category = htmlspecialchars(strtolower((string)($p['category'] ?? '')));
          $img = trim((string)$p['image']);
          if ($img !== '' && !preg_match('~^https?://~i', $img)) {
            $img = ltrim(str_replace('\\', '/', $img), '/');
          }
          $createdAt = isset($p['created_at']) ? strtotime($p['created_at']) : false;
          $isNew = $createdAt ? ($createdAt >= (time() - 30*24*60*60)) : false;
          $stock = (int)($p['stock'] ?? 0);
          if ($stock <= 0) { $stockClass = 'stock-out'; $stockText = 'Out of Stock'; }
          elseif ($stock < 10) { $stockClass = 'stock-low'; $stockText = 'Low Stock'; }
          else { $stockClass = 'stock-good'; $stockText = 'In Stock'; }
        ?>
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>" data-category="<?= $category !== '' ? $category : 'others' ?>">
          <div class="product-card">
<div class="product-image-wrapper">
              <?php if ($isNew): ?>
                <span class="product-badge new">New</span>
              <?php endif; ?>
              <img src="<?= $img !== '' ? htmlspecialchars($img) : 'images/placeholder-product.png' ?>" alt="<?= $name ?>" onerror="this.src='images/placeholder-product.png'">
            </div>
            <div class="product-content">
              <h5 class="product-title"><?= $name ?></h5>
              <?php if (!empty($desc)): ?>
              <p class="product-description"><?= $desc ?></p>
              <?php endif; ?>
<div class="product-meta">
                <div class="product-price">₹<?= $price ?></div>
                <div class="product-right" style="display:flex;flex-direction:column;align-items:flex-end;gap:0.25rem;">
                  <div class="product-rating">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="far fa-star"></i>
                  <span>(4.0)</span>
                  </div>
                  <div class="stock-indicator <?= $stockClass ?>"><?= $stockText ?><?= $stock > 0 ? ' • ' . (int)$stock . ' pcs' : '' ?></div>
                </div>
              </div>
              <div class="product-actions">
                <?php if($isLoggedIn): ?>
                  <button class="btn-add-cart add-to-cart" data-product="product-<?= (int)$p['id'] ?>"><i class="fas fa-shopping-cart me-2"></i>Add to Cart</button>
                  <button class="btn-wishlist" data-product="product-<?= (int)$p['id'] ?>"><i class="far fa-heart"></i></button>
                <?php else: ?>
                  <a href="SignUp_LogIn_Form.html" class="btn-add-cart"><i class="fas fa-lock me-2"></i>Login to Purchase</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="text-center py-5">
          <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
          <h5 class="text-muted">No products available</h5>
          <p class="text-muted">Please check back later.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="text-center mt-5" data-aos="fade-up">
    <a href="index.php" class="btn" style="background: var(--gradient-primary); color: white; padding: 1rem 2rem; border-radius: 50px; text-decoration: none; font-weight: 600; box-shadow: var(--shadow-soft); transition: var(--transition-smooth);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-large)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-soft)'"><i class="fas fa-home me-2"></i>Back to Home</a>
  </div>
</div>

<!-- Enhanced Footer - Matching Services and Home -->
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="mb-4">
          <h3>🌿VEDAMRUT</h3>
          <p class="mb-4">Experience authentic Ayurvedic healing with our premium treatments and natural products. Your journey to holistic wellness begins here.</p>
          
          <div class="d-flex gap-3">
            <a href="#" class="social-link">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-link">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-link">
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

<script>
  // Initialize AOS
  AOS.init({
    duration: 800,
    easing: 'ease-in-out',
    once: true,
    offset: 100
  });

  // Enhanced navbar scroll effect
  window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    const productCards = document.querySelectorAll('#productsGrid [data-category]');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortProducts');
    const cartCountBadge = document.getElementById('cartCountBadge');

    // Helpers
    const slugify = (s) => (s || '').toString().toLowerCase().replace(/[^a-z0-9]+/g, '').trim();
    const normalizeCategorySlug = (raw) => {
      const s = slugify(raw);
      if (s.includes('oil')) return 'oils';
      if (s.includes('hair')) return 'haircare';
      if (s.includes('skin')) return 'skincare';
      if (s.includes('supplement')) return 'supplements';
      if (s.includes('food') || s.includes('edible')) return 'food';
      if (s === '') return 'others';
      return s; // fallback to compacted form
    };

    // Track current filters
    let selectedCategory = 'all';
    let searchTerm = '';

    // Initialize from URL (?category=...)
    const params = new URLSearchParams(window.location.search);
    const urlCat = params.get('category');
    if (urlCat) {
      selectedCategory = normalizeCategorySlug(urlCat);
      categoryButtons.forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-category') === selectedCategory);
      });
    }

    // Initialize cart counter on page load
    updateCartCounter();

    // Category filtering (keep buttons visible; only update product grid)
    categoryButtons.forEach(button => {
      button.addEventListener('click', function() {
        categoryButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        selectedCategory = this.getAttribute('data-category');
        applyFilters();
      });
    });

    // Debounced search input
    let searchTimer = null;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        searchTerm = (searchInput.value || '').toLowerCase();
        applyFilters();
      }, 150);
    });

    // Sort functionality (preserve current filter)
    sortSelect.addEventListener('change', function() {
      sortProducts(this.value);
    });

    function applyFilters() {
      productCards.forEach(card => {
        const cardRawCat = card.getAttribute('data-category') || '';
        const cardCatSlug = normalizeCategorySlug(cardRawCat);
        const title = (card.querySelector('.product-title')?.textContent || '').toLowerCase();
        const description = (card.querySelector('.product-description')?.textContent || '').toLowerCase();

        const matchesCategory = (selectedCategory === 'all') || (cardCatSlug === selectedCategory);
        const matchesSearch = (searchTerm === '') || title.includes(searchTerm) || description.includes(searchTerm) || cardRawCat.toLowerCase().includes(searchTerm);

        if (matchesCategory && matchesSearch) {
          card.style.display = 'block';
          card.classList.add('aos-animate');
        } else {
          card.style.display = 'none';
        }
      });
    }

    function sortProducts(sortType) {
      const productsGrid = document.getElementById('productsGrid');
      const items = Array.from(productCards);

      items.sort((a, b) => {
        switch(sortType) {
          case 'name':
            return a.querySelector('.product-title').textContent.localeCompare(b.querySelector('.product-title').textContent);
          case 'price-low':
            return parseInt(a.querySelector('.product-price').textContent.replace(/[^0-9]/g, '')) - 
                   parseInt(b.querySelector('.product-price').textContent.replace(/[^0-9]/g, ''));
          case 'price-high':
            return parseInt(b.querySelector('.product-price').textContent.replace(/[^0-9]/g, '')) - 
                   parseInt(a.querySelector('.product-price').textContent.replace(/[^0-9]/g, ''));
          case 'rating':
            return parseFloat(b.querySelector('.product-rating span').textContent.replace(/[()]/g, '')) - 
                   parseFloat(a.querySelector('.product-rating span').textContent.replace(/[()]/g, ''));
          default:
            return 0;
        }
      });

      items.forEach(item => productsGrid.appendChild(item));
      // Re-apply filters to maintain visibility after reordering
      applyFilters();
    }

    // Initial apply (for URL category or initial state)
    applyFilters();

    // Cart management functions
    function getCart() {
      return JSON.parse(localStorage.getItem('vedalife_cart')) || [];
    }

    function setCart(cart) {
      localStorage.setItem('vedalife_cart', JSON.stringify(cart));
    }

    function extractProductData(productCard) {
      const title = productCard.querySelector('.product-title').textContent.trim();
      const priceElement = productCard.querySelector('.product-price');
      
      // Extract only the first price (current price), not the original price in span
      const priceText = priceElement.childNodes[0].textContent.trim();
      const price = parseInt(priceText.replace(/[^0-9]/g, ''));
      
      const image = productCard.querySelector('.product-image-wrapper img').src;
      const productId = productCard.querySelector('.add-to-cart').getAttribute('data-product');
      
      return {
        id: productId,
        name: title,
        price: price,
        image: image,
        quantity: 1
      };
    }

    function addToCart(productData) {
      const cart = getCart();
      const existingItemIndex = cart.findIndex(item => item.id === productData.id);
      
      if (existingItemIndex >= 0) {
        cart[existingItemIndex].quantity += 1;
      } else {
        cart.push(productData);
      }
      
      setCart(cart);
      return cart;
    }

    function getCartItemCount() {
      const cart = getCart();
      return cart.reduce((total, item) => total + item.quantity, 0);
    }

    function updateCartCounter() {
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

    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
      button.addEventListener('click', function() {
        const productCard = this.closest('.product-card');
        const productData = extractProductData(productCard);
        
        // Add to cart
        const updatedCart = addToCart(productData);
        const itemCount = getCartItemCount();
        
        // Add animation
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
          this.style.transform = 'scale(1)';
          this.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
          this.style.background = 'var(--gradient-secondary)';
          this.style.color = 'var(--text-dark)';
        }, 100);
        
        // Reset button after 2 seconds
        setTimeout(() => {
          this.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
          this.style.background = 'var(--gradient-primary)';
          this.style.color = 'white';
        }, 2000);
        
        // Update cart counter badge
        updateCartCounter();
        
        // Show success feedback
        console.log(`Added ${productData.name} to cart. Total items: ${itemCount}`);
        
        // Optional: Show a toast notification
        showCartNotification(productData.name, itemCount);
      });
    });

    // Cart notification function
    function showCartNotification(productName, itemCount) {
      // Create notification element if it doesn't exist
      let notification = document.getElementById('cart-notification');
      if (!notification) {
        notification = document.createElement('div');
        notification.id = 'cart-notification';
        notification.style.cssText = `
          position: fixed;
          top: 100px;
          right: 20px;
          background: var(--gradient-primary);
          color: white;
          padding: 1rem 1.5rem;
          border-radius: var(--border-radius);
          box-shadow: var(--shadow-medium);
          z-index: 10000;
          opacity: 0;
          transform: translateX(100%);
          transition: all 0.4s ease;
          max-width: 300px;
        `;
        document.body.appendChild(notification);
      }
      
      notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <i class="fas fa-check-circle"></i>
          <div>
            <strong>${productName}</strong> added to cart<br>
            <small>Total items: ${itemCount}</small>
          </div>
        </div>
      `;
      
      // Show notification
      setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
      }, 100);
      
      // Hide notification after 3 seconds
      setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
      }, 3000);
    }

    // Wishlist functionality
    document.querySelectorAll('.btn-wishlist').forEach(button => {
      button.addEventListener('click', function() {
        const icon = this.querySelector('i');
        
        if (icon.classList.contains('far')) {
          icon.classList.remove('far');
          icon.classList.add('fas');
          this.style.color = 'var(--accent-color)';
          this.style.background = 'rgba(255, 107, 107, 0.1)';
        } else {
          icon.classList.remove('fas');
          icon.classList.add('far');
          this.style.color = 'var(--text-light)';
          this.style.background = 'var(--background-light)';
        }
      });
    });

    // Smooth scroll for internal links
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

