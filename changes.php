<?php
// Include your database connection
include "./php/connection.php";

// Check if the user is logged in, otherwise redirect to index.html
session_start();
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

// Fetch details from the changes table
$query = "SELECT id, product_id, oldStock, stock, action, made_at FROM changes";
$result = $conn->query($query);

// Check if there are rows in the result
if ($result->num_rows > 0) {
    $changes = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $changes = [];
}

// Close the database connection
$conn->close();
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager</title>
    <link rel="stylesheet" href="./css/changes.css">
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

<!-- Display changes details -->
<section class="changes-section">
    <h1>Changes</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product ID</th>
                <th>Old Stock</th>
                <th>Stock</th>
                <th>Action</th>
                <th>Made At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($changes as $change): ?>
                <tr>
                    <td><?php echo $change['id']; ?></td>
                    <td><?php echo $change['product_id']; ?></td>
                    <td><?php echo $change['oldStock']; ?></td>
                    <td><?php echo $change['stock']; ?></td>
                    <td><?php echo $change['action']; ?></td>
                    <td><?php echo $change['made_at']; ?></td>
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