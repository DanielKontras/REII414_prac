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
                <li><a href="cart.php">Cart</a></li> <!-- Link to Cart page -->
                <li><a href="contact.php">Contact Us</a></li>
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