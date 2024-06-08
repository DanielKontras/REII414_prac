<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect to the login page
    header("Location: index.php");
    exit();
}

// Get the username from the session
$username = $_SESSION['username'];

// Database connection details
$servername = "localhost";
$dbname = "takealittle";
$dbusername = "root";
$password = "";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch products from the database
    $stmt = $conn->prepare("SELECT * FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Initialize the cart array
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];


// Function to add product to cart and decrement stock quantity
function addToCart($product_index, $product_stock) {
    global $conn;
    if ($product_stock > 1) { // Check if product is in stock
        $stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_number = ?");
        $stmt->execute([$product_index]);
        
        // Store original stock quantity in session if not already stored
        if (!isset($_SESSION['original_stock'][$product_index])) {
            $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_number = ?");
            $stmt->execute([$product_index]);
            $original_stock = $stmt->fetchColumn();
            $_SESSION['original_stock'][$product_index] = $original_stock;
        }
        
        $_SESSION['cart'][] = $product_index;
    } else {
        echo "Product is out of stock.";
    }
}




// Function to clear cart and restore original stock quantity
function clearCart() {
    global $conn, $cart;
    
    // Restore stock quantity of each product to its original value
    if(isset($_SESSION['original_stock'])) {
        foreach ($_SESSION['original_stock'] as $index => $quantity) {
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = ?+1 WHERE product_number = ?");
            $stmt->execute([$quantity, $index]);
        }
    }

    // Clear the cart and original_stock session variables
    unset($_SESSION['cart']);
    unset($_SESSION['original_stock']);
}




// Check if the form is submitted and perform actions accordingly
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
        $product_index = $_POST['product_index'];
        $product_stock = $_POST['product_stock'];
        addToCart($product_index, $product_stock); // Pass product stock quantity
    } elseif (isset($_POST['clear_cart'])) {
        clearCart();
    }
}

// Function to display cart contents and calculate total price
function displayCart() {
    global $conn, $cart;
    $total_price = 0;
    echo "<ul>";
    foreach ($cart as $index) {
        $stmt = $conn->prepare("SELECT product_name, product_price FROM products WHERE product_number = ?");
        $stmt->execute([$index]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<li>{$product['product_name']} - {$product['product_price']}</li>";
        $total_price += $product['product_price'];
    }
    echo "</ul>";
    echo "<p>Total: $total_price</p>";
    return $total_price; // Return the total price
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Online Store</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* Add CSS styles for positioning the cart and clear cart button */
        .cart-container {
            float: right;
            margin-right: 20px; /* Adjust margin as needed */
        }
                /* Style for the total price */
                .total-price {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Browse Products, <?php echo $username; ?></h1>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="index.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Our Products</h2>
        <div class="cart-container">
            <!-- Display cart contents -->
            <h3>Your Cart</h3>
            <?php
                if (!empty($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $product_index) {
                        // Fetch product details from the database based on the product index
                        $stmt = $conn->prepare("SELECT product_name, product_price FROM products WHERE product_number = ?");
                        $stmt->execute([$product_index]);
                        $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                        // Display product name and price
                        echo "<p>{$product['product_name']} - {$product['product_price']}</p>";
                    }
                } else {
                    echo "<p>Your cart is empty.</p>";
                }
            ?>
             <p class="total-price">Total: <?php echo $total_price; ?></p>
            <!-- Clear cart button -->
            <form action="products.php" method="post">
                <button type="submit" name="clear_cart">Clear Cart</button>
            </form>
        </div>
        <div>
            <!-- Display product listings -->
            <?php foreach ($products as $product): ?>
                <div>
                    <h3><?php echo $product['product_name']; ?></h3>
                    <img src="<?php echo $product['image_path']; ?>" alt="Product Image" style="max-width: 100px; max-height: 100px;"> <!-- Add image -->
                    <p>Price: R<?php echo $product['product_price']; ?></p>
                    <p>In stock: <?php echo $product['stock_quantity']; ?></p>
                    <form action="products.php" method="post">
    <input type="hidden" name="product_index" value="<?php echo $product['product_number']; ?>">
    <input type="hidden" name="product_stock" value="<?php echo $product['stock_quantity']; ?>"> <!-- Add this line -->
    <button type="submit" name="add_to_cart">Add to Cart</button>
    <input type="hidden" name="action" value="add_to_cart">
</form>

                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
