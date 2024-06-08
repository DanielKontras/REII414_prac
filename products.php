<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username']; // Store the username from the session
$servername = "localhost";
$dbname = "takealittle";  // Replace with your actual database name
$dbusername = "root";              // Typically 'root' for XAMPP
$password = "";                  // Default is no password in XAMPP

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch product details including the image path and stock quantity
    $stmt = $conn->prepare("SELECT product_name, product_price, vendor_name, image_path, stock_quantity FROM products");
    $stmt->execute();

    // Set the resulting array to associative
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
        <h1>Browse Products, <?php echo $username; ?></h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Our Products</h2>
        <div>
            <?php foreach ($products as $product): ?>
                <div>
                    <h3><?php echo $product['product_name']; ?></h3>
                    <img src="<?php echo $product['image_path']; ?>" alt="Image of <?php echo $product['product_name']; ?>" style="width:100px; height:auto;">
                    <p>Price: R<?php echo $product['product_price']; ?></p>
                    <p>Vendor: <?php echo $product['vendor_name']; ?></p>
                    <p>Stock: <?php echo $product['stock_quantity']; ?></p>
                    <button>Add to Cart</button>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
