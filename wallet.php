<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

// Get the username from the session variable
$username = $_SESSION['username'];

// Database connection
$servername = "localhost";
$dbname = "takealittle";  // Replace with your actual database name
$dbusername = "root";     // Typically 'root' for XAMPP
$password = "";           // Default is no password in XAMPP

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user's wallet balance
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if result is valid
    if ($result !== false && isset($result['balance'])) {
        $balance = $result['balance'];
    } else {
        // Handle case where no balance is found
        $balance = 0; // Set default balance to 0
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
    <title>Wallet - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Include your CSS file here -->
</head>
<body>

    <header>
        <h1>Welcome to Your Wallet, <?php echo htmlspecialchars($username); ?>!</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="cart.php">Cart</a></li> <!-- Link to Cart page -->
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="index.php">Logout</a></li> <!-- Logout link -->
            </ul>
        </nav>
    </header>

    <main>
        <h2>Your Current Balance: <?php echo $balance; ?></h2>
        <form action="deposit_action.php" method="post">
            <label for="amount">Enter Amount to Deposit:</label>
            <input type="number" id="amount" name="amount" min="0" step="any">
            <input type="submit" value="Deposit">
        </form>
    </main>

</body>
</html>
