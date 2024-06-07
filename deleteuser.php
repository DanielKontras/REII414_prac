<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="login-background">
    <div class="frm">
        <h1>Delete Account</h1>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']); // Clear the message after displaying it
        }
        ?>
        <form name="deleteForm" action="deleteuser_action.php" onsubmit="return validateDeleteForm()" method="POST">
            <div class="form-group">
                <label for="username">UserName:</label>
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
            var user = document.deleteForm.username.value;
            var pass = document.deleteForm.password.value;
            if (user.length == "" && pass.length == "") {
                alert("User Name and Password fields are empty");
                return false;
            } else {
                if (user.length == "") {
                    alert("User Name is empty");
                    return false;
                }
                if (pass.length == "") {
                    alert("Password field is empty");
                    return false;
                }
            }
        }
    </script>
</body>
</html>