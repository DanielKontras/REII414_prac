<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Login System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="login-background">
    <div class="frm">
        <h1>Login</h1>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo "<p style='color:red;'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']); // Clear the error message after displaying it
        }
        ?>
        <form name="f1" action="authentication.php" onsubmit="return validation()" method="POST">
            <div class="form-group">
                <label for="user">UserName:</label>
                <input type="text" id="user" name="user" />
            </div>
            <div class="form-group">
                <label for="pass">Password:</label>
                <input type="password" id="pass" name="pass" />
            </div>
            <div class="form-group">
                <input type="submit" id="btn" value="Login" />
            </div>
        </form>
        <p>Don't have an account? <a href="createuser.php">Create Account</a></p>
        <p>Delete my account? <a href="deleteuser.php">Delete Account</a></p> <!-- Link to delete account page -->
    </div>
    <script>
        function validation() {
            var id = document.f1.user.value;
            var ps = document.f1.pass.value;
            if (id.length == "" && ps.length == "") {
                alert("User Name and Password fields are empty");
                return false;
            } else {
                if (id.length == "") {
                    alert("User Name is empty");
                    return false;
                }
                if (ps.length == "") {
                    alert("Password field is empty");
                    return false;
                }
            }
        }
    </script>
</body>
</html>