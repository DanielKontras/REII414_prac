<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

// Get the username from the session variable
$username = $_SESSION['username'];

// Database connection
$servername = "localhost";
$dbname = "takealittle";
$dbusername = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user's wallet balance
    $stmt = $conn->prepare("SELECT balance FROM wallets WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if result is valid
    if ($result !== false && isset($result['balance'])) {
        $balance = $result['balance'];
    } else {
        // Handle case where no balance is found
        $balance = 0; // Set default balance to 0
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['deposit'])) {
            $deposit_amount = $_POST['amount'];
            $new_balance = $balance + $deposit_amount;
            $stmt = $conn->prepare("UPDATE wallets SET balance = :new_balance WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
            $stmt->bindParam(':new_balance', $new_balance);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $balance = $new_balance;
        } elseif (isset($_POST['withdraw'])) {
            $withdraw_amount = $_POST['withdraw_amount'];
            if ($withdraw_amount > $balance) {
                echo "<script>alert('Withdrawal amount exceeds your current balance.');</script>";
            } else {
                $new_balance = $balance - $withdraw_amount;
                $stmt = $conn->prepare("UPDATE wallets SET balance = :new_balance WHERE user_num = (SELECT user_num FROM users WHERE username = :username)");
                $stmt->bindParam(':new_balance', $new_balance);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $balance = $new_balance;
            }
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - Online Store</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f06, #f90);
            color: #000;
            margin: 0;
            padding: 0;
        }
        header, main {
            width: 80%;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            justify-content: center;
            margin: 0;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        nav ul li {
            display: inline;
            margin: 0 10px;
        }
        nav ul li a {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 5px;
        }
        nav ul li a:hover {
            background-color: #fff;
            color: #000;
        }
        main {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        form {
            margin-top: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="number"], input[type="submit"] {
            padding: 8px;
            margin: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #3CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <header>
        <h1>Welcome to Your Wallet, <?php echo htmlspecialchars($username); ?>!</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="orderhistory.php">Order History</a></li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Your Current Balance: R<?php echo $balance; ?></h2>
        <form action="" method="post">
            <label for="amount">Enter Amount to Deposit:</label>
            <input type="number" id="amount" name="amount" min="0" step="any">
            <input type="submit" name="deposit" value="Deposit">
        </form>
        <form action="" method="post">
            <label for="withdraw_amount">Enter Amount to Withdraw:</label>
            <input type="number" id="withdraw_amount" name="withdraw_amount" min="0" step="any">
            <input type="submit" name="withdraw" value="Withdraw">
        </form>
    </main>

</body>
</html>
