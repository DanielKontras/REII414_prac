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

        // Get the username of the user to be deleted
        $stmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $username = $_POST['username'];
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Check if the user is a vendor
            $stmt = $conn->prepare("SELECT * FROM vendor WHERE vendor_name = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

            // If the user is a vendor, delete their products from the products table
            if ($vendor) {
                $stmt = $conn->prepare("DELETE FROM products WHERE vendor_name = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
            }

            // Delete the user from the users table
            $stmt = $conn->prepare("DELETE FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // Delete the user from the vendors table if they are a vendor
            if ($vendor) {
                $stmt = $conn->prepare("DELETE FROM vendor WHERE vendor_name = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
            }

            $_SESSION['message'] = "Your account has been deleted successfully. <a href='index.php'>Return to login page</a>";
            $_SESSION['message_type'] = "success";
            header("Location: deleteuser.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid username.";
            $_SESSION['message_type'] = "error";
            header("Location: deleteuser.php");
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: deleteuser.php");
        exit();
    }

    $conn = null;
} 
?>


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body class="login-background">
    <div class="frm">
        <h1>Delete Account</h1>
        <?php
        if (isset($_SESSION['message'])) {
            $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
            echo "<p class='$message_type'>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']); // Clear the message after displaying it
            unset($_SESSION['message_type']); // Clear the message type after displaying it
        }
        ?>
        <form name="deleteForm" action="deleteuser.php" onsubmit="return validateDeleteForm()" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" />
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" />
            </div>
            <div class="form-group">
                <input type="submit" id="btn" value="Delete Account" />
            </div>
        </form>
    </div>
    <script>
        function validateDeleteForm() {
            var username = document.deleteForm.username.value;
            var password = document.deleteForm.password.value;
            if (username.length === 0 || password.length === 0) {
                alert("Username and password are required.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
