<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username']; // Store the username from the session

// Initialize the cart items array
$cart_items = array();

// Check if there are items in the cart
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_items = $_SESSION['cart'];
}

// Connect to the database
$servername = "localhost";
$dbname = "takealittle";  // Replace with your actual database name
$dbusername = "root";     // Typically 'root' for XAMPP
$password = "";           // Default is no password in XAMPP

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    <title>Cart - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
        <h1>Your Cart, <?php echo $username; ?></h1>
        <nav>
            <ul>
                <!-- Navigation links -->
            </ul>
        </nav>
    </header>
    <main>
        <p>This is your shopping cart. You can view and manage the items you have added.</p>
        <div>
            <?php foreach ($cart_items as $item): ?>
                <div>
                    <?php
                    // Fetch product details based on product number
                    $product_number = $item['product_number'];
                    $stmt = $conn->prepare("SELECT product_name, product_price FROM products WHERE product_number = :product_number");
                    $stmt->bindParam(':product_number', $product_number);
                    $stmt->execute();
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <h3><?php echo $product['product_name']; ?></h3>
                    <p>Price: $<?php echo $product['product_price']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
