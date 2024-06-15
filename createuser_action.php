<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $dbname = "takealittle";  
    $dbusername = "root";     
    $password = "";           

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
            // Prepare and bind for users table
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
            $stmt->bindParam(':username', $newuser);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);

            // Set parameters for users table
            $newpass = $_POST['newpass'];
            $hashed_password = $newpass; // Directly taking the password without hashing
            $role = $_POST['role']; // Adding role parameter
            $stmt->execute();

            // Get the user_num of the inserted user
            $user_num = $conn->lastInsertId();

            // Prepare and bind for wallets table
            $stmt = $conn->prepare("INSERT INTO wallets (user_num, balance) VALUES (:user_num, 0)");
            $stmt->bindParam(':user_num', $user_num);
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
