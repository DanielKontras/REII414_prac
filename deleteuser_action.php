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

        // Get the user_num of the user to be deleted
        $stmt = $conn->prepare("SELECT user_num FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $username = $_POST['username'];
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $user_num = $result['user_num'];

            // Delete the corresponding wallet entry from the wallets table
            $stmt = $conn->prepare("DELETE FROM wallets WHERE user_num = :user_num");
            $stmt->bindParam(':user_num', $user_num);
            $stmt->execute();

            // Delete the user from the users table
            $stmt = $conn->prepare("DELETE FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $_SESSION['message'] = "Your account has been deleted successfully. <a href='index.php'>Return to login page</a>";
            header("Location: deleteuser.php");
            exit();
        } else {
            $_SESSION['message'] = "Invalid username.";
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
