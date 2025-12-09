<?php
// membership.php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_membership'])) {
    $membershipId = intval($_POST['membership_id']);
    
    $sql = "DELETE FROM memberships WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$membershipId]);
    
    header("Location: membership.php");
    exit();
}

// Fetch all memberships
$sql = "SELECT m.*, m.user_id 
        FROM memberships m 
        ORDER BY m.id DESC";
$stmt = $pdo->query($sql);
$memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user names
$userNames = [];
$userSql = "SELECT userID, Name FROM users"; // include Name column
$userStmt = $pdo->query($userSql);
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $userNames[$user['userID']] = $user['Name']; // correct column name
}


// Calculate statistics
$totalActive = 0;
$totalExpired = 0;
$totalRevenue = 0;

foreach ($memberships as $membership) {
    $endDate = strtotime($membership['end_date']);
    $now = time();
    
    if ($endDate >= $now) {
        $totalActive++;
    } else {
        $totalExpired++;
    }
    
    $totalRevenue += $membership['price'];
}

$totalMemberships = count($memberships);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Management - GymFlex Fusion Hub</title>
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

        .stat-value.active {
            color: #10b981;
        }

        .stat-value.expired {
            color: #ef4444;
        }

        /* Membership Table */
        .membership-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .membership-table {
            width: 100%;
            border-collapse: collapse;
        }

        .membership-table thead {
            background-color: #f9fafb;
        }

        .membership-table th {
            padding: 16px 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .membership-table td {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 15px;
            color: #374151;
        }

        .membership-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .user-name {
            color: #1a1a1a;
            font-weight: 500;
        }

        .plan-name {
            color: #1a1a1a;
            font-weight: 500;
        }

        .plan-type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .plan-type-badge.monthly {
            background-color: #fce7f3;
            color: #831843;
        }

        .price-value {
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

        /* Warning Icon */
        .warning-row {
            background-color: #fef3c7 !important;
        }

        .warning-icon {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #f59e0b;
            font-weight: 600;
        }

        .warning-icon svg {
            width: 20px;
            height: 20px;
            fill: #f59e0b;
        }

        .expired-text {
            color: #dc2626;
            font-weight: 600;
            font-size: 14px;
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

        .btn-go-workouts {
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

        .btn-go-workouts:hover {
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
            <h1 class="page-title">Membership Management</h1>
            <a href="workouts.php" class="back-link">← Back to Workouts</a>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Memberships</div>
                <div class="stat-value"><?php echo $totalMemberships; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Memberships</div>
                <div class="stat-value active"><?php echo $totalActive; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Expired Memberships</div>
                <div class="stat-value expired"><?php echo $totalExpired; ?></div>
            </div>
        </div>

        <!-- Membership Table -->
        <div class="membership-container">
            <?php if (count($memberships) > 0): ?>
                <table class="membership-table">
                    <thead>
                        <tr>
                            <th>USER ID</th>
                            <th>NAME</th>
                            <th>PLAN NAME</th>
                            <th>PLAN TYPE</th>
                            <th>PRICE</th>
                            <th>START DATE</th>
                            <th>END DATE</th>
                            <th class="action-column">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($memberships as $membership): ?>
                            <?php
                                $endDate = strtotime($membership['end_date']);
                                $now = time();
                                $isExpired = $endDate < $now;
                                $userName = isset($userNames[$membership['user_id']]) ? $userNames[$membership['user_id']] : 'Unknown';
                            ?>
                            <tr <?php echo $isExpired ? 'class="warning-row"' : ''; ?>>
                                <td><?php echo htmlspecialchars($membership['user_id']); ?></td>
                                <td class="user-name">
                                    <?php echo htmlspecialchars($userName); ?>
                                    <?php if ($isExpired): ?>
                                        <div class="warning-icon">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                            </svg>
                                            <span class="expired-text">Expired</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="plan-name"><?php echo htmlspecialchars($membership['plan_name']); ?></td>
                                <td>
                                    <span class="plan-type-badge <?php echo $membership['plan_type'] === 'monthly' ? 'monthly' : ''; ?>">
                                        <?php echo ucfirst($membership['plan_type']); ?>
                                    </span>
                                </td>
                                <td class="price-value">$<?php echo number_format($membership['price'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($membership['start_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($membership['end_date'])); ?></td>
                                <td class="action-column">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_membership" value="1">
                                        <input type="hidden" name="membership_id" value="<?php echo $membership['id']; ?>">
                                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this membership?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No memberships yet</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>