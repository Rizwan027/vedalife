<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();

// CSRF setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_field() {
    $t = $_SESSION['csrf_token'] ?? '';
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
}
function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
}

// Upload configuration
$projectRoot = dirname(__DIR__);
$uploadDir = $projectRoot . '/uploads/products';
$uploadUrlBase = 'uploads/products';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }

function is_allowed_image(string $tmp, string $name): array {
    $max = 5 * 1024 * 1024;
    if (!file_exists($tmp)) return [false, 'Upload failed'];
    if (filesize($tmp) > $max) return [false, 'File too large (max 5MB)'];

    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];

    // Detect MIME using best available method
    $mime = '';
    if (class_exists('finfo')) {
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file($tmp) ?: '';
    }
    if ($mime === '' && function_exists('mime_content_type')) {
        $mime = @mime_content_type($tmp) ?: '';
    }
    if ($mime === '' && function_exists('getimagesize')) {
        $info = @getimagesize($tmp);
        if (is_array($info) && isset($info['mime'])) $mime = $info['mime'];
    }

    // If we still couldn't detect, fall back to extension check only
    if ($mime !== '' && !isset($allowed[$mime])) {
        return [false, 'Unsupported image type'];
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    // Prefer MIME-derived extension when available
    if ($mime !== '' && isset($allowed[$mime])) {
        $ext = $allowed[$mime];
    }

    if ($ext === '' || !in_array($ext, array_values($allowed), true)) {
        return [false, 'Unsupported or missing image extension'];
    }

    return [true, $ext];
}
function save_uploaded_image(array $file, string $uploadDir, string $uploadUrlBase): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return [false, 'No file uploaded', null];
    [$ok, $extOrMsg] = is_allowed_image($file['tmp_name'], $file['name']);
    if (!$ok) return [false, $extOrMsg, null];
    $ext = $extOrMsg;
    $base = preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
    $name = $base . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return [false, 'Failed to move uploaded file', null];
    return [true, 'ok', rtrim($uploadUrlBase, '/\\') . '/' . $name];
}

$message = '';
$message_type = '';
$action = $_POST['action'] ?? '';
if ($action) { verify_csrf(); }

// Build correct image URL from DB path for admin context
function product_image_url_for_admin(?string $path): string {
    $p = trim((string)$path);
    if ($p === '') return '';
    if (preg_match('~^https?://~i', $p)) return $p; // absolute URL
    // Normalize slashes and remove leading slash
    $p = str_replace('\\\\', '/', $p);
    $p = str_replace('\\', '/', $p);
    $p = ltrim($p, '/');
    return '../' . $p;
}

// Add product (old UI name is add_product, keep compatibility)
if ($action === 'add_product' || $action === 'create') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 1;

    if ($name === '' || $price <= 0) {
        $message = 'Please fill in required fields: Name and valid Price.';
        $message_type = 'danger';
    } else {
        $dup = $conn->prepare('SELECT id FROM products WHERE name = ? LIMIT 1');
        $dup->bind_param('s', $name);
        $dup->execute();
        if ($dup->get_result()->num_rows > 0) {
            $message = 'A product with this name already exists.';
            $message_type = 'danger';
        } else {
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                [$ok, $msg, $stored] = save_uploaded_image($_FILES['image'], $uploadDir, $uploadUrlBase);
                if (!$ok) { $message = 'Image upload error: ' . $msg; $message_type = 'danger'; }
                else { $imagePath = $stored; }
            } else {
// If the old UI provides a text path, accept it as fallback
                $textPath = trim($_POST['image'] ?? '');
                if ($textPath !== '') {
                    $textPath = str_replace('\\\\', '/', $textPath);
                    $textPath = str_replace('\\', '/', $textPath);
                    $textPath = ltrim($textPath, '/');
                }
                $imagePath = $textPath !== '' ? $textPath : null;
            }
            if ($message_type === '') {
                $stmt = $conn->prepare('INSERT INTO products (name, description, price, image, category, stock, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
                $stmt->bind_param('ssdssii', $name, $description, $price, $imagePath, $category, $stock, $is_active);
                if ($stmt->execute()) { $message='Product added successfully!'; $message_type='success'; logAdminActivity('product_add', 'Added product: '.$name); }
                else { $message='Error adding product: ' . $conn->error; $message_type='danger'; }
            }
        }
        $dup->close();
    }
}

// Update product (old UI name update_product)
if ($action === 'update_product' || $action === 'update') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $category = sanitizeInput($_POST['category'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($product_id <= 0 || $name === '' || $price <= 0) { $message='Please provide valid product data.'; $message_type='danger'; }
    else {
        $cur = $conn->prepare('SELECT image FROM products WHERE id = ?');
        $cur->bind_param('i', $product_id);
        $cur->execute();
        $res = $cur->get_result();
        if ($res->num_rows === 0) { $message='Product not found.'; $message_type='danger'; }
        else {
            $currentImage = $res->fetch_assoc()['image'];
            $dup = $conn->prepare('SELECT id FROM products WHERE name = ? AND id <> ? LIMIT 1');
            $dup->bind_param('si', $name, $product_id);
            $dup->execute();
            if ($dup->get_result()->num_rows > 0) { $message='Another product with this name already exists.'; $message_type='danger'; }
            else {
                $newImage = $currentImage;
                if (!empty($_FILES['image']['name'])) {
                    [$ok, $msg, $stored] = save_uploaded_image($_FILES['image'], $uploadDir, $uploadUrlBase);
                    if (!$ok) { $message='Image upload error: ' . $msg; $message_type='danger'; }
                    else { $newImage = $stored; }
} else if (isset($_POST['image']) && trim($_POST['image']) !== '') {
                    $newImage = trim($_POST['image']);
                    $newImage = str_replace('\\\\', '/', $newImage);
                    $newImage = str_replace('\\', '/', $newImage);
                    $newImage = ltrim($newImage, '/');
                }
                if ($message_type === '') {
                    $stmt = $conn->prepare('UPDATE products SET name = ?, description = ?, price = ?, image = ?, category = ?, stock = ?, is_active = ?, updated_at = NOW() WHERE id = ?');
                    $stmt->bind_param('ssdssiii', $name, $description, $price, $newImage, $category, $stock, $is_active, $product_id);
                    if ($stmt->execute()) {
                        if ($newImage !== $currentImage && !empty($currentImage) && strpos($currentImage, $uploadUrlBase . '/') === 0) {
                            $old = $projectRoot . '/' . $currentImage;
                            if (is_file($old)) { @unlink($old); }
                        }
                        $message='Product updated successfully!'; $message_type='success'; logAdminActivity('product_update', 'Updated product ID: '.$product_id);
                    } else { $message='Error updating product: ' . $conn->error; $message_type='danger'; }
                }
            }
            $dup->close();
        }
    }
}

// Delete product (old UI name delete_product)
if ($action === 'delete_product' || $action === 'delete') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if ($product_id <= 0) { $message='Invalid product.'; $message_type='danger'; }
    else {
        $check = $conn->prepare('SELECT COUNT(*) AS c FROM order_items WHERE product_id = ?');
        $check->bind_param('i', $product_id);
        $check->execute();
        $c = ($check->get_result()->fetch_assoc()['c'] ?? 0);
        if ($c > 0) { $message='Cannot delete product referenced in orders. Deactivate instead.'; $message_type='warning'; }
        else {
            $g = $conn->prepare('SELECT image FROM products WHERE id = ?');
            $g->bind_param('i', $product_id);
            $g->execute();
            $img = $g->get_result()->fetch_assoc();
            $imgPath = $img['image'] ?? null;
            $del = $conn->prepare('DELETE FROM products WHERE id = ?');
            $del->bind_param('i', $product_id);
            if ($del->execute()) {
                if (!empty($imgPath) && strpos($imgPath, $uploadUrlBase . '/') === 0) {
                    $old = $projectRoot . '/' . $imgPath;
                    if (is_file($old)) { @unlink($old); }
                }
                $message='Product deleted successfully!'; $message_type='success'; logAdminActivity('product_delete', 'Deleted product ID: '.$product_id);
            } else { $message='Error deleting product: ' . $conn->error; $message_type='danger'; }
        }
    }
}

// Update stock
if ($action === 'update_stock') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    if ($product_id <= 0) { $message='Invalid product.'; $message_type='danger'; }
    else {
        $stmt = $conn->prepare('UPDATE products SET stock = ?, updated_at = NOW() WHERE id = ?');
        $stmt->bind_param('ii', $stock, $product_id);
        if ($stmt->execute()) { $message='Stock updated successfully!'; $message_type='success'; logAdminActivity('stock_update', 'Updated stock for product ID: '.$product_id.' to '.$stock); }
        else { $message='Error updating stock: ' . $conn->error; $message_type='danger'; }
    }
}

// Bulk action
if ($action === 'bulk_action' && isset($_POST['product_ids']) && isset($_POST['bulk_action'])) {
    $product_ids = array_map('intval', (array)$_POST['product_ids']);
    $bulk_action = $_POST['bulk_action'];
    $count = 0;
    if (!empty($product_ids)) {
        if ($bulk_action === 'activate' || $bulk_action === 'deactivate') {
            $val = $bulk_action === 'activate' ? 1 : 0;
            $stmt = $conn->prepare('UPDATE products SET is_active = ?, updated_at = NOW() WHERE id = ?');
            foreach ($product_ids as $id) { $stmt->bind_param('ii', $val, $id); if ($stmt->execute()) { $count++; } }
            $message = "$count products " . ($val ? 'activated' : 'deactivated') . ' successfully.'; $message_type='success';
        } elseif ($bulk_action === 'delete') {
            foreach ($product_ids as $id) {
                $check = $conn->prepare('SELECT COUNT(*) AS c FROM order_items WHERE product_id = ?');
                $check->bind_param('i', $id);
                $check->execute();
                $c = ($check->get_result()->fetch_assoc()['c'] ?? 0);
                if ($c == 0) {
                    $g = $conn->prepare('SELECT image FROM products WHERE id = ?');
                    $g->bind_param('i', $id);
                    $g->execute();
                    $img = $g->get_result()->fetch_assoc();
                    $imgPath = $img['image'] ?? null;
                    $del = $conn->prepare('DELETE FROM products WHERE id = ?');
                    $del->bind_param('i', $id);
                    if ($del->execute()) {
                        if (!empty($imgPath) && strpos($imgPath, $uploadUrlBase . '/') === 0) {
                            $old = $projectRoot . '/' . $imgPath;
                            if (is_file($old)) { @unlink($old); }
                        }
                        $count++;
                    }
                }
            }
            $message = "$count products deleted successfully."; $message_type='success';
        } else { $message='Invalid bulk action.'; $message_type='danger'; }
    } else { $message='No products selected.'; $message_type='warning'; }
}

// Listing
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = strtoupper($_GET['sort_order'] ?? 'DESC');
$allowed_sort_fields = ['name','price','stock','category','created_at','updated_at'];
if (!in_array($sort_by, $allowed_sort_fields)) { $sort_by='created_at'; }
$allowed_sort_orders = ['ASC','DESC'];
if (!in_array($sort_order, $allowed_sort_orders)) { $sort_order='DESC'; }
$where = 'WHERE 1=1';
$params = [];$types='';
if ($search !== '') { $where.=' AND (name LIKE ? OR description LIKE ? OR category LIKE ?)'; $term="%$search%"; $params=array_merge($params,[$term,$term,$term]); $types.='sss'; }
if ($category_filter !== '') { $where.=' AND category = ?'; $params[]=$category_filter; $types.='s'; }
if ($status_filter === 'active') { $where.=' AND is_active = 1'; } elseif ($status_filter === 'inactive') { $where.=' AND is_active = 0'; }
$countSql = "SELECT COUNT(*) AS total FROM products $where"; $countStmt=$conn->prepare($countSql); if (!empty($params)) { $countStmt->bind_param($types, ...$params); } $countStmt->execute(); $totalProducts=(int)$countStmt->get_result()->fetch_assoc()['total']; $totalPages=max(1,(int)ceil($totalProducts/$limit));
$dataSql = "SELECT * FROM products $where ORDER BY $sort_by $sort_order LIMIT $limit OFFSET $offset"; $dataStmt=$conn->prepare($dataSql); if (!empty($params)) { $dataStmt->bind_param($types, ...$params); } $dataStmt->execute(); $products=$dataStmt->get_result();
$catRes=$conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category <> '' ORDER BY category"); $categoriesList=[]; while($c=$catRes->fetch_assoc()){ $categoriesList[]=$c['category']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - VEDAMRUT Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Cormorant+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
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
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search and Filters -->
        <div class="search-filters" data-aos="fade-up" data-aos-delay="200">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= $category_filter === $category ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($category)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
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
                <?php $delay = 300; while($product = $products->fetch_assoc()): $delay += 50; ?>
                <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="product-card">
                        <div class="mb-3">
                            <?php if (!empty($product['image'])): ?>
<?php $imgSrc = product_image_url_for_admin($product['image']); ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" onerror="this.src='../images/placeholder-product.png'">
                            <?php else: ?>
                                <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-2">
                            <h6 class="product-title"><?= htmlspecialchars($product['name']) ?></h6>
                            <span class="product-category"><?= htmlspecialchars(ucfirst($product['category'] ?? 'Uncategorized')) ?></span>
                        </div>
                        <?php if (!empty($product['description'])): ?>
                        <p class="text-muted small mb-2"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="product-price">₹<?= number_format((float)$product['price'], 2) ?></div>
                            <div>
                                <?php $stockClass = $product['stock']==0?'stock-out':($product['stock']<10?'stock-low':'stock-good'); ?>
                                <span class="stock-indicator <?= $stockClass ?>"><?= (int)$product['stock'] ?> items</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="badge <?= $product['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $product['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                        <div class="action-buttons d-flex gap-2">
                            <button type="button"
                                class="btn btn-sm btn-outline-primary flex-fill btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#editProductModal"
                                data-id="<?= (int)$product['id'] ?>"
                                data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES) ?>"
                                data-price="<?= htmlspecialchars((string)$product['price'], ENT_QUOTES) ?>"
                                data-category="<?= htmlspecialchars($product['category'] ?? '', ENT_QUOTES) ?>"
                                data-stock="<?= (int)$product['stock'] ?>"
                                data-image="<?= htmlspecialchars($product['image'] ?? '', ENT_QUOTES) ?>"
                                data-active="<?= (int)$product['is_active'] ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="prepareStock(<?= (int)$product['id'] ?>, <?= (int)$product['stock'] ?>)"><i class="fas fa-boxes"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="prepareDelete(<?= (int)$product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Start by adding your first product to the catalog</p>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fas fa-plus"></i> Add Product</button>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>&status=<?= urlencode($status_filter) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
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
                <form method="POST" enctype="multipart/form-data">
                    <?php csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <input type="text" class="form-control" name="category" placeholder="e.g., oils, powders, teas">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Product description and benefits..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price (₹) *</label>
                                    <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" name="stock" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div class="form-text">Optional: upload an image (JPEG/PNG/WebP/GIF, max 5MB). You can also provide a relative path in the Edit form later.</div>
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
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="update_product">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" class="form-control" name="name" id="edit_name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" name="category" id="edit_category">
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Price (₹) *</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="price" id="edit_price" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" min="0" class="form-control" name="stock" id="edit_stock">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Image (upload to replace)</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Or Image Path</label>
                    <input type="text" class="form-control" name="image" id="edit_image_path" placeholder="images/products/filename.png">
                    <div class="form-text">If a file is uploaded, it will be used instead of this path.</div>
                  </div>
                </div>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" name="is_active" id="edit_is_active">
                <label class="form-check-label" for="edit_is_active">Active</label>
              </div>
              <div class="mt-3" id="edit_image_preview_wrap" style="display:none;">
                <img id="edit_image_preview" src="#" alt="Current image" style="width: 80px; height: 80px; object-fit: cover; border:1px solid #eee; border-radius:6px;">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Changes</button>
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
                    <?php csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_stock">
                        <input type="hidden" name="product_id" id="stockProductId">
                        <div class="mb-3">
                            <label class="form-label">Stock Quantity</label>
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
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <button type="submit" class="btn btn-danger">Delete Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        AOS.init({ duration: 600, easing: 'ease-in-out', once: true });
        function prepareStock(id, stock) { document.getElementById('stockProductId').value = id; document.getElementById('stockQuantity').value = stock; new bootstrap.Modal(document.getElementById('stockModal')).show(); }
        function prepareDelete(id, name) { document.getElementById('deleteProductId').value = id; document.getElementById('deleteProductName').textContent = name; new bootstrap.Modal(document.getElementById('deleteModal')).show(); }
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.product-image').forEach(img=>{
    img.addEventListener('error', function(){ this.src='../images/placeholder-product.png'; });
  });

  // Populate Edit modal when it is about to be shown
  const modalEl = document.getElementById('editProductModal');
  if (modalEl) {
    modalEl.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name') || '';
      const description = btn.getAttribute('data-description') || '';
      const price = btn.getAttribute('data-price') || '';
      const category = btn.getAttribute('data-category') || '';
      const stock = btn.getAttribute('data-stock') || '0';
      const image = btn.getAttribute('data-image') || '';
      const active = btn.getAttribute('data-active') === '1';

      document.getElementById('edit_product_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_description').value = description;
      document.getElementById('edit_price').value = price;
      document.getElementById('edit_category').value = category;
      document.getElementById('edit_stock').value = stock;
      document.getElementById('edit_is_active').checked = active;
      document.getElementById('edit_image_path').value = image;

      const previewWrap = document.getElementById('edit_image_preview_wrap');
      const preview = document.getElementById('edit_image_preview');
      if (image) {
        const norm = image.replace(/\\\\/g, '/').replace(/\\/g, '/').replace(/^\//, '');
        preview.src = '../' + norm;
        previewWrap.style.display = 'block';
      } else {
        previewWrap.style.display = 'none';
        preview.src = '#';
      }
    });
  }
});
    </script>
</body>
</html>
