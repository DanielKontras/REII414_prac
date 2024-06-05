<?php      
    include('connection.php');  

    $username = $_POST['user'];  
    $password = $_POST['pass'];  

    // Sanitize user input to prevent SQL injection
    $username = mysqli_real_escape_string($con, $username);  
    $password = mysqli_real_escape_string($con, $password);  

    // Query to check if the username and password exist in the database
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";  
    $result = mysqli_query($con, $sql);  

    // Check the number of rows returned by the query
    if(mysqli_num_rows($result) == 1){  
        // Authentication successful
        // Start a session and store the username in the session variable
        session_start();
        $_SESSION['username'] = $username;

        // Redirect the user to another page (e.g., dashboard.php)
        header("Location: Home.php");
        exit(); // Make sure to exit after redirection to prevent further script execution
    }  
    else{  
        // Authentication failed
        // Redirect back to the login page with an error message as a query parameter
        header("Location: index.php?login_error=1");
        exit();
    }     
?>
