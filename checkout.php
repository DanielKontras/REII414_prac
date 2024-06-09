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
$order_id = null;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user's wallet balance
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result !== false && isset($result['balance'])) {
        $balance = $result['balance'];
    } else {
        $balance = 0;
    }

    $total_price = $_SESSION['total_price'] ?? 0;

    // Calculate shipping fee
    if ($total_price > 500) {
        $shipping_fee = 0;
    } else {
        $shipping_fee = $total_price * 0.20;
    }

    $total_with_shipping = $total_price + $shipping_fee;

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay'])) {
        if ($total_with_shipping <= $balance) {
            $balance -= $total_with_shipping;
            $stmt = $conn->prepare("UPDATE wallets SET balance = :balance WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
            $stmt->bindParam(':balance', $balance);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Insert order into orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_num, username, total_price, order_date, shipping_fee) VALUES ((SELECT user_num FROM users WHERE username = :username), :username, :total_price, NOW(), :shipping_fee)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':total_price', $total_price);
            $stmt->bindParam(':shipping_fee', $shipping_fee);
            $stmt->execute();

            // Get the order ID
            $order_id = $conn->lastInsertId();

            // Insert order items into order_items table
            foreach ($_SESSION['cart'] as $product_index => $count) {
                $stmt = $conn->prepare("SELECT product_name, product_price FROM products WHERE product_number = ?");
                $stmt->execute([$product_index]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (:order_id, :product_name, :quantity, :price)");
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_name', $product['product_name']);
                $stmt->bindParam(':quantity', $count);
                $stmt->bindParam(':price', $product['product_price']);
                $stmt->execute();
            }

            // Clear cart and adjust session values
            $_SESSION['cart'] = [];
            $_SESSION['total_price'] = 0;
            $_SESSION['balance'] = $balance;

            $message = 'Payment successful! Your order number is ' . $order_id;
        } else {
            $message = 'Insufficient funds.';
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <header>
        <h1>Checkout</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Order Summary</h2>
        <p>Your total charge is R<?php echo $total_price; ?>.</p>
        <p>Shipping Fee: R<?php echo $shipping_fee; ?>.</p>
        <p>Total with Shipping: R<?php echo $total_with_shipping; ?>.</p>
        <h3>Your Available Balance: R<?php echo $balance; ?></h3>
        <p style="color: blue;">If you purchase more than R500 worth of products, shipping will be free !!!</p>
        <?php if ($message): ?>
            <p style="color: <?php echo strpos($message, 'Payment successful') !== false ? 'green' : 'red'; ?>;"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="post" action="checkout.php">
            <button type="submit" name="pay">Pay Amount</button>
        </form>
        <p><button type="button" onclick="location.href='products.php'">Back to Products</button></p>
    </main>
</body>
</html>