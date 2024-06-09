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
        header("Location: index.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_vendor'])) {
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
    <title>Add New Vendor - Online Store</title>
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
        <h1>Add New Vendor</h1>
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
        <h2>Add New Vendor</h2>
        <?php if (isset($message) && $message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="addnewvendor.php" method="post">
            <input type="text" name="vendor_name" placeholder="Vendor Name" required>
            <input type="number" name="vendor_number" placeholder="Vendor Number" required>
            <button type="submit" name="add_vendor">Add Vendor</button>
        </form>
    </main>
</body>
</html>