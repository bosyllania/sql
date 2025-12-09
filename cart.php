<?php
// cart.php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Update quantity in cart
    if ($quantity > 0) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] = $quantity;
                break;
            }
        }
    } else {
        // If quantity is 0 or less, remove item
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $productId) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    
    // Redirect back to cart
    header("Location: cart.php");
    exit();
}

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $productId = intval($_POST['product_id']);
    
    // Remove item from cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $productId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Reindex array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    // Redirect back to cart
    header("Location: cart.php");
    exit();
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (count($_SESSION['cart']) > 0) {
        $userName = $_SESSION['fullname'];
        
        // Process each item in cart
        foreach ($_SESSION['cart'] as $item) {
            $purchaseName = $item['name'] . ' (x' . $item['quantity'] . ')';
            $total = $item['price'] * $item['quantity'];
            
            // Insert into history
            $sql = "INSERT INTO history (name, purchases, total, purchase_type) VALUES (?, ?, ?, 'product')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userName, $purchaseName, $total]);
        }
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        $checkoutSuccess = true;
    }
}

// Calculate cart count and total
$cartCount = 0;
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'];
    $cartTotal += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - GymFlex Fusion Hub</title>
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

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 36px;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        .back-link {
            display: inline-block;
            color: #5B4FFF;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Cart Container */
        .cart-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Cart Items */
        .cart-items {
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
            align-items: center;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            font-size: 18px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .cart-item-price {
            color: #5B4FFF;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .quantity-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #d1d5db;
            background-color: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #374151;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }

        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 16px;
        }

        .remove-item-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
            padding: 6px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .remove-item-btn:hover {
            background-color: #fee2e2;
        }

        .cart-item-subtotal {
            text-align: right;
            min-width: 120px;
        }

        .subtotal-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }

        .subtotal-amount {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 20px;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            color: #9ca3af;
        }

        .empty-cart h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #6b7280;
        }

        .empty-cart p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn-continue-shopping {
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

        .btn-continue-shopping:hover {
            background-color: #4A3FE8;
        }

        /* Cart Summary */
        .cart-summary {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .summary-row.total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .summary-label {
            color: #666;
            font-weight: 500;
        }

        .summary-label.total {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .summary-value {
            font-weight: 600;
            color: #1a1a1a;
        }

        .summary-value.total {
            font-size: 28px;
            font-weight: 700;
            color: #5B4FFF;
        }

        .checkout-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        .btn-checkout {
            flex: 1;
            padding: 16px;
            background-color: #5B4FFF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-checkout:hover {
            background-color: #4A3FE8;
        }

        .btn-checkout:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
        }

        .btn-continue {
            padding: 16px 30px;
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-continue:hover {
            background-color: #d1d5db;
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-close-modal:hover {
            background-color: #4A3FE8;
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
        <a href="store.php" class="back-link">← Back to Store</a>
        
        <div class="page-header">
            <h1 class="page-title">Shopping Cart</h1>
        </div>

        <div class="cart-container">
            <?php if (count($_SESSION['cart']) > 0): ?>
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="cart-item-image"
                                 onerror="this.src='https://via.placeholder.com/100'">
                            
                            <div class="cart-item-details">
                                <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
                                
                                <div class="quantity-controls">
                                    <span class="quantity-label">Quantity:</span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="update_quantity" value="1">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                        <button type="submit" class="quantity-btn">−</button>
                                    </form>
                                    
                                    <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="update_quantity" value="1">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                        <button type="submit" class="quantity-btn">+</button>
                                    </form>
                                </div>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="remove_from_cart" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="remove-item-btn" onclick="return confirm('Remove this item from cart?')">Remove Item</button>
                                </form>
                            </div>
                            
                            <div class="cart-item-subtotal">
                                <div class="subtotal-label">Subtotal</div>
                                <div class="subtotal-amount">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal (<?php echo $cartCount; ?> items)</span>
                        <span class="summary-value">$<?php echo number_format($cartTotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="summary-label total">Total Amount</span>
                        <span class="summary-value total">$<?php echo number_format($cartTotal, 2); ?></span>
                    </div>

                    <div class="checkout-actions">
                        <a href="store.php" class="btn-continue">Continue Shopping</a>
                        <form method="POST" style="flex: 1;">
                            <input type="hidden" name="checkout" value="1">
                            <button type="submit" class="btn-checkout">Confirm Purchase</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <h3>Your cart is empty</h3>
                    <p>Add some products to get started!</p>
                    <a href="store.php" class="btn-continue-shopping">Continue Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Success Modal -->
    <?php if (isset($checkoutSuccess) && $checkoutSuccess): ?>
    <div class="success-modal active" id="successModal">
        <div class="success-content">
            <div class="success-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2>Purchase Successful!</h2>
            <p>Your order has been placed successfully and saved to your purchase history.</p>
            <a href="store.php" class="btn-close-modal">Continue Shopping</a>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>