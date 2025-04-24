<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

// Adicione instruções de depuração para verificar os valores das variáveis de sessão
// var_dump($_SESSION['user_id'], $_SESSION['is_admin']);

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) { // Mudança para verificar o valor numérico de is_admin
    // Redirecionar se o usuário não for um administrador
    header('Location: /pages/userlogin/login.php'); // Caminho ajustado
    exit();
}


// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $order_id = (int)$_POST['order_id'];
                $status = $conn->real_escape_string($_POST['status']);
                $sql = "UPDATE orders SET status='$status', updated_at=NOW() WHERE id=$order_id";
                $conn->query($sql);
                break;
                
            case 'add_note':
                $order_id = (int)$_POST['order_id'];
                $note = $conn->real_escape_string($_POST['note']);
                $sql = "UPDATE orders SET notes=CONCAT(IFNULL(notes,''), '\n', NOW(), ': $note') 
                        WHERE id=$order_id";
                $conn->query($sql);
                break;
        }
    }
}

// Get orders with optional filtering
$where = "1=1";
if (isset($_GET['status']) && $_GET['status'] !== 'all') {
    $status = $conn->real_escape_string($_GET['status']);
    $where .= " AND status='$status'";
}

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where .= " AND (order_number LIKE '%$search%' OR customer_name LIKE '%$search%')";
}

$sql = "SELECT * FROM orders WHERE $where ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Order Management</h2>
        
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search orders..." value="<?php echo $_GET['search'] ?? ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="manage-orders.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Orders List -->
        <div class="card">
            <div class="card-header">
                Orders List
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td>$<?php echo number_format($row['total'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($row['status']) {
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#orderModal<?php echo $row['id']; ?>">
                                    View Details
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Order Details Modal -->
                        <div class="modal fade" id="orderModal<?php echo $row['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Order Details - #<?php echo htmlspecialchars($row['order_number']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Customer Information</h6>
                                                <p>Name: <?php echo htmlspecialchars($row['customer_name']); ?><br>
                                                   Email: <?php echo htmlspecialchars($row['customer_email']); ?><br>
                                                   Phone: <?php echo htmlspecialchars($row['customer_phone']); ?></p>
                                                
                                                <h6>Shipping Address</h6>
                                                <p><?php echo nl2br(htmlspecialchars($row['shipping_address'])); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Order Status</h6>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                    <select name="status" class="form-select mb-3">
                                                        <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $row['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $row['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $row['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="cancelled" <?php echo $row['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                </form>
                                                
                                                <h6 class="mt-4">Add Note</h6>
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="add_note">
                                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                                    <textarea name="note" class="form-control mb-2" rows="2" required></textarea>
                                                    <button type="submit" class="btn btn-secondary">Add Note</button>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <h6 class="mt-4">Order Items</h6>
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $items = json_decode($row['items'], true);
                                                foreach ($items as $item):
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                    <td>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                    <td><strong>$<?php echo number_format($row['total'], 2); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        
                                        <?php if ($row['notes']): ?>
                                        <h6 class="mt-4">Order Notes</h6>
                                        <pre class="bg-light p-2"><?php echo htmlspecialchars($row['notes']); ?></pre>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>