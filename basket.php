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

// Handle remove from basket logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeFromBasket'])) {
    // Iterate through the submitted productIDs and delete from the basket
    if(isset($_POST['productID'])) {
        foreach ($_POST['productID'] as $productID) {
            $deleteQuery = "DELETE FROM basket WHERE productID = '$productID' AND username = '$username'";
            $conn->query($deleteQuery);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed'])) {
    // Check if there are selected products
    if(isset($_POST['productID']) && !empty($_POST['productID'])) {
        // Insert into the checkout table
        foreach ($_POST['quantity'] as $key => $quantity) {
            $productID = $_POST['productID'][$key];
            $insertQuery = "CALL addcheckout('$username', '$productID', '$quantity')";
            $conn->query($insertQuery);
        }

        // Clear the basket table for the user
        $clearBasketQuery = "DELETE FROM basket WHERE username = '$username'";
        $conn->query($clearBasketQuery);

        // Redirect to checkout.php
        header('Location: checkout.php');
        exit();
    } else {
        // Handle the case where no products are selected
        // Display an alert message
        echo '<script>alert("No products selected!");</script>';
    }
}

// Fetch products from the basket for the logged-in user
$basketQuery = "SELECT * from basket_details
                WHERE basket_details.username = '$username'";
$basketResult = $conn->query($basketQuery);

// Check if there are rows in the result
if ($basketResult->num_rows > 0) {
    $basketRows = $basketResult->fetch_all(MYSQLI_ASSOC);
} else {
    $basketRows = [];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="./css/basket.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">Botika</div>
            <div class="burger-menu" onclick="toggleMenu()">☰</div>
            <div class="navigation">
                <ul>
                    <li><a href="pharmacist.php"><i class="fa-solid fa-shop"></i>Products</a></li>
                    <li><a href="#"><i class="fa-solid fa-basket-shopping" style="color: #0be407;"></i>Basket</a></li>
                    <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
                </ul>
            </div>
        </nav>
    </header>
    <section class="basket-section">
        <h1>Your Basket</h1>
        <form method="post">
        <div class="basket-buttons">
                <button type="submit" name="removeFromBasket">Remove Selected</button>
                <button type="submit" name="proceed">Proceed</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Brand Name</th>
                        <th>Generic Name</th>
                        <th>Stocks</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($basketRows as $basketRow): ?>
                        <tr>
                            <td><?php echo $basketRow['brandName']; ?></td>
                            <td><?php echo $basketRow['genericName']; ?></td>
                            <td><?php echo $basketRow['stocks']; ?></td>
                            <td><?php echo $basketRow['price']; ?></td>
                            <td><input type="text" name="quantity[]" value="1"></td>
                            <td>
                                <input type="checkbox" name="productID[]" value="<?php echo $basketRow['productID']; ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
          
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
