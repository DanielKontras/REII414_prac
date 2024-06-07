<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $dbname = "takealittle";  // Replace with your actual database name
    $dbusername = "root";     // Typically 'root' for XAMPP
    $password = "";           // Default is no password in XAMPP

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the username and password match
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        $username = $_POST['username'];
        $password = $_POST['password'];
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Delete the user
            $stmt = $conn->prepare("DELETE FROM users WHERE username = :username AND password = :password");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            $_SESSION['message'] = "Your account has been deleted successfully. <a href='index.php'>Return to login page</a>";
            header("Location: deleteuser.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid username or password.";
            header("Location: deleteuser.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        header("Location: deleteuser.php");
        exit();
    }

    $conn = null;
} else {
    echo "Invalid request method";
}
?>