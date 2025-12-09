<?php
// history.php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_history'])) {
    $historyId = intval($_POST['history_id']);
    
    // Delete from database
    $sql = "DELETE FROM history WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$historyId]);
    
    // Redirect back to history page
    header("Location: history.php");
    exit();
}

// Fetch all purchase history
$sql = "SELECT * FROM history ORDER BY id DESC";
$stmt = $pdo->query($sql);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalPurchases = count($purchases);
$totalRevenue = 0;
$uniqueCustomers = [];

foreach ($purchases as $purchase) {
    $totalRevenue += $purchase['total'];
    if (!in_array($purchase['name'], $uniqueCustomers)) {
        $uniqueCustomers[] = $purchase['name'];
    }
}

$uniqueCustomerCount = count($uniqueCustomers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History - GymFlex Fusion Hub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f5f5;
        }

        /* Navbar Styles */
        .navbar {
            background-color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: #5B4FFF;
            text-decoration: none;
        }

        .navbar-menu {
            display: flex;
            gap: 30px;
            list-style: none;
            align-items: center;
        }

        .navbar-menu a {
            text-decoration: none;
            color: #666;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .navbar-menu a:hover {
            color: #5B4FFF;
        }

        /* Main Content */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 36px;
            color: #1a1a1a;
        }

        .back-link {
            display: inline-block;
            padding: 12px 24px;
            background-color: #e5e7eb;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .back-link:hover {
            background-color: #d1d5db;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #5B4FFF;
        }

        .stat-value.revenue {
            color: #10b981;
        }

        /* History Table */
        .history-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table thead {
            background-color: #f9fafb;
        }

        .history-table th {
            padding: 16px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-table td {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 15px;
            color: #374151;
        }

        .purchase-date {
            color: #6b7280;
            font-size: 14px;
        }

        .history-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .purchase-name {
            color: #1a1a1a;
            font-weight: 500;
        }

        .purchase-total {
            color: #10b981;
            font-weight: 600;
            font-size: 16px;
        }

        .delete-btn {
            background-color: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #fecaca;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #9ca3af;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #6b7280;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn-go-store {
            display: inline-block;
            padding: 12px 30px;
            background-color: #5B4FFF;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-go-store:hover {
            background-color: #4A3FE8;
        }

        /* Action Column */
        .action-column {
            width: 120px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">ADMIN</a>
        <ul class="navbar-menu">
            <li><a href="admindb.php">Products</a></li>
            <li><a href="membership.php">Membership</a></li>
            <li><a href="history.php">History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Purchase History</h1>
            <a href="store.php" class="back-link">← Back to Products</a>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Purchases</div>
                <div class="stat-value"><?php echo $totalPurchases; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value revenue">$<?php echo number_format($totalRevenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Unique Customers</div>
                <div class="stat-value"><?php echo $uniqueCustomerCount; ?></div>
            </div>
        </div>

        <!-- Purchase History Table -->
        <div class="history-container">
            <?php if (count($purchases) > 0): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>PURCHASES</th>
                            <th>TOTAL</th>
                            <th>PURCHASE DATE</th>
                            <th class="action-column">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($purchase['id']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['name']); ?></td>
                                <td class="purchase-name"><?php echo htmlspecialchars($purchase['purchases']); ?></td>
                                <td class="purchase-total">$<?php echo number_format($purchase['total'], 2); ?></td>
                                <td class="purchase-date">
                                    <?php 
                                        $purchaseDate = isset($purchase['created_at']) ? $purchase['created_at'] : $purchase['purchase_date'];
                                        echo date('M d, Y - h:i A', strtotime($purchaseDate)); 
                                    ?>
                                </td>
                                <td class="action-column">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_history" value="1">
                                        <input type="hidden" name="history_id" value="<?php echo $purchase['id']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this purchase record?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No purchase history yet</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>