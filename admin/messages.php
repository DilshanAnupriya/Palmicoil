<?php
require_once '../config/config.php';
require_once '../config/database.php';

// Check if admin is logged in
requireLogin();

$db = getDB();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("UPDATE contact_messages SET status = 'read', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Message marked as read!";
                break;
                
            case 'mark_unread':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("UPDATE contact_messages SET status = 'unread', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Message marked as unread!";
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Message deleted successfully!";
                break;
                
            case 'bulk_action':
                $action = $_POST['bulk_action'];
                $selected_ids = $_POST['selected_messages'] ?? [];
                
                if (!empty($selected_ids) && in_array($action, ['mark_read', 'mark_unread', 'delete'])) {
                    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
                    
                    if ($action == 'mark_read') {
                        $stmt = $db->prepare("UPDATE contact_messages SET status = 'read', updated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($selected_ids);
                        $success = count($selected_ids) . " messages marked as read!";
                    } elseif ($action == 'mark_unread') {
                        $stmt = $db->prepare("UPDATE contact_messages SET status = 'unread', updated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($selected_ids);
                        $success = count($selected_ids) . " messages marked as unread!";
                    } elseif ($action == 'delete') {
                        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
                        $stmt->execute($selected_ids);
                        $success = count($selected_ids) . " messages deleted!";
                    }
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get messages with pagination
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Count total messages
$count_query = "SELECT COUNT(*) FROM contact_messages $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_messages = $count_stmt->fetchColumn();
$total_pages = ceil($total_messages / $per_page);

// Get messages
$query = "SELECT * FROM contact_messages $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Get message counts for status filter
$stmt = $db->query("SELECT status, COUNT(*) as count FROM contact_messages GROUP BY status");
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['count'];
}
$status_counts['all'] = array_sum($status_counts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">
                            <i class="fas fa-leaf text-success me-2"></i>Admin Panel
                        </h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="products.php">
                                <i class="fas fa-box me-2"></i>Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="categories.php">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="pages.php">
                                <i class="fas fa-file-alt me-2"></i>Pages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active bg-success" href="messages.php">
                                <i class="fas fa-envelope me-2"></i>Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Contact Messages</h1>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>
                                        All (<?php echo $status_counts['all'] ?? 0; ?>)
                                    </option>
                                    <option value="unread" <?php echo $status_filter == 'unread' ? 'selected' : ''; ?>>
                                        Unread (<?php echo $status_counts['unread'] ?? 0; ?>)
                                    </option>
                                    <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>
                                        Read (<?php echo $status_counts['read'] ?? 0; ?>)
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name, email, subject, or message...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <?php if (!empty($messages)): ?>
                    <form method="POST" id="bulkForm">
                        <input type="hidden" name="action" value="bulk_action">
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                            <label for="selectAll" class="form-check-label">Select All</label>
                                            
                                            <select name="bulk_action" class="form-select" style="width: auto;">
                                                <option value="">Bulk Actions</option>
                                                <option value="mark_read">Mark as Read</option>
                                                <option value="mark_unread">Mark as Unread</option>
                                                <option value="delete">Delete</option>
                                            </select>
                                            
                                            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirmBulkAction()">
                                                Apply
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <small class="text-muted">
                                            Showing <?php echo count($messages); ?> of <?php echo $total_messages; ?> messages
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages List -->
                        <div class="card">
                            <div class="card-body p-0">
                                <?php foreach ($messages as $message): ?>
                                    <div class="message-item border-bottom p-3 <?php echo $message['status'] == 'unread' ? 'bg-light' : ''; ?>">
                                        <div class="row align-items-start">
                                            <div class="col-auto">
                                                <input type="checkbox" name="selected_messages[]" value="<?php echo $message['id']; ?>" class="form-check-input message-checkbox">
                                            </div>
                                            <div class="col">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1 <?php echo $message['status'] == 'unread' ? 'fw-bold' : ''; ?>">
                                                            <?php echo htmlspecialchars($message['subject']); ?>
                                                        </h6>
                                                        <div class="text-muted small">
                                                            <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                                            &lt;<?php echo htmlspecialchars($message['email']); ?>&gt;
                                                            <?php if (!empty($message['phone'])): ?>
                                                                | <?php echo htmlspecialchars($message['phone']); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-<?php echo $message['status'] == 'unread' ? 'warning' : 'success'; ?>">
                                                            <?php echo ucfirst($message['status']); ?>
                                                        </span>
                                                        <div class="text-muted small mt-1">
                                                            <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <p class="mb-2 text-muted">
                                                    <?php echo nl2br(htmlspecialchars(substr($message['message'], 0, 200))); ?>
                                                    <?php if (strlen($message['message']) > 200): ?>
                                                        <span class="text-primary" style="cursor: pointer;" onclick="toggleMessage(<?php echo $message['id']; ?>)">
                                                            ... Read more
                                                        </span>
                                                    <?php endif; ?>
                                                </p>
                                                
                                                <div id="fullMessage<?php echo $message['id']; ?>" style="display: none;">
                                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <?php if ($message['status'] == 'unread'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="mark_read">
                                                            <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success">
                                                                <i class="fas fa-check me-1"></i>Mark Read
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="mark_unread">
                                                            <input type="hidden" name="id" value="<?php echo $message['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">
                                                                <i class="fas fa-envelope me-1"></i>Mark Unread
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo htmlspecialchars($message['subject']); ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-reply me-1"></i>Reply
                                                    </a>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteMessage(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['subject']); ?>')">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Messages pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No messages found</h5>
                            <p class="text-muted">
                                <?php if (!empty($search) || $status_filter !== 'all'): ?>
                                    Try adjusting your search criteria or filters.
                                <?php else: ?>
                                    Contact messages will appear here when customers submit the contact form.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Delete Form (hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all when individual checkboxes change
        document.querySelectorAll('.message-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.message-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.message-checkbox:checked');
                const selectAll = document.getElementById('selectAll');
                
                if (checkedCheckboxes.length === 0) {
                    selectAll.indeterminate = false;
                    selectAll.checked = false;
                } else if (checkedCheckboxes.length === allCheckboxes.length) {
                    selectAll.indeterminate = false;
                    selectAll.checked = true;
                } else {
                    selectAll.indeterminate = true;
                }
            });
        });

        function toggleMessage(id) {
            const fullMessage = document.getElementById('fullMessage' + id);
            if (fullMessage.style.display === 'none') {
                fullMessage.style.display = 'block';
            } else {
                fullMessage.style.display = 'none';
            }
        }

        function deleteMessage(id, subject) {
            if (confirm(`Are you sure you want to delete the message "${subject}"?`)) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function confirmBulkAction() {
            const selectedCheckboxes = document.querySelectorAll('.message-checkbox:checked');
            const action = document.querySelector('select[name="bulk_action"]').value;
            
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one message.');
                return false;
            }
            
            if (!action) {
                alert('Please select an action.');
                return false;
            }
            
            const actionText = action.replace('_', ' ');
            return confirm(`Are you sure you want to ${actionText} ${selectedCheckboxes.length} selected message(s)?`);
        }
    </script>
</body>
</html>