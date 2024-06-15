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
        main {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        main h2 {
            margin-bottom: 10px;
        }
        main p, main ul {
            text-align: left;
            margin-bottom: 15px;
        }
        main ul {
            padding-left: 20px;
        }
    </style>
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
                <div>
                    <p>Total Price: R<?php echo $order['total_price']; ?></p>
                    <p>Shipping Fee: R<?php echo $order['shipping_fee']; ?></p>
                    <p>Tax Fee: R<?php echo $order['tax_fee']; ?></p>
                    <p>Total with Shipping and Tax: R<?php echo $order['total_price'] + $order['shipping_fee'] + $order['tax_fee']; ?></p>
                    <p><strong>Your order will be shipped within 24 hours.</strong></p>
                </div>
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
