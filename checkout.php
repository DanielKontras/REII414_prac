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
$user_role = '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user's wallet balance
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retrieve user's role
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user_role_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_role = $user_role_result['role'];

    $balance = $result['balance'] ?? 0; // Ensure there is a fallback value

    $total_price = $_SESSION['total_price'] ?? 0;
    $tax_fee = $total_price * 0.20;
    $shipping_fee = ($total_price > 500) ? 0 : $total_price * 0.20;
    $total_with_shipping_and_tax = $total_price + $shipping_fee + $tax_fee;

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay'])) {
        if ($total_with_shipping_and_tax <= $balance) {
            // Update customer's wallet
            $balance -= $total_with_shipping_and_tax;
            $stmt = $conn->prepare("UPDATE wallets SET balance = :balance WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
            $stmt->bindParam(':balance', $balance);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Insert the order into the database
            $stmt = $conn->prepare("INSERT INTO orders (user_num, username, total_price, order_date, shipping_fee, tax_fee) VALUES ((SELECT user_num FROM users WHERE username = :username), :username, :total_price, NOW(), :shipping_fee, :tax_fee)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':total_price', $total_price);
            $stmt->bindParam(':shipping_fee', $shipping_fee);
            $stmt->bindParam(':tax_fee', $tax_fee);
            $stmt->execute();
            $order_id = $conn->lastInsertId();

            // Handle order items and vendor payments
            foreach ($_SESSION['cart'] as $product_index => $count) {
                $stmt = $conn->prepare("SELECT product_name, product_price, vendor_name FROM products WHERE product_number = ?");
                $stmt->execute([$product_index]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (:order_id, :product_name, :quantity, :price)");
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_name', $product['product_name']);
                $stmt->bindParam(':quantity', $count);
                $stmt->bindParam(':price', $product['product_price']);
                $stmt->execute();

                // Update vendor's wallet
                $total_product_price = $product['product_price'] * $count;
                $stmt = $conn->prepare("UPDATE wallets SET balance = balance + :total_product_price WHERE user_num = (SELECT user_num FROM users WHERE username = :vendor_name)");
                $stmt->bindParam(':total_product_price', $total_product_price);
                $stmt->bindParam(':vendor_name', $product['vendor_name']);
                $stmt->execute();
            }

            // Update all administrators' wallets with the tax fee
            $stmt = $conn->prepare("UPDATE wallets SET balance = balance + :tax_fee WHERE user_num IN (SELECT user_num FROM users WHERE role = 'administrator')");
            $stmt->bindParam(':tax_fee', $tax_fee);
            $stmt->execute();

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

    </style>
</head>
<body>
    <header>
        <h1>Checkout</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="orderhistory.php">Order History</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Order Summary</h2>
        <p>Your total charge is R<?php echo $total_price; ?>.</p>
        <p>Shipping Fee: R<?php echo $shipping_fee; ?>.</p>
        <p>Tax: R<?php echo $tax_fee; ?>.</p>
        <p>Total with Shipping and Tax: R<?php echo $total_with_shipping_and_tax; ?>.</p>
        <h3>Your Available Balance: R<?php echo $balance; ?></h3>
        <p style="color: blue;">If you purchase more than R500 worth of products, shipping will be free !!!</p>
        <?php if ($message): ?>
            <p class="message" style="color: <?php echo strpos($message, 'Payment successful') !== false ? 'green' : 'red'; ?>;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if ($user_role !== 'vendor' && $user_role !== 'administrator'): ?>
            <form method="post" action="checkout.php">
                <button type="submit" name="pay">Pay Amount</button>
            </form>
        <?php elseif ($user_role === 'vendor'): ?>
            <p style="color: red;">You are a vendor. To buy, you need to be a customer or create a customer account.</p>
        <?php elseif ($user_role === 'administrator'): ?>
            <p style="color: red;">You are an administrator. To buy, you need to be a customer or create a customer account.</p>
        <?php endif; ?>
        <p><button type="button" onclick="location.href='products.php'">Back to Products</button></p>
    </main>
</body>
</html>