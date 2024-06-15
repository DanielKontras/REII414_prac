<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$servername = "localhost";
$dbname = "takealittle";
$dbusername = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user role
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $role = $user['role'];

    // Retrieve products based on search query
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    if ($search) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_name LIKE :search");
        $stmt->bindValue(':search', '%' . $search . '%');
    } else {
        $stmt = $conn->prepare("SELECT * FROM products");
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$total_price = 0;

function addToCart($product_index, $product_stock) {
    global $conn, $cart, $total_price;
    if ($product_stock >= 1) {
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_number = ?");
        $stmt->execute([$product_index]);

        $_SESSION['cart'][$product_index] = ($_SESSION['cart'][$product_index] ?? 0) + 1;
        updateTotalPrice();
    } else {
        echo "Product is out of stock.";
    }
}

function clearCart() {
    global $conn, $cart;
    foreach ($cart as $product_index => $count) {
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_number = ?");
        $stmt->execute([$count, $product_index]);
    }
    $_SESSION['cart'] = [];
    $_SESSION['total_price'] = 0;
}

function updateTotalPrice() {
    global $conn, $cart, $total_price;
    $total_price = 0;
    foreach ($cart as $index => $count) {
        $stmt = $conn->prepare("SELECT product_price FROM products WHERE product_number = ?");
        $stmt->execute([$index]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_price += $product['product_price'] * $count;
    }
    $_SESSION['total_price'] = $total_price;
}

// Function to delete a product
function deleteProduct($product_index) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE product_number = ?");
    $stmt->execute([$product_index]);
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
        addToCart($_POST['product_index'], $_POST['product_stock']);
        header("Location: products.php?search=" . urlencode($search));
        exit();
    } elseif (isset($_POST['clear_cart'])) {
        clearCart();
        header("Location: products.php");
        exit();
    } elseif (isset($_POST['update_stock']) && $role === 'vendor') {
        $new_stock = intval($_POST['new_stock']);
        $product_index = intval($_POST['product_index']);
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE product_number = ?");
        $stmt->execute([$new_stock, $product_index]);
        header("Location: products.php?search=" . urlencode($search));
        exit();
    } elseif (isset($_POST['update_price']) && $role === 'administrator') {
        $new_price = floatval($_POST['new_price']);
        $product_index = intval($_POST['product_index']);
        $stmt = $conn->prepare("UPDATE products SET product_price = ? WHERE product_number = ?");
        $stmt->execute([$new_price, $product_index]);
        header("Location: products.php?search=" . urlencode($search));
        exit();
    } elseif (isset($_POST['add_vendor']) && $role === 'administrator') {
        $vendor_name = $_POST['vendor_name'];
        $vendor_number = intval($_POST['vendor_number']);

        // Check if vendor already exists
        $stmt = $conn->prepare("SELECT * FROM vendor WHERE vendor_name = :vendor_name OR vendor_number = :vendor_number");
        $stmt->bindParam(':vendor_name', $vendor_name);
        $stmt->bindParam(':vendor_number', $vendor_number);
        $stmt->execute();
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vendor) {
            $message = "Vendor already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO vendor (vendor_name, vendor_number) VALUES (:vendor_name, :vendor_number)");
            $stmt->bindParam(':vendor_name', $vendor_name);
            $stmt->bindParam(':vendor_number', $vendor_number);
            $stmt->execute();
            $message = "Vendor added successfully!";
        }
    } elseif (isset($_POST['remove_product']) && $role === 'administrator') {
        $product_index = intval($_POST['product_index']);
        deleteProduct($product_index);
        header("Location: products.php?search=" . urlencode($search));
        exit();
    }
}

updateTotalPrice();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f06, #f90);
            color: #000;
            margin: 0;
            padding: 0;
        }
        header, main {
            width: 80%;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            justify-content: center;
            margin: 0;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        nav ul li {
            display: inline;
            margin: 0 10px;
        }
        nav ul li a {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 5px;
        }
        nav ul li a:hover {
            background-color: #fff;
            color: #000;
        }
        .products-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .product {
            border: 1px solid #ddd;
            padding: 10px;
            width: 200px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .product img {
            max-width: 100px;
            max-height: 100px;
            margin-bottom: 10px;
        }
        .cart-container {
            float: right;
            margin-right: 20px;
            text-align: left;
        }
        .total-price {
            font-weight: bold;
            margin-top: 10px;
        }
        .message {
            color: red;
            font-weight: bold;
        }
        main p {
            font-size: 18px;
            color: #000;
        }
        /* Adjust button styling */
        button {
            margin-top: 5px; /* Add space between buttons */
            background-color: #0000FF; /* Green */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.1s;
        }

        button:hover {
            background-color: #37CEEB; /* Darker green */
        }

        .search-bar-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .search-bar-container input[type="text"] {
            width: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px 0 0 5px;
            outline: none;
        }
        .search-bar-container button {
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Browse Products, <?php echo htmlspecialchars($username); ?></h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="orderhistory.php">Order History</a></li>
                <?php if ($role === 'administrator'): ?>
                    <li><a href="addnewproduct.php">Add New Product</a></li>
                <?php endif; ?>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Our Products</h2>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <div class="search-bar-container">
            <form method="get" action="products.php">
                <input type="text" name="search" placeholder="Search for products" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="cart-container">
            <h3>Your Cart</h3>
            <?php if (!empty($cart)): ?>
                <ul>
                    <?php foreach ($cart as $product_index => $count): ?>
                        <?php
                        $stmt = $conn->prepare("SELECT product_name, product_price FROM products WHERE product_number = ?");
                        $stmt->execute([$product_index]);
                        $product = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <li><?php echo htmlspecialchars($product['product_name']); ?> - R<?php echo htmlspecialchars($product['product_price']); ?> x <?php echo $count; ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="total-price">Total: R<?php echo $total_price; ?></p>
                <form action="products.php" method="post">
                    <button type="submit" name="clear_cart">Clear Cart</button>
                </form>
                <form action="checkout.php" method="post">
                    <button type="submit">Checkout</button>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
        <div class="products-container">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <h3><?php echo $product['product_name']; ?></h3>
                    <img src="<?php echo $product['image_path']; ?>" alt="Product Image">
                    <p>Price: R<?php echo $product['product_price']; ?></p>
                    <p>In stock: <?php echo $product['stock_quantity']; ?></p>
                    <?php if ($role === 'vendor' && $product['vendor_name'] === $username): ?>
                        <form action="products.php?search=<?php echo urlencode($search); ?>" method="post">
                            <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
                            <input type="number" name="new_stock" value="<?php echo $product['stock_quantity']; ?>" min="0">
                            <button type="submit" name="update_stock">Update Stock</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($role === 'administrator'): ?>
                        <form action="products.php?search=<?php echo urlencode($search); ?>" method="post">
                            <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
                            <input type="number" name="new_price" 
                            placeholder="New Price" step="0.01" min="0">
                            <button type="submit" name="update_price">Update Price</button>
                        </form>
                        <!-- Form for deleting a product -->
                        <form action="products.php?search=<?php echo urlencode($search); ?>" method="post">
                            <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
                            <button type="submit" name="remove_product" style="background-color: #f44336; color: white; padding: 10px; border: none; border-radius: 5px;">Delete Product</button>
                        </form>
                    <?php endif; ?>
                    <form action="products.php?search=<?php echo urlencode($search); ?>" method="post">
                        <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
                        <input type="hidden" name="product_stock" value="<?php echo $product['stock_quantity']; ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                        <input type="hidden" name="action" value="add_to_cart">
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>


