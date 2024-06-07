<?php      
    include('connection.php');  

    $username = $_POST['user'];  
    $password = $_POST['pass'];  

    // Sanitize user input to prevent SQL injection
    $username = mysqli_real_escape_string($con, $username);  
    $password = mysqli_real_escape_string($con, $password);  

    // Start a session to store error messages
    session_start();

    // Query to check if the username exists in the database
    $user_check_query = "SELECT * FROM users WHERE username = '$username'";  
    $user_check_result = mysqli_query($con, $user_check_query);

    if(mysqli_num_rows($user_check_result) > 0){  
        // Username exists, now check the password
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";  
        $result = mysqli_query($con, $sql);  

        if(mysqli_num_rows($result) == 1){  
            // Authentication successful
            $_SESSION['username'] = $username;
            header("Location: home.php");
            exit();
        } else {  
            // Incorrect password
            $_SESSION['error'] = "Incorrect password.";
            header("Location: index.php");
            exit();
        }
    } else {  
        // Username does not exist
        $_SESSION['error'] = "User does not exist.";
        header("Location: index.php");
        exit();
    }     
?>
