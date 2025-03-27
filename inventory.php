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

    // Handle removing product
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeProduct'])) {
        $productID = $_POST['productID'];

        // Prepare and execute the SQL statement to remove the product
        $removeProductQuery = "DELETE FROM products WHERE productID = ?";
        $stmt = $conn->prepare($removeProductQuery);
        $stmt->bind_param('i', $productID);

        if ($stmt->execute()) {
            // Product removed successfully, you may want to add a success message or redirect
            // For example: header('Location: inventory.php');
        } else {
            // Handle error, you may want to add an error message
            // For example: echo "Error: " . $conn->error;
        }

        // Close the statement
        $stmt->close();
    }

    // Fetch products from the database
    $query = "SELECT * FROM products";
    $result = $conn->query($query);

    // Check if there are rows in the result
    if ($result->num_rows > 0) {
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $rows = [];
    }

    // Close the database connection
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="./css/inventory.css">
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
<section class="inventory-section">
    <h1>Inventory</h1>
    <a href="changes.php"> Changes.... </a>
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Image</th>
                <th>Brand Name</th>
                <th>Generic Name</th>
                <th>Prescription</th>
                <th>Stocks</th>
                <th>Price</th>
                <th>Manufacture Date</th>
                <th>Expiry Date</th>
                <th>Added At</th>
                <th>Delete</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $row['productID']; ?></td>
                    <td><img src="productuploads/<?php echo $row['productImg']; ?>" alt="Product Image" style="width: 50px; height: 50px;"></td>
                    <td><?php echo $row['brandName']; ?></td>
                    <td><?php echo $row['genericName']; ?></td>
                    <td><?php echo $row['prescription']; ?></td>
                    <td><?php echo $row['stocks']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $row['manufactureDate']; ?></td>
                    <td><?php echo $row['expiryDate']; ?></td>
                    <td><?php echo $row['added_at']; ?></td>
                    <td>
                        <a href="confirmdelete_product.php?productID=<?php echo $row['productID']; ?>" class="confirm-delete">Remove Product</a>
                    </td>
                    <td>
                <a href="edit.php?productID=<?php echo $row['productID']; ?>" class="update-stock">Update Stock</a>
            </td>
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