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

$message = '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user role
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $role = $user['role'];

    if ($role !== 'administrator') {
        header("Location: home.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
        $product_name = $_POST['product_name'];
        $product_price = floatval($_POST['product_price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $vendor_name = $_POST['vendor_name'];
        $vendor_number = intval($_POST['vendor_number']);
        $image_path = $_POST['image_path'];

        // Check if vendor exists in vendor table
        $stmt = $conn->prepare("SELECT * FROM vendor WHERE vendor_name = :vendor_name AND vendor_number = :vendor_number");
        $stmt->bindParam(':vendor_name', $vendor_name);
        $stmt->bindParam(':vendor_number', $vendor_number);
        $stmt->execute();
        $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vendor) {
            $stmt = $conn->prepare("INSERT INTO products (product_name, product_price, stock_quantity, vendor_name, vendor_number, image_path) VALUES (:product_name, :product_price, :stock_quantity, :vendor_name, :vendor_number, :image_path)");
            $stmt->bindParam(':product_name', $product_name);
            $stmt->bindParam(':product_price', $product_price);
            $stmt->bindParam(':stock_quantity', $stock_quantity);
            $stmt->bindParam(':vendor_name', $vendor_name);
            $stmt->bindParam(':vendor_number', $vendor_number);
            $stmt->bindParam(':image_path', $image_path);
            $stmt->execute();
            $message = "Product added successfully!";
        } else {
            $message = "Vendor does not exist.";
        }
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .message {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1>Add New Product</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="orderhistory.php">Order History</a></li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <div>
            <h2>Add New Product</h2>
            <form action="addnewproduct.php" method="post">
                <input type="text" name="product_name" placeholder="Product Name" required>
                <input type="number" name="product_price" placeholder="Product Price" step="0.01" min="0" required>
                <input type="number" name="stock_quantity" placeholder="Stock Quantity" min="0" required>
                <input type="text" name="vendor_name" placeholder="Vendor Name" required>
                <input type="number" name="vendor_number" placeholder="Vendor Number" required>
                <input type="text" name="image_path" placeholder="Image Path" required>
                <button type="submit" name="add_product">Add Product</button>
            </form>
        </div>
    </main>
</body>
</html>