<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="login-background">
    <div class="frm">
        <h1>Create Account</h1>
        <?php
        if (isset($_SESSION['message'])) {
            echo "<p>" . $_SESSION['message'] . "</p>";
            unset($_SESSION['message']); // Clear the message after displaying it
        }
        ?>
        <form name="createForm" action="createuser_action.php" onsubmit="return validateCreateForm()" method="POST">
            <div class="form-group">
                <label for="newuser">Username:</label>
                <input type="text" id="newuser" name="newuser" />
            </div>
            <div class="form-group">
                <label for="newpass">Password:</label>
                <input type="password" id="newpass" name="newpass" />
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="customer">Customer</option>
                    <option value="vendor">Vendor</option>
                    <option value="administrator">Administrator</option>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" id="btn" value="Create Account" />
            </div>
        </form>
    </div>
    <script>
        function validateCreateForm() {
            var user = document.createForm.newuser.value;
            var pass = document.createForm.newpass.value;
            if (user.length == "" && pass.length == "") {
                alert("Username and Password fields are empty");
                return false;
            } else {
                if (user.length == "") {
                    alert("Username is empty");
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
