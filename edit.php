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

// Initialize variables to store feedback
$feedback = '';
$alertType = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $productID = $_POST['productID'];
    $newStock = $_POST['newStock'];

    // Call the stored procedure with the two parameters
    $procedureCall = "CALL updatestock('$productID', '$newStock')";

    if ($conn->query($procedureCall)) {
        // Update successful
        $feedback = 'Stock updated successfully.';
        $alertType = 'success';
    } else {
        // Update failed
        $feedback = 'Error updating stock: ' . $conn->error;
        $alertType = 'error';
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product Stock</title>
    <link rel="stylesheet" href="./css/edit.css">
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
                <li class="dropdown">
                    <a href="#" class="dropbtn"><i class="fa-solid fa-pen-to-square"></i>Product</a>
                    <div class="dropdown-content">
                        <a href="add.php">Add</a>
                        <a href="edit.php">Edit</a>
                        <a href="delete.php">Delete</a>
                    </div>
                </li>

                <li><a href="sales.php"><i class="fa-solid fa-dollar-sign"></i>Sales</a></li>
                <li>
            <a href="notif.php">
                <i class="fa-solid fa-envelope"></i>
                Notification
                <?php if (!empty($expiryRows) || !empty($zeroStocksRows)): ?>
                    <span class="notification-icon">!</span>
                <?php endif; ?>
            </a>
        </li>
                <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
            </ul>
        </div>
    </nav>
</header>

<section class="product-form-section">
    <h1>Edit Product Stock</h1>
    <?php if ($feedback): ?>
        <script>alert('<?php echo $feedback; ?>');</script>
    <?php endif; ?>
    <form action="edit.php" method="post">
        <?php
        // Check if productID is present in the URL
        if (isset($_GET['productID'])) {
            $productID = $_GET['productID'];
        ?>
        <div class="form-group">
            <label for="productID">Product ID:</label>
            <input type="text" id="productID" name="productID" value="<?php echo $productID; ?>" readonly>
        </div>
        <?php } else { ?>
        <div class="form-group">
            <label for="productID">Product ID:</label>
            <input type="text" id="productID" name="productID" required>
        </div>
        <?php } ?>

        <div class="form-group">
            <label for="newStock">New Stock:</label>
            <input type="number" id="newStock" name="newStock" required>
        </div>

        <button type="submit">Update Stock</button>
    </form>
</section>
<script>
    function toggleMenu() {
        const navigation = document.querySelector('.navigation');
        navigation.classList.toggle('show');
    }
</script>

</body>
</html>