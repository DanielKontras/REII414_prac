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

    if ($role !== 'administrator') {
        echo "Access denied.";
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    <title>Remove Vendor - Online Store</title>
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
        <h1>Remove Vendor</h1>
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
        <h2>Remove Vendor</h2>
        <form action="removevendor.php" method="post">
            <input type="text" name="vendor_name" placeholder="Vendor Name" required>
            <input type="number" name="vendor_number" placeholder="Vendor Number" required>
            <button type="submit">Remove Vendor</button>
        </form>
    </main>
</body>
</html>