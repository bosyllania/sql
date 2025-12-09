<?php
// workouts.php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['fullname'];

// Handle plan purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_plan'])) {
    $planName = $_POST['plan_name'];
    $planType = $_POST['plan_type'];
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']); // in days
    
    // Check if user already has an active membership of this type
    $sql = "SELECT * FROM memberships WHERE user_id = ? AND plan_type = ? AND end_date >= NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $planType]);
    $existingMembership = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingMembership) {
        // Insert new membership
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
        
        $sql = "INSERT INTO memberships (user_id, plan_name, plan_type, price, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $planName, $planType, $price, $startDate, $endDate]);
        
        // Add to history
        $sql = "INSERT INTO history (name, purchases, total, purchase_type) VALUES (?, ?, ?, 'membership')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userName, $planName, $price]);
        
        $purchaseSuccess = true;
    }
}

// Get active memberships for current user
$sql = "SELECT * FROM memberships WHERE user_id = ? AND end_date >= NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$activeMemberships = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create an array for easy checking
$activeSessionPlan = false;
$activeMonthlyPlan = false;

foreach ($activeMemberships as $membership) {
    if ($membership['plan_type'] === 'session') {
        $activeSessionPlan = $membership;
    } elseif ($membership['plan_type'] === 'monthly') {
        $activeMonthlyPlan = $membership;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Plans - GymFlex Fusion Hub</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .page-title {
            font-size: 36px;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 40px;
        }

        /* Tab Buttons */
        .tab-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .tab-btn {
            padding: 12px 30px;
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .tab-btn.active {
            background-color: #5B4FFF;
            color: white;
        }

        /* Plans Container */
        .plans-container {
            display: none;
        }

        .plans-container.active {
            display: block;
        }

        .plans-grid {
            display: grid;
            gap: 30px;
        }

        /* Plan Card */
        .plan-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            transition: transform 0.3s;
        }

        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .plan-icon {
            width: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .plan-icon.cyan {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .plan-icon.purple {
            background: linear-gradient(135deg, #c084fc 0%, #a855f7 100%);
        }

        .plan-icon.pink {
            background: linear-gradient(135deg, #f472b6 0%, #ec4899 100%);
        }

        .plan-icon svg {
            width: 80px;
            height: 80px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }

        .plan-content {
            padding: 30px;
            flex: 1;
        }

        .plan-name {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .plan-description {
            font-size: 15px;
            color: #666;
            margin-bottom: 20px;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 25px;
        }

        .plan-features li {
            padding: 8px 0;
            color: #059669;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-features li:before {
            content: "✓";
            color: #059669;
            font-weight: bold;
            font-size: 16px;
        }

        .plan-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .plan-price {
            font-size: 32px;
            font-weight: 700;
            color: #5B4FFF;
        }

        .plan-price .period {
            font-size: 16px;
            color: #666;
            font-weight: 500;
        }

        .plan-price .original-price {
            font-size: 16px;
            color: #9ca3af;
            text-decoration: line-through;
            margin-left: 8px;
        }

        .btn-select-plan {
            padding: 12px 28px;
            background-color: #5B4FFF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-select-plan:hover {
            background-color: #4A3FE8;
        }

        .btn-select-plan:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
        }

        .btn-select-plan.active {
            background-color: #10b981;
            cursor: default;
        }

        .btn-select-plan.active:hover {
            background-color: #10b981;
        }

        .active-badge {
            display: inline-block;
            padding: 6px 12px;
            background-color: #d1fae5;
            color: #065f46;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .expiry-info {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

        /* Success Modal */
        .success-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .success-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-content {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            animation: slideUp 0.3s;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background-color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-content h2 {
            color: #10b981;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .success-content p {
            color: #666;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .btn-close-modal {
            background-color: #5B4FFF;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Gymrats</a>
        <ul class="navbar-menu">
            <li><a href="store.php">Store</a></li>
            <li><a href="workouts.php">Workouts</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h1 class="page-title">Workout Plans</h1>

        <!-- Tab Buttons -->
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('session')">Session Plans</button>
            <button class="tab-btn" onclick="showTab('monthly')">Monthly Plans</button>
        </div>

        <!-- Session Plans -->
        <div class="plans-container active" id="sessionPlans">
            <div class="plans-grid">
                <!-- Single Session -->
                <div class="plan-card">
                    <div class="plan-icon cyan">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="plan-content">
                        <?php if ($activeSessionPlan && $activeSessionPlan['plan_name'] === 'Single Session'): ?>
                            <div class="active-badge">ACTIVE</div>
                        <?php endif; ?>
                        <h3 class="plan-name">Single Session</h3>
                        <p class="plan-description">Perfect for trying out our facilities or dropping in when you're in town</p>
                        <ul class="plan-features">
                            <li>1 full day access</li>
                        </ul>
                        <?php if ($activeSessionPlan && $activeSessionPlan['plan_name'] === 'Single Session'): ?>
                            <div class="expiry-info">
                                Expires: <?php echo date('M d, Y', strtotime($activeSessionPlan['end_date'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="plan-footer">
                            <div class="plan-price">$1.00</div>
                            <?php if ($activeSessionPlan && $activeSessionPlan['plan_name'] === 'Single Session'): ?>
                                <button class="btn-select-plan active" disabled>Active</button>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="purchase_plan" value="1">
                                    <input type="hidden" name="plan_name" value="Single Session">
                                    <input type="hidden" name="plan_type" value="session">
                                    <input type="hidden" name="price" value="1.00">
                                    <input type="hidden" name="duration" value="1">
                                    <button type="submit" class="btn-select-plan">Select Plan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 5-Session Pack -->
                <div class="plan-card">
                    <div class="plan-icon purple">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div class="plan-content">
                        <?php if ($activeSessionPlan && $activeSessionPlan['plan_name'] === '5-Session Pack'): ?>
                            <div class="active-badge">ACTIVE</div>
                        <?php endif; ?>
                        <h3 class="plan-name">5-Session Pack</h3>
                        <p class="plan-description">Ideal for those who visit occasionally but want better value</p>
                        <ul class="plan-features">
                            <li>5 full day accesses</li>
                        </ul>
                        <?php if ($activeSessionPlan && $activeSessionPlan['plan_name'] === '5-Session Pack'): ?>
                            <div class="expiry-info">
                                Expires: <?php echo date('M d, Y', strtotime($activeSessionPlan['end_date'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="plan-footer">
                            <div class="plan-price">
                                $4.00
                                <span class="original-price">$5.00</span>
                            </div>
                            <?php if ($activeSessionPlan && $activeSessionPlan['plan_name'] === '5-Session Pack'): ?>
                                <button class="btn-select-plan active" disabled>Active</button>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="purchase_plan" value="1">
                                    <input type="hidden" name="plan_name" value="5-Session Pack">
                                    <input type="hidden" name="plan_type" value="session">
                                    <input type="hidden" name="price" value="4.00">
                                    <input type="hidden" name="duration" value="5">
                                    <button type="submit" class="btn-select-plan">Select Plan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Plans -->
        <div class="plans-container" id="monthlyPlans">
            <div class="plans-grid">
                <!-- Basic Monthly -->
                <div class="plan-card">
                    <div class="plan-icon cyan">
                        <svg viewBox="0 0 24 24">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                    </div>
                    <div class="plan-content">
                        <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Basic Monthly'): ?>
                            <div class="active-badge">ACTIVE</div>
                        <?php endif; ?>
                        <h3 class="plan-name">Basic Monthly</h3>
                        <p class="plan-description">Perfect for beginners starting their fitness journey</p>
                        <ul class="plan-features">
                            <li>Unlimited gym access</li>
                            <li>Access to cardio equipment</li>
                            <li>Locker room facilities</li>
                        </ul>
                        <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Basic Monthly'): ?>
                            <div class="expiry-info">
                                Expires: <?php echo date('M d, Y', strtotime($activeMonthlyPlan['end_date'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="plan-footer">
                            <div class="plan-price">
                                $29.99
                                <span class="period">/month</span>
                            </div>
                            <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Basic Monthly'): ?>
                                <button class="btn-select-plan active" disabled>Active</button>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="purchase_plan" value="1">
                                    <input type="hidden" name="plan_name" value="Basic Monthly">
                                    <input type="hidden" name="plan_type" value="monthly">
                                    <input type="hidden" name="price" value="29.99">
                                    <input type="hidden" name="duration" value="30">
                                    <button type="submit" class="btn-select-plan">Select Plan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Standard Monthly -->
                <div class="plan-card">
                    <div class="plan-icon purple">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="7"></circle>
                            <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                        </svg>
                    </div>
                    <div class="plan-content">
                        <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Standard Monthly'): ?>
                            <div class="active-badge">ACTIVE</div>
                        <?php endif; ?>
                        <h3 class="plan-name">Standard Monthly</h3>
                        <p class="plan-description">Great for regular gym-goers who want more amenities</p>
                        <ul class="plan-features">
                            <li>All Basic features</li>
                            <li>Access to weight training area</li>
                            <li>Group fitness classes</li>
                            <li>Free towel service</li>
                        </ul>
                        <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Standard Monthly'): ?>
                            <div class="expiry-info">
                                Expires: <?php echo date('M d, Y', strtotime($activeMonthlyPlan['end_date'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="plan-footer">
                            <div class="plan-price">
                                $49.99
                                <span class="period">/month</span>
                            </div>
                            <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Standard Monthly'): ?>
                                <button class="btn-select-plan active" disabled>Active</button>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="purchase_plan" value="1">
                                    <input type="hidden" name="plan_name" value="Standard Monthly">
                                    <input type="hidden" name="plan_type" value="monthly">
                                    <input type="hidden" name="price" value="49.99">
                                    <input type="hidden" name="duration" value="30">
                                    <button type="submit" class="btn-select-plan">Select Plan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Premium Monthly -->
                <div class="plan-card">
                    <div class="plan-icon pink">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <div class="plan-content">
                        <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Premium Monthly'): ?>
                            <div class="active-badge">ACTIVE</div>
                        <?php endif; ?>
                        <h3 class="plan-name">Premium Monthly</h3>
                        <p class="plan-description">Ultimate access with all premium amenities and services</p>
                        <ul class="plan-features">
                            <li>All Standard features</li>
                            <li>Personal training sessions (2/month)</li>
                            <li>Access to spa & sauna</li>
                            <li>Nutrition consultation</li>
                            <li>Priority class booking</li>
                        </ul>
                        <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Premium Monthly'): ?>
                            <div class="expiry-info">
                                Expires: <?php echo date('M d, Y', strtotime($activeMonthlyPlan['end_date'])); ?>
                            </div>
                        <?php endif; ?>
                        <div class="plan-footer">
                            <div class="plan-price">
                                $79.99
                                <span class="period">/month</span>
                            </div>
                            <?php if ($activeMonthlyPlan && $activeMonthlyPlan['plan_name'] === 'Premium Monthly'): ?>
                                <button class="btn-select-plan active" disabled>Active</button>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="purchase_plan" value="1">
                                    <input type="hidden" name="plan_name" value="Premium Monthly">
                                    <input type="hidden" name="plan_type" value="monthly">
                                    <input type="hidden" name="price" value="79.99">
                                    <input type="hidden" name="duration" value="30">
                                    <button type="submit" class="btn-select-plan">Select Plan</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <?php if (isset($purchaseSuccess) && $purchaseSuccess): ?>
    <div class="success-modal active" id="successModal">
        <div class="success-content">
            <div class="success-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2>Plan Activated!</h2>
            <p>Your workout plan has been successfully activated and is now ready to use.</p>
            <button onclick="window.location.href='workouts.php'" class="btn-close-modal">Continue</button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function showTab(tab) {
            // Hide all plans
            document.querySelectorAll('.plans-container').forEach(container => {
                container.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            if (tab === 'session') {
                document.getElementById('sessionPlans').classList.add('active');
                document.querySelectorAll('.tab-btn')[0].classList.add('active');
            } else {
                document.getElementById('monthlyPlans').classList.add('active');
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }
        }
    </script>
</body>
</html>