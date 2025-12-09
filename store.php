<?php
// store.php
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

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = intval($_POST['product_id']);
    
    // Get product details
    $sql = "SELECT * FROM products WHERE ProductID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }
        
        // If not in cart, add new item
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['ProductID'],
                'name' => $product['Product_Name'],
                'price' => $product['Price'],
                'image' => $product['Image'],
                'quantity' => 1
            ];
        }
    }
    
    // Redirect back to store
    header("Location: store.php");
    exit();
}

// Get search query if exists
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch products from database with search filter
if (!empty($search)) {
    $sql = "SELECT * FROM products WHERE Product_Name LIKE ? OR Description LIKE ? ORDER BY ProductID DESC";
    $stmt = $pdo->prepare($sql);
    $searchTerm = "%{$search}%";
    $stmt->execute([$searchTerm, $searchTerm]);
} else {
    $sql = "SELECT * FROM products ORDER BY ProductID DESC";
    $stmt = $pdo->query($sql);
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate cart count
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - GymFlex Fusion Hub</title>
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
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 36px;
            color: #1a1a1a;
            margin-bottom: 20px;
        }

        /* Search Bar */
        .search-container {
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #5B4FFF;
        }

        .btn-search {
            padding: 14px 30px;
            background-color: #5B4FFF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-search:hover {
            background-color: #4A3FE8;
        }

        .btn-clear {
            padding: 14px 20px;
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-clear:hover {
            background-color: #d1d5db;
        }

        .search-result {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
            font-size: 15px;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .product-image-container {
            width: 100%;
            height: 250px;
            background-color: #f0f0f0;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
            min-height: 40px;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #5B4FFF;
        }

        .btn-add-cart {
            background-color: #E8E6FF;
            color: #5B4FFF;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-add-cart:hover {
            background-color: #5B4FFF;
            color: white;
        }

        /* Floating Cart Button */
        .floating-cart {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: #5B4FFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(91, 79, 255, 0.4);
            cursor: pointer;
            transition: transform 0.3s;
            text-decoration: none;
        }

        .floating-cart:hover {
            transform: scale(1.1);
        }

        .floating-cart svg {
            width: 28px;
            height: 28px;
            stroke: white;
            fill: none;
        }

        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ef4444;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            border: 2px solid white;
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
        <div class="page-header">
            <h1 class="page-title">Gym Supplements Store</h1>
            
            <!-- Search Bar -->
            <div class="search-container">
                <form method="GET" action="store.php" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="Search for products..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                        autofocus>
                    <button type="submit" class="btn-search">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="store.php" class="btn-clear">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($search)): ?>
                <div class="search-result">
                    <?php 
                        $resultCount = count($products);
                        echo $resultCount . ($resultCount === 1 ? ' result' : ' results') . ' found for "' . htmlspecialchars($search) . '"';
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo htmlspecialchars($product['Image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['Product_Name']); ?>" 
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/400x250?text=No+Image'">
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['Product_Name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($product['Description']); ?></p>
                            <div class="product-footer">
                                <span class="product-price">$<?php echo number_format($product['Price'], 2); ?></span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                    <button type="submit" class="btn-add-cart">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No products found</h3>
                <p>
                    <?php if (!empty($search)): ?>
                        Try searching with different keywords or <a href="store.php" style="color: #5B4FFF;">view all products</a>
                    <?php else: ?>
                        No products available at the moment.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Floating Cart Button -->
    <a href="cart.php" class="floating-cart">
        <svg viewBox="0 0 24 24" stroke-width="2">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
        <?php if ($cartCount > 0): ?>
            <span class="cart-badge"><?php echo $cartCount; ?></span>
        <?php endif; ?>
    </a>
</body>
</html>