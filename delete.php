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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Edit/Delete Product</title>
    <link rel="stylesheet" href="./css//edit.css">
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
                <li><a href="notif.php"> <i class="fa-solid fa-envelope"></i>Notification</a></li>
                <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
            </ul>
        </div>
    </nav>
</header>

<section class="product-form-section">
    <h1>Delete Product</h1>
    <?php
    include "./php/connection.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $productID = $_POST['productID'];

        $query = "DELETE FROM products WHERE productID = '$productID'";

        if ($conn->query($query) == true) {
            $feedback = "Product deleted successfully.";
            $alertType = "success";
        } else {
            $feedback = "Error deleting product: " . $conn->error;
            $alertType = "error";
        }
    }

    // JavaScript to display alert
    if (isset($feedback)) {
        echo "<script>alert('$feedback');</script>";
    }
    ?>
    <form action="delete.php" method="post">
        <div class="form-group">
            <label for="productID">Product ID:</label>
            <input type="text" id="productID" name="productID" required>
        </div>

        <button type="submit">Delete Product</button>
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