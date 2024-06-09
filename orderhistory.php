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

    // Retrieve the user's user_num
    $stmt = $conn->prepare("SELECT user_num FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_num = $user['user_num'];

    // Retrieve the orders for the user
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_num = :user_num ORDER BY order_date DESC");
    $stmt->bindParam(':user_num', $user_num);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
        <h1>Your Order History</h1>
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
        <?php if (empty($orders)): ?>
            <p>You have no orders.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <h2>Order #<?php echo $order['order_id']; ?> - <?php echo $order['order_date']; ?></h2>
                <p>Total Price: R<?php echo $order['total_price']; ?></p>
                <p>Shipping Fee: R<?php echo $order['shipping_fee']; ?></p>
                <p>Total with Shipping: R<?php echo $order['total_price'] + $order['shipping_fee']; ?></p>
                <h3>Items:</h3>
                <ul>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
                    $stmt->bindParam(':order_id', $order['order_id']);
                    $stmt->execute();
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php foreach ($items as $item): ?>
                        <li><?php echo $item['product_name']; ?> - Quantity: <?php echo $item['quantity']; ?> - Price: R<?php echo $item['price']; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>