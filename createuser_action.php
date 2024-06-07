<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $dbname = "takealittle";  // Replace with your actual database name
    $dbusername = "root";     // Typically 'root' for XAMPP
    $password = "";           // Default is no password in XAMPP

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
        // Set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->bindParam(':username', $newuser);
        $newuser = $_POST['newuser'];
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $_SESSION['message'] = "Username already exists. Please choose another username.";
            header("Location: createuser.php");
            exit();
        } else {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $newuser);
            $stmt->bindParam(':password', $newpass);

            // Set parameters and execute
            $newpass = $_POST['newpass'];
            $stmt->execute();

            $_SESSION['message'] = "New account created successfully. <a href='index.php'>Return to login page</a>";
            header("Location: createuser.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        header("Location: createuser.php");
        exit();
    }

    $conn = null;
} else {
    echo "Invalid request method";
}
?>