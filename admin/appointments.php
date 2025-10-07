<?php
require_once 'admin_auth.php';
requireAdminLogin();

$admin = getCurrentAdmin();
$conn = getAdminDbConnection();
require_once __DIR__ . '/../email_config.php';

$message = '';
$message_type = '';

// Handle appointment status update via dropdown
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE booking SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $message = "Appointment status updated successfully!";
        $message_type = "success";
        logAdminActivity('appointment_status_update', "Updated appointment ID: $booking_id status to: $status");
        
        // Send email notification for key statuses
        if (in_array($status, ['confirmed','cancelled','completed'], true)) {
            $q = $conn->prepare("SELECT b.*, u.email AS user_email, u.username FROM booking b JOIN users u ON b.user_id = u.id WHERE b.id = ? LIMIT 1");
            $q->bind_param('i', $booking_id);
            if ($q->execute()) {
                $ap = $q->get_result()->fetch_assoc();
                if ($ap) {
                    $email = $ap['user_email'] ?: $ap['email'];
                    if ($email) {
                        sendAppointmentStatusEmail($email, [
                            'status' => $status,
                            'customer_name' => $ap['name'] ?: $ap['username'],
                            'service' => $ap['service'],
                            'preferred_date' => $ap['preferred_date'],
                            'appointment_id' => $ap['id'] ?? null,
                            'phone' => $ap['phone'] ?? '',
                            'notes' => $ap['notes'] ?? ''
                        ]);
                    }
                }
            }
        }
        // Redirect to clear any status filter (e.g., pending) so updated appointment remains visible
        header('Location: appointments.php');
        exit;
    } else {
        $message = "Error updating appointment status.";
        $message_type = "danger";
    }
}

// Quick actions: accept/reject appointment with email notification
if (isset($_POST['action']) && in_array($_POST['action'], ['accept_appointment','reject_appointment'], true) && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    $newStatus = $_POST['action'] === 'accept_appointment' ? 'confirmed' : 'cancelled';

    $q = $conn->prepare("SELECT b.*, u.email AS user_email, u.username FROM booking b JOIN users u ON b.user_id = u.id WHERE b.id = ? LIMIT 1");
    $q->bind_param('i', $booking_id);
    if ($q->execute()) {
        $ap = $q->get_result()->fetch_assoc();
        if ($ap) {
            $stmt = $conn->prepare("UPDATE booking SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $newStatus, $booking_id);
            if ($stmt->execute()) {
                $message = "Appointment " . ($newStatus === 'confirmed' ? 'accepted and confirmed' : 'rejected and cancelled') . " successfully!";
                $message_type = "success";
                logAdminActivity('appointment_quick_update', "Set appointment ID: $booking_id to $newStatus");
                // Send email
                $email = $ap['user_email'] ?: $ap['email'];
                if ($email) {
                    sendAppointmentStatusEmail($email, [
                        'status' => $newStatus,
                        'customer_name' => $ap['name'] ?: $ap['username'],
                        'service' => $ap['service'],
                        'preferred_date' => $ap['preferred_date'],
                        'appointment_id' => $ap['id'] ?? null,
                        'phone' => $ap['phone'] ?? '',
                        'notes' => $ap['notes'] ?? ''
                    ]);
                }
                // Redirect to clear filters so updated appointment stays visible
                header('Location: appointments.php');
                exit;
            } else {
                $message = "Error updating appointment status.";
                $message_type = "danger";
            }
        } else {
            $message = "Appointment not found.";
            $message_type = "danger";
        }
    } else {
        $message = "Error fetching appointment.";
        $message_type = "danger";
    }
}

// Handle appointment deletion
if ($_POST['action'] ?? '' === 'delete_appointment' && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    // Get appointment info for logging
    $apptInfo = $conn->query("SELECT service, name FROM booking WHERE id = $booking_id")->fetch_assoc();
    
    $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $message = "Appointment deleted successfully!";
        $message_type = "success";
        logAdminActivity('appointment_delete', "Deleted appointment: " . $apptInfo['service'] . " for " . $apptInfo['name']);
    } else {
        $message = "Error deleting appointment.";
        $message_type = "danger";
    }
}

// Handle appointment notes update
if ($_POST['action'] ?? '' === 'update_notes' && isset($_POST['booking_id'], $_POST['notes'])) {
    $booking_id = (int)$_POST['booking_id'];
    $notes = trim($_POST['notes']);
    
    $stmt = $conn->prepare("UPDATE booking SET notes = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $notes, $booking_id);
    
    if ($stmt->execute()) {
        $message = "Appointment notes updated successfully!";
        $message_type = "success";
        logAdminActivity('appointment_notes_update', "Updated notes for appointment ID: $booking_id");
    } else {
        $message = "Error updating appointment notes.";
        $message_type = "danger";
    }
}

// Get appointments with pagination and filters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// Per-page selection with sane defaults
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 15;
$allowed_per_page = [10,15,25,50,100];
if (!in_array($per_page, $allowed_per_page, true)) { $per_page = 15; }
$limit = $per_page;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$service_filter = trim($_GET['service'] ?? '');
$phone_filter = trim($_GET['phone'] ?? '');
$id_filter = trim($_GET['id'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$single_date = trim($_GET['date'] ?? '');
$range = trim($_GET['range'] ?? ''); // today, tomorrow, week, overdue, upcoming

// If a single date is provided and no from/to specified, use it for both
if ($single_date !== '' && $date_from === '' && $date_to === '') {
    $date_from = $single_date;
    $date_to = $single_date;
}

// Sorting
$sort_by = $_GET['sort_by'] ?? 'preferred_date';
$sort_order = strtoupper($_GET['sort_order'] ?? 'DESC');
$allowed_sort_fields = ['preferred_date','created_at','status','service','name','id'];
if (!in_array($sort_by, $allowed_sort_fields, true)) { $sort_by = 'preferred_date'; }
$allowed_sort_orders = ['ASC','DESC'];
if (!in_array($sort_order, $allowed_sort_orders, true)) { $sort_order = 'DESC'; }

// Apply quick ranges if provided and no explicit date_from/to
if ($range && $date_from === '' && $date_to === '') {
    $today = new DateTime('today');
    if ($range === 'today') {
        $date_from = $today->format('Y-m-d');
        $date_to = $today->format('Y-m-d');
    } elseif ($range === 'tomorrow') {
        $t = new DateTime('tomorrow');
        $date_from = $t->format('Y-m-d');
        $date_to = $t->format('Y-m-d');
    } elseif ($range === 'week') {
        $start = new DateTime('monday this week');
        $end = new DateTime('sunday this week');
        $date_from = $start->format('Y-m-d');
        $date_to = $end->format('Y-m-d');
    } elseif ($range === 'overdue') {
        // pending appointments before today
        $date_to = $today->format('Y-m-d');
        $status_filter = 'pending';
    } elseif ($range === 'upcoming') {
        $date_from = $today->format('Y-m-d');
        $status_filter = 'confirmed';
    }
}

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $whereClause .= " AND (b.name LIKE ? OR b.email LIKE ? OR b.service LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= "ssss";
}

if ($status_filter !== '') {
    $whereClause .= " AND b.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($service_filter !== '') {
    $whereClause .= " AND b.service = ?";
    $params[] = $service_filter;
    $types .= "s";
}

if ($phone_filter !== '') {
    $whereClause .= " AND b.phone LIKE ?";
    $params[] = "%$phone_filter%";
    $types .= "s";
}

if ($id_filter !== '' && ctype_digit($id_filter)) {
    $whereClause .= " AND b.id = ?";
    $params[] = (int)$id_filter;
    $types .= "i";
}

if ($date_from !== '' && $date_to !== '') {
    $whereClause .= " AND b.preferred_date BETWEEN ? AND ?";
    $params[] = $date_from; $params[] = $date_to; $types .= "ss";
} elseif ($date_from !== '') {
    $whereClause .= " AND b.preferred_date >= ?";
    $params[] = $date_from; $types .= "s";
} elseif ($date_to !== '') {
    $whereClause .= " AND b.preferred_date <= ?";
    $params[] = $date_to; $types .= "s";
}

// Get total count
$countQuery = "SELECT COUNT(*) as total FROM booking b JOIN users u ON b.user_id = u.id $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) { $countStmt->bind_param($types, ...$params); }
$countStmt->execute();
$totalAppointments = (int)$countStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalAppointments / $limit));

// Get appointments
$appointmentQuery = "
    SELECT b.*, u.username, u.email as user_email
    FROM booking b 
    JOIN users u ON b.user_id = u.id 
    $whereClause 
    ORDER BY b.$sort_by $sort_order
    LIMIT $limit OFFSET $offset
";

$appointmentStmt = $conn->prepare($appointmentQuery);
if (!empty($params)) { $appointmentStmt->bind_param($types, ...$params); }
$appointmentStmt->execute();
$appointments = $appointmentStmt->get_result();

// Get unique services for filter
$services = $conn->query("SELECT DISTINCT service FROM booking ORDER BY service");
$servicesList = [];
while ($service = $services->fetch_assoc()) {
    $servicesList[] = $service['service'];
}

// Auto-delete cancelled appointments older than 7 days
$conn->query("DELETE FROM booking WHERE status = 'cancelled' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");

// Dashboard stats
$statsTotalAll = 0; $statsPending = 0; $statsUpcoming = 0; $statsCompleted = 0;
$__r = $conn->query("SELECT COUNT(*) AS c FROM booking");
if ($__r) { $row = $__r->fetch_assoc(); $statsTotalAll = (int)($row['c'] ?? 0); }
$__r2 = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'pending'");
if ($__r2) { $row = $__r2->fetch_assoc(); $statsPending = (int)($row['c'] ?? 0); }
$__r3 = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'confirmed' AND preferred_date >= CURDATE()");
if ($__r3) { $row = $__r3->fetch_assoc(); $statsUpcoming = (int)($row['c'] ?? 0); }
$__r4 = $conn->query("SELECT COUNT(*) AS c FROM booking WHERE status = 'completed'");
if ($__r4) { $row = $__r4->fetch_assoc(); $statsCompleted = (int)($row['c'] ?? 0); }

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management - VEDAMRUT Admin</title>
    
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
                    <h3 class="mb-1">Appointment Management</h3>
                    <p class="mb-0 text-muted">View and manage all customer appointments</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt me-2"></i><?php echo number_format($totalAppointments); ?> Total Appointments
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
        
        <!-- Dashboard Cards -->
        <div class="row g-3 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-3 col-md-6">
                <div class="stats-card-enhanced total-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($statsTotalAll); ?></div>
                        <div class="stats-label">Total Appointments</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card-enhanced pending-card">
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($statsPending); ?></div>
                        <div class="stats-label">Pending Appointments</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card-enhanced upcoming-card">
                    <div class="stats-icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($statsUpcoming); ?></div>
                        <div class="stats-label">Confirmed Appointments</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stats-card-enhanced completed-card">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-content">
                        <div class="stats-number"><?php echo number_format($statsCompleted); ?></div>
                        <div class="stats-label">Completed Appointments</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Advanced Search and Filters -->
        <div class="search-filters" data-aos="fade-up" data-aos-delay="100">
            <form method="GET" class="row g-3">
                <!-- Row 1: Main Search and Quick Filters -->
                <div class="col-lg-4 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search by name, email, phone..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-3">
                    <select name="service" class="form-select">
                        <option value="">All Services</option>
                        <?php foreach ($servicesList as $service): ?>
                        <option value="<?php echo htmlspecialchars($service); ?>" <?php echo $service_filter === $service ? 'selected' : ''; ?>><?php echo htmlspecialchars($service); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <select name="range" class="form-select">
                        <option value="">All Dates</option>
                        <option value="today" <?php echo $range === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="tomorrow" <?php echo $range === 'tomorrow' ? 'selected' : ''; ?>>Tomorrow</option>
                        <option value="week" <?php echo $range === 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="upcoming" <?php echo $range === 'upcoming' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="overdue" <?php echo $range === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                    </select>
                </div>
                
                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-fill"><i class="fas fa-filter"></i> Filter</button>
                    <a href="appointments.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                </div>
                
            </form>
        </div>
        
        <!-- Quick Action Toolbar -->
        <div class="action-toolbar mb-4" data-aos="fade-up" data-aos-delay="150">
            <div class="d-flex justify-content-end align-items-center">
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted small">Show:</span>
                    <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="15" <?php echo $per_page === 15 ? 'selected' : ''; ?>>15</option>
                        <option value="25" <?php echo $per_page === 25 ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo $per_page === 100 ? 'selected' : ''; ?>>100</option>
                    </select>
                    <span class="text-muted small">entries</span>
                </div>
            </div>
        </div>
        
        <!-- Appointments Table -->
        <div class="card" data-aos="fade-up" data-aos-delay="200">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i> Appointments List
                    <span class="badge status-active ms-2"><?php echo number_format($totalAppointments); ?> total</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <?php if ($appointments->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover appointment-table">
                            <thead>
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="200">Customer</th>
                                    <th width="180">Service</th>
                                    <th width="150">Date</th>
                                    <th width="120">Status</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($appointment = $appointments->fetch_assoc()): ?>
                                <tr class="appointment-row">
                                    <td><strong>#<?php echo $appointment['id']; ?></strong></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($appointment['name']); ?></strong>
                                            <small class="text-muted d-block">@<?php echo htmlspecialchars($appointment['username']); ?></small>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($appointment['email']); ?></small>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($appointment['service']); ?></strong>
                                        <?php if (!empty($appointment['notes'])): ?>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars(substr($appointment['notes'], 0, 50)); ?>...
                                        </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo date('M d, Y', strtotime($appointment['preferred_date'])); ?></strong>
                                            <small class="text-muted d-block">
                                                Booked: <?php echo date('M d, Y g:i A', strtotime($appointment['created_at'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onchange="this.submit()">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?php echo $appointment['id']; ?>">
                                            <select name="status" class="form-select form-select-sm status-select">
                                                <option value="pending" <?php echo $appointment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="completed" <?php echo $appointment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" name="action" value="accept_appointment" class="btn btn-sm btn-outline-success" title="Accept / Confirm" <?php echo $appointment['status']==='confirmed' ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline ms-1">
                                                <input type="hidden" name="booking_id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" name="action" value="reject_appointment" class="btn btn-sm btn-outline-warning" title="Reject / Cancel" <?php echo $appointment['status']==='cancelled' ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-primary ms-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal"
                                                    onclick="viewAppointment(<?php echo htmlspecialchars(json_encode($appointment)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger ms-1"
                                                    onclick="deleteAppointment(<?php echo $appointment['id']; ?>, '<?php echo htmlspecialchars($appointment['name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No appointments found</p>
                        <small class="text-muted">Appointments will appear here once customers start booking</small>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php 
                            $baseQs = [
                                'search'=>$search,
                                'status'=>$status_filter,
                                'service'=>$service_filter,
                                'date'=>$single_date,
                            ];
                            function qs($arr){ return http_build_query(array_filter($arr, fn($v)=>$v!=='' && $v!==null)); }
                        ?>
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo qs($baseQs + ['page'=>$page-1]); ?>">Previous</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo qs($baseQs + ['page'=>$i]); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo qs($baseQs + ['page'=>$page+1]); ?>">Next</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Appointment Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="appointmentDetails">
                    <!-- Details will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Notes Modal -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_notes">
                        <input type="hidden" name="booking_id" id="notesBookingId">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notesText" rows="4" 
                                      placeholder="Add any notes or special instructions..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Notes</button>
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
                    <p>Are you sure you want to delete the appointment for <strong id="deleteCustomerName"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-warning"></i> This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_appointment">
                        <input type="hidden" name="booking_id" id="deleteBookingId">
                        <button type="submit" class="btn btn-danger">Delete Appointment</button>
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
        AOS.init({ duration: 600, easing: 'ease-in-out', once: true });
        
        // Per Page Change
        function changePerPage(value) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }
        
        // Enhanced view appointment
        function viewAppointment(appointment) {
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-section mb-4">
                            <h6 class="text-primary"><i class="fas fa-user me-2"></i>Customer Information</h6>
                            <div class="info-card p-3 bg-light rounded">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="user-avatar me-3">${appointment.name.charAt(0).toUpperCase()}</div>
                                    <div>
                                        <h6 class="mb-1">${appointment.name}</h6>
                                        <small class="text-muted">@${appointment.username}</small>
                                    </div>
                                </div>
                                <div class="contact-info">
                                    <p class="mb-2"><i class="fas fa-envelope text-primary me-2"></i><a href="mailto:${appointment.email}">${appointment.email}</a></p>
                                    <p class="mb-0"><i class="fas fa-phone text-primary me-2"></i><a href="tel:${appointment.phone}">${appointment.phone}</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-section mb-4">
                            <h6 class="text-success"><i class="fas fa-calendar-check me-2"></i>Appointment Details</h6>
                            <div class="info-card p-3 bg-light rounded">
                                <div class="appointment-info">
                                    <div class="mb-2">
                                        <strong>Service:</strong> <span class="badge bg-primary">${appointment.service}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Date:</strong> ${new Date(appointment.preferred_date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Status:</strong> <span class="badge bg-${getStatusColor(appointment.status)}">${appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1)}</span>
                                    </div>
                                    <div>
                                        <strong>Booked:</strong> <small class="text-muted">${new Date(appointment.created_at).toLocaleString()}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ${appointment.notes ? `
                <div class="row">
                    <div class="col-12">
                        <div class="info-section">
                            <h6 class="text-info"><i class="fas fa-sticky-note me-2"></i>Additional Notes</h6>
                            <div class="alert alert-info">
                                <p class="mb-0">${appointment.notes}</p>
                            </div>
                        </div>
                    </div>
                </div>
                ` : ''}
            `;
            document.getElementById('appointmentDetails').innerHTML = content;
        }
        
        function getStatusColor(status) {
            switch(status) {
                case 'pending': return 'warning';
                case 'confirmed': return 'info';
                case 'completed': return 'success';
                case 'cancelled': return 'danger';
                default: return 'secondary';
            }
        }
        
        function editNotes(bookingId, currentNotes) {
            document.getElementById('notesBookingId').value = bookingId;
            document.getElementById('notesText').value = currentNotes;
        }
        
        function deleteAppointment(bookingId, customerName) {
            document.getElementById('deleteBookingId').value = bookingId;
            document.getElementById('deleteCustomerName').textContent = customerName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        
        
    </script>
</body>
</html>

<?php $conn->close(); ?>