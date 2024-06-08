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

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the amount from the form
    $amount = $_POST['amount'];

    // Database connection
    $servername = "localhost";
    $dbname = "takealittle";  // Replace with your actual database name
    $dbusername = "root";     // Typically 'root' for XAMPP
    $password = "";           // Default is no password in XAMPP

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Update wallet balance
        $stmt = $conn->prepare("UPDATE wallets SET balance = balance + :amount WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        // Redirect back to wallet page
        header("Location: wallet.php");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
