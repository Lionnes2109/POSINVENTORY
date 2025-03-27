<?php
// Start the session
session_start();

// Check if the user is logged in, otherwise redirect to index.html
if (!isset($_SESSION['username'])) {
    header('Location: index.html');
    exit();
}

// Logout logic
if (isset($_GET['logout'])) {
    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header('Location: index.html');
    exit();
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Include your database connection
include "./php/connection.php";

// Fetch products with expiry date greater than or equal to the current date
$expiryQuery = "SELECT * FROM expiredproducts";
$expiryResult = $conn->query($expiryQuery);

// Check for errors
if (!$expiryResult) {
    die("Query failed: " . $conn->error);
}

// Check if there are rows in the result
if ($expiryResult->num_rows > 0) {
    $expiryRows = $expiryResult->fetch_all(MYSQLI_ASSOC);
} else {
    $expiryRows = [];
}

// Fetch products with zero stocks
$zeroStocksQuery = "SELECT * FROM products WHERE stocks = 0";
$zeroStocksResult = $conn->query($zeroStocksQuery);

// Check for errors
if (!$zeroStocksResult) {
    die("Query failed: " . $conn->error);
}

// Check if there are rows in the result
if ($zeroStocksResult->num_rows > 0) {
    $zeroStocksRows = $zeroStocksResult->fetch_all(MYSQLI_ASSOC);
} else {
    $zeroStocksRows = [];
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="./css/notif.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<header>
        <nav>
        <div class="logo">Botika</div>
        <div class="burger-menu" onclick="toggleMenu()">☰</div>
        <div class="navigation">
            <ul>
                <li><a href="inventory.php"><i class="fa-solid fa-box"></i>Inventory</a></li>
                <li>
                    <a href="add.php" class="dropbtn"><i class="fa-solid fa-pen-to-square"></i>Add Product</a>
                   
                </li>

                <li><a href="sales.php"><i class="fa-solid fa-dollar-sign"></i>Sales</a></li>
                <li><a href="notif.php"> <i class="fa-solid fa-envelope"></i>Notification</a></li>
                <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
            </ul>
        </div>
    </nav>
</header>

<section class="expiry-section">
    <h1>Expired Products</h1>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Brand Name</th>
                <th>Generic Name</th>
                <th>Expiry Date</th>
                <th>Action</th> <!-- New column for Remove button -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expiryRows as $expiryRow): ?>
                <tr>
                    <td><?php echo $expiryRow['productID']; ?></td>
                    <td><?php echo $expiryRow['brandName']; ?></td>
                    <td><?php echo $expiryRow['genericName']; ?></td>
                    <td><?php echo $expiryRow['expiryDate']; ?></td>
                    <td>
                        <form method="post" action="remove_expired_product.php">
                            <input type="hidden" name="productID" value="<?php echo $expiryRow['productID']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to remove this product?')">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="zerostocks-section">
    <h1>Products with Zero Stocks</h1>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Brand Name</th>
                <th>Generic Name</th>
                <th>Stocks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zeroStocksRows as $zeroStocksRow): ?>
                <tr>
                    <td><?php echo $zeroStocksRow['productID']; ?></td>
                    <td><?php echo $zeroStocksRow['brandName']; ?></td>
                    <td><?php echo $zeroStocksRow['genericName']; ?></td>
                    <td><?php echo $zeroStocksRow['stocks']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<script>
        function toggleMenu() {
            const navigation = document.querySelector('.navigation');
            navigation.classList.toggle('show');
        }
    </script>
</body>
</html>