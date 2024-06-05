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
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css"> <!-- Include your CSS file here -->
</head>
<body>
    <div class="container">
        <h1>Welcome to Takealittle, <?php echo $username; ?>!</h1>
        <p>This is a secure area. You have successfully logged in.</p>
        <p><a href="index.php">Logout</a></p>
    </div>
</body>
</html>
