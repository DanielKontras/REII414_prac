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

    // Retrieve all products
    $stmt = $conn->prepare("SELECT * FROM products");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
        addToCart($_POST['product_index'], $_POST['product_stock']);
        header("Location: products.php");
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
        header("Location: products.php");
        exit();
    } elseif (isset($_POST['update_price']) && $role === 'administrator') {
        $new_price = floatval($_POST['new_price']);
        $product_index = intval($_POST['product_index']);
        $stmt = $conn->prepare("UPDATE products SET product_price = ? WHERE product_number = ?");
        $stmt->execute([$new_price, $product_index]);
        header("Location: products.php");
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
    } elseif (isset($_POST['remove_vendor']) && $role === 'administrator') {
        $vendor_name = $_POST['vendor_name'];
        $vendor_number = intval($_POST['vendor_number']);

        // Check if vendor exists
        $stmt = $conn->prepare("SELECT * FROM vendor WHERE vendor_name = :vendor_name AND vendor_number = :vendor_number");
        $stmt->bindParam(':vendor_name', $vendor_name);
        $stmt->bindParam(':vendor_number', $vendor_number);
        $stmt->execute();
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vendor) {
            // Check if the vendor has any products
            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE vendor_name = :vendor_name AND vendor_number = :vendor_number");
            $stmt->bindParam(':vendor_name', $vendor_name);
            $stmt->bindParam(':vendor_number', $vendor_number);
            $stmt->execute();
            $product_count = $stmt->fetchColumn();

            if ($product_count == 0) {
                $stmt = $conn->prepare("DELETE FROM vendor WHERE vendor_name = :vendor_name AND vendor_number = :vendor_number");
                $stmt->bindParam(':vendor_name', $vendor_name);
                $stmt->bindParam(':vendor_number', $vendor_number);
                $stmt->execute();
                $message = "Vendor removed successfully!";
            } else {
                $message = "Vendor cannot be removed because they have products listed.";
            }
        } else {
            $message = "Vendor does not exist.";
        }
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
        .cart-container {
            float: right;
            margin-right: 20px;
        }
        .total-price {
            font-weight: bold;
            margin-top: 10px;
        }
        .message {
            color: red;
            font-weight: bold;
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
        <div>
            <?php foreach ($products as $product): ?>
                <div>
                    <h3><?php echo $product['product_name']; ?></h3>
                    <img src="<?php echo $product['image_path']; ?>" alt="Product Image" style="max-width: 100px; max-height: 100px;">
                    <p>Price: R<?php echo $product['product_price']; ?></p>
                    <p>In stock: <?php echo $product['stock_quantity']; ?></p>
                    <?php if ($role === 'vendor' && $product['vendor_name'] === $username): ?>
                        <form action="products.php" method="post">
                            <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
                            <input type="number" name="new_stock" value="<?php echo $product['stock_quantity']; ?>" min="0">
                            <button type="submit" name="update_stock">Update Stock</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($role === 'administrator'): ?>
                        <form action="products.php" method="post">
                            <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
                            <input type="number" name="new_price" placeholder="New Price" step="0.01" min="0">
                            <button type="submit" name="update_price">Update Price</button>
                        </form>
                    <?php endif; ?>
                    <form action="products.php" method="post">
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