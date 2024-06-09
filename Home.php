<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

// Get the username from the session variable
$username = $_SESSION['username'];

// Database connection parameters
$servername = "localhost";
$dbname = "takealittle";
$dbusername = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve user role
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $role = $user['role'];
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css"> <!-- Include your CSS file here -->
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
        main p {
            font-size: 18px;
            color: #000;
        }
        .why-shop {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .why-shop ul {
            list-style: none;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to Takealittle, <?php echo htmlspecialchars($username); ?>!</h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="wallet.php">Wallet</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="orderhistory.php">Order History</a></li> <!-- Link to Order History -->
                <?php if ($role === 'administrator'): ?>
                    <li><a href="addnewvendor.php">Add New Vendor</a></li>
                    <li><a href="removevendor.php">Remove Vendor</a></li>
                   
                <?php endif; ?>
                <li><a href="index.php">Logout</a></li> <!-- Logout link -->
            </ul>
        </nav>
    </header>
    <main>
        <p>Feel free to explore our range of products and add them to your cart.</p>
        <div class="why-shop">
            <h2>Why Shop With Us</h2>
            <p>Here are some reasons why you should choose Takealittle for your online shopping:</p>
            <ul>
                <li>Wide selection of high-quality imaginary products</li>
                <li>We ONLY use imaginary money</li>
                <li>Fast and reliable imaginary delivery</li>
                <li>100% customer satisfaction guarantee</li>
            </ul>
        </div>
        <div class="general-content">
            <h2>About Takealittle</h2>
            <p>Takealittle is a leading online store offering a diverse range of jams and 3D printed things to customers worldwide. With a focus on quality, convenience, and customer satisfaction, we strive to make your shopping experience enjoyable and hassle-free.</p>
            <h2>Our Mission</h2>
            <p>Our mission is to provide customers with access to a wide selection of products from unknown brands, delivered with exceptional service and value. We are committed to building long-term relationships with our customers and suppliers based on integrity, reliability, and mutual respect.</p>
        </div>
    </main>
</body>
</html>


