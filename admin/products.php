<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();

$message = '';
$message_type = '';

// Handle product addition
if ($_POST['action'] ?? '' === 'add_product') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $image = sanitizeInput($_POST['image'] ?? '');
    
    if (empty($name) || $price <= 0) {
        $message = "Please fill in all required fields with valid data.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsls", $name, $description, $price, $category, $stock, $image);
        
        if ($stmt->execute()) {
            $message = "Product added successfully!";
            $message_type = "success";
            logAdminActivity('product_add', "Added product: $name");
        } else {
            $message = "Error adding product.";
            $message_type = "danger";
        }
    }
}

// Handle product update
if ($_POST['action'] ?? '' === 'update_product') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $image = sanitizeInput($_POST['image'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || $price <= 0) {
        $message = "Please fill in all required fields with valid data.";
        $message_type = "danger";
    } else {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock = ?, image = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssdsisii", $name, $description, $price, $category, $stock, $image, $is_active, $product_id);
        
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
            $message_type = "success";
            logAdminActivity('product_update', "Updated product ID: $product_id");
        } else {
            $message = "Error updating product.";
            $message_type = "danger";
        }
    }
}

// Handle product deletion
if ($_POST['action'] ?? '' === 'delete_product' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    // Get product info for logging
    $productInfo = $conn->query("SELECT name FROM products WHERE id = $product_id")->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
        $message_type = "success";
        logAdminActivity('product_delete', "Deleted product: " . $productInfo['name']);
    } else {
        $message = "Error deleting product. It may be referenced in orders.";
        $message_type = "danger";
    }
}

// Handle stock update
if ($_POST['action'] ?? '' === 'update_stock') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    
    $stmt = $conn->prepare("UPDATE products SET stock = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $stock, $product_id);
    
    if ($stmt->execute()) {
        $message = "Stock updated successfully!";
        $message_type = "success";
        logAdminActivity('stock_update', "Updated stock for product ID: $product_id to $stock");
    } else {
        $message = "Error updating stock.";
        $message_type = "danger";
    }
}

// Get products with pagination and filters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';
$stock_filter = $_GET['stock'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $whereClause .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= "sss";
}

if (!empty($category_filter)) {
    $whereClause .= " AND category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($status_filter === 'active') {
    $whereClause .= " AND is_active = 1";
} elseif ($status_filter === 'inactive') {
    $whereClause .= " AND is_active = 0";
}

if ($stock_filter === 'low') {
    $whereClause .= " AND stock < 10";
} elseif ($stock_filter === 'out') {
    $whereClause .= " AND stock = 0";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM products $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

// Get products
$productQuery = "SELECT * FROM products $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$productStmt = $conn->prepare($productQuery);
if (!empty($params)) {
    $productStmt->bind_param($types, ...$params);
}
$productStmt->execute();
$products = $productStmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
$categoriesList = [];
while ($category = $categories->fetch_assoc()) {
    $categoriesList[] = $category['category'];
}

// Get product statistics
$statsQuery = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
        SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
        AVG(price) as avg_price,
        SUM(stock) as total_stock
    FROM products
");
$stats = $statsQuery->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - VEDAMRUT Admin</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin Shared Styles -->
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header" data-aos="fade-down">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Product Management</h3>
                    <p class="mb-0 text-muted">Manage your product catalog and inventory</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-gradient-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="stats-card-enhanced" data-aos="fade-up" data-aos-delay="100">
                    <div class="stats-number text-primary"><i class="fas fa-box me-2"></i><?php echo $stats['total']; ?></div>
                    <div class="stats-label">Total Products</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card-enhanced" data-aos="fade-up" data-aos-delay="200">
                    <div class="stats-number text-success"><i class="fas fa-check-circle me-2"></i><?php echo $stats['active']; ?></div>
                    <div class="stats-label">Active</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card-enhanced" data-aos="fade-up" data-aos-delay="300">
                    <div class="stats-number text-warning"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $stats['low_stock']; ?></div>
                    <div class="stats-label">Low Stock</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="stats-card-enhanced" data-aos="fade-up" data-aos-delay="400">
                    <div class="stats-number text-danger"><i class="fas fa-times-circle me-2"></i><?php echo $stats['out_of_stock']; ?></div>
                    <div class="stats-label">Out of Stock</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card-enhanced" data-aos="fade-up" data-aos-delay="500">
                    <div class="stats-number text-info"><i class="fas fa-rupee-sign me-2"></i><?php echo formatCurrency($stats['avg_price'] ?? 0); ?></div>
                    <div class="stats-label">Avg. Price</div>
                </div>
            </div>
            <div class="col-md-1">
                <div class="stats-card-enhanced" data-aos="fade-up" data-aos-delay="600">
                    <div class="stats-number text-dark"><i class="fas fa-cubes me-2"></i><?php echo number_format($stats['total_stock']); ?></div>
                    <div class="stats-label">Total Stock</div>
                </div>
            </div>
        </div>
        
        <!-- Search and Filters -->
        <div class="search-filters" data-aos="fade-up" data-aos-delay="700">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categoriesList as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($category)); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stock" class="form-select">
                        <option value="">All Stock</option>
                        <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="products.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Products Grid -->
        <?php if ($products->num_rows > 0): ?>
            <div class="row">
                <?php $delay = 800; while($product = $products->fetch_assoc()): $delay += 100; ?>
                <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <div class="product-card">
                        <!-- Product Image -->
                        <div class="mb-3">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="product-image"
                                     onerror="this.src='../images/placeholder-product.png'">
                            <?php else: ?>
                                <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="mb-2">
                            <h6 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h6>
                            <span class="product-category"><?php echo htmlspecialchars(ucfirst($product['category'] ?? 'Uncategorized')); ?></span>
                        </div>
                        
                        <!-- Description -->
                        <?php if (!empty($product['description'])): ?>
                        <p class="text-muted small mb-2">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                        </p>
                        <?php endif; ?>
                        
                        <!-- Price and Stock -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                            <div>
                                <?php 
                                $stockClass = $product['stock'] == 0 ? 'stock-out' : ($product['stock'] < 10 ? 'stock-low' : 'stock-good');
                                $stockText = $product['stock'] == 0 ? 'Out of Stock' : ($product['stock'] < 10 ? 'Low Stock' : 'In Stock');
                                ?>
                                <span class="stock-indicator <?php echo $stockClass; ?>">
                                    <?php echo $product['stock']; ?> items
                                </span>
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-3">
                            <span class="badge <?php echo $product['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        
                        <!-- Actions -->
                        <div class="action-buttons d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary flex-fill" 
                                    onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                    title="Edit Product">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-success" 
                                    onclick="updateStock(<?php echo $product['id']; ?>, <?php echo $product['stock']; ?>)"
                                    title="Update Stock">
                                <i class="fas fa-boxes"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')"
                                    title="Delete Product">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>">
                                Previous
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>">
                                Next
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Start by adding your first product to the catalog</p>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" name="category" 
                                           placeholder="e.g., oils, powders, teas">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Product description and benefits..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (₹) *</label>
                                    <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" name="stock" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image Path</label>
                            <input type="text" class="form-control" name="image" 
                                   placeholder="images/product-name.png">
                            <div class="form-text">Relative path to product image (optional)</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_product">
                        <input type="hidden" name="product_id" id="editProductId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editName" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="name" id="editName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editCategory" class="form-label">Category</label>
                                    <input type="text" class="form-control" name="category" id="editCategory">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editPrice" class="form-label">Price (₹) *</label>
                                    <input type="number" class="form-control" name="price" id="editPrice" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editStock" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" name="stock" id="editStock" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Image Path</label>
                            <input type="text" class="form-control" name="image" id="editImage">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive">
                                <label class="form-check-label" for="editIsActive">
                                    Product is active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stock Update Modal -->
    <div class="modal fade" id="stockModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_stock">
                        <input type="hidden" name="product_id" id="stockProductId">
                        <div class="mb-3">
                            <label for="stockQuantity" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" name="stock" id="stockQuantity" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the product <strong id="deleteProductName"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-warning"></i> This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <button type="submit" class="btn btn-danger">Delete Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true
        });
        function editProduct(product) {
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editCategory').value = product.category || '';
            document.getElementById('editDescription').value = product.description || '';
            document.getElementById('editPrice').value = product.price;
            document.getElementById('editStock').value = product.stock;
            document.getElementById('editImage').value = product.image || '';
            document.getElementById('editIsActive').checked = product.is_active == 1;
            
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        }
        
        function updateStock(productId, currentStock) {
            document.getElementById('stockProductId').value = productId;
            document.getElementById('stockQuantity').value = currentStock;
            
            new bootstrap.Modal(document.getElementById('stockModal')).show();
        }
        
        function deleteProduct(productId, productName) {
            document.getElementById('deleteProductId').value = productId;
            document.getElementById('deleteProductName').textContent = productName;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        
        // Image error handling
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image');
            images.forEach(img => {
                img.addEventListener('error', function() {
                    this.src = '../images/placeholder-product.png';
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>