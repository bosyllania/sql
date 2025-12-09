<?php
// admindb.php
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // CREATE
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $product_name = trim($_POST['Product_Name']);
        $description = trim($_POST['Description']);
        $price = floatval($_POST['Price']);
        $image = trim($_POST['Image']);
        
        $sql = "INSERT INTO products (Product_Name, Description, Price, Image) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_name, $description, $price, $image]);
        
        header("Location: admindb.php?success=created");
        exit();
    }
    
    // UPDATE
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $id = intval($_POST['ProductID']);
        $product_name = trim($_POST['Product_Name']);
        $description = trim($_POST['Description']);
        $price = floatval($_POST['Price']);
        $image = trim($_POST['Image']);
        
        $sql = "UPDATE products SET Product_Name = ?, Description = ?, Price = ?, Image = ? WHERE ProductID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_name, $description, $price, $image, $id]);
        
        header("Location: admindb.php?success=updated");
        exit();
    }
    
    // DELETE
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['ProductID']);
        
        $sql = "DELETE FROM products WHERE ProductID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        header("Location: admindb.php?success=deleted");
        exit();
    }
}

// READ - Fetch all products
$sql = "SELECT * FROM products ORDER BY ProductID DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GymFlex Fusion Hub</title>
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

        /* Navbar */
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
            padding: 40px 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 32px;
            color: #1a1a1a;
        }

        .btn-add {
            background-color: #5B4FFF;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-add:hover {
            background-color: #4A3FE8;
        }

        /* Success Message */
        .success-message {
            background-color: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Products Table */
        .products-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #f9fafb;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-top: 1px solid #e5e7eb;
            color: #4b5563;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        .product-name {
            font-weight: 600;
            color: #1a1a1a;
        }

        .product-price {
            color: #5B4FFF;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-edit {
            background-color: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background-color: #2563eb;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h2 {
            font-size: 24px;
            color: #1a1a1a;
        }

        .close {
            font-size: 32px;
            font-weight: 300;
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #4b5563;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #5B4FFF;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .btn-submit {
            background-color: #5B4FFF;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #4A3FE8;
        }

        .btn-cancel {
            background-color: #e5e7eb;
            color: #374151;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-cancel:hover {
            background-color: #d1d5db;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="admindb.php" class="navbar-brand">ADMIN</a>
        <ul class="navbar-menu">
            <li><a href="admindb.php">Products</a></li>
            <li><a href="membership.php">Membership</a></li>
            <li><a href="history.php">History</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="header">
            <h1>Manage Products</h1>
            <button class="btn-add" onclick="openModal('create')">+ Add New Product</button>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message" id="successMessage">
                <?php
                    if ($_GET['success'] === 'created') echo 'Product created successfully!';
                    if ($_GET['success'] === 'updated') echo 'Product updated successfully!';
                    if ($_GET['success'] === 'deleted') echo 'Product deleted successfully!';
                ?>
            </div>
        <?php endif; ?>

        <div class="products-table">
            <?php if (count($products) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>IMAGE</th>
                            <th>PRODUCT NAME</th>
                            <th>DESCRIPTION</th>
                            <th>PRICE</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['Image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['Product_Name']); ?>" 
                                         class="product-image"
                                         onerror="this.src='https://via.placeholder.com/60'">
                                </td>
                                <td class="product-name"><?php echo htmlspecialchars($product['Product_Name']); ?></td>
                                <td><?php echo htmlspecialchars($product['Description']); ?></td>
                                <td class="product-price">$<?php echo number_format($product['Price'], 2); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-edit" onclick='openModal("edit", <?php echo json_encode($product); ?>)'>Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="ProductID" value="<?php echo $product['ProductID']; ?>">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No products yet</h3>
                    <p>Click "Add New Product" to create your first product</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="productForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="ProductID" id="productId">
                
                <div class="form-group">
                    <label for="Product_Name">Product Name</label>
                    <input type="text" id="Product_Name" name="Product_Name" required>
                </div>
                
                <div class="form-group">
                    <label for="Description">Description</label>
                    <textarea id="Description" name="Description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="Price">Price ($)</label>
                    <input type="number" id="Price" name="Price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="Image">Image URL</label>
                    <input type="url" id="Image" name="Image" required placeholder="https://example.com/image.jpg">
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit" id="submitBtn">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(action, product = null) {
            const modal = document.getElementById('productModal');
            const modalTitle = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('productForm');
            
            // Reset form
            form.reset();
            
            if (action === 'create') {
                modalTitle.textContent = 'Add New Product';
                formAction.value = 'create';
                submitBtn.textContent = 'Add Product';
                document.getElementById('productId').value = '';
            } else if (action === 'edit' && product) {
                modalTitle.textContent = 'Edit Product';
                formAction.value = 'update';
                submitBtn.textContent = 'Update Product';
                document.getElementById('productId').value = product.ProductID;
                document.getElementById('Product_Name').value = product.Product_Name;
                document.getElementById('Description').value = product.Description;
                document.getElementById('Price').value = product.Price;
                document.getElementById('Image').value = product.Image;
            }
            
            modal.classList.add('active');
        }
        
        function closeModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Auto-hide success message
        setTimeout(function() {
            const successMsg = document.getElementById('successMessage');
            if (successMsg) {
                successMsg.style.opacity = '0';
                successMsg.style.transition = 'opacity 0.5s';
                setTimeout(() => successMsg.remove(), 500);
            }
        }, 3000);
    </script>
</body>
</html>