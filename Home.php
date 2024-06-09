<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

// Get the username from the session variable
$username = $_SESSION['username'];

// Database connection parameters
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
    <title>Home - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Include your CSS file here -->
</head>
<body>
    <header>
        <h1>Welcome to Our Online Store, <?php echo htmlspecialchars($username); ?>!</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="orderhistory.php">Order History</a></li> <!-- Link to Order History -->
                <?php if ($role === 'administrator'): ?>
                    <li><a href="addnewvendor.php">Add New Vendor</a></li>
                    <li><a href="removevendor.php">Remove Vendor</a></li>
                <?php endif; ?>
                <li><a href="index.php">Logout</a></li> <!-- Logout link -->
            </ul>
        </nav>
    </header>
    <main>
        <p>Explore our range of products and add them to your cart.</p>
        <!-- Content for the home screen -->
    </main>
</body>
</html>