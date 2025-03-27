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

    // Pagination settings
    $itemsPerPage = 10; // Number of sales records per page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $itemsPerPage;

    // Get all available years for dropdown
    $yearsQuery = "SELECT DISTINCT YEAR(ordered_at) as year FROM orders ORDER BY year DESC";
    $yearsResult = $conn->query($yearsQuery);
    $availableYears = [];
    
    if ($yearsResult->num_rows > 0) {
        while($row = $yearsResult->fetch_assoc()) {
            $availableYears[] = $row['year'];
        }
    }

    // Set default year to the latest year if available
    $defaultYear = !empty($availableYears) ? $availableYears[0] : date('Y');
    $selectedYear = isset($_GET['year']) ? $_GET['year'] : $defaultYear;

    // Fetch sales data based on the selected year
    $condition = "YEAR(orders.ordered_at) = '$selectedYear'";
    $countQuery = "SELECT COUNT(*) as total FROM orders WHERE $condition";
    $countResult = $conn->query($countQuery);
    $totalRecords = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $itemsPerPage);

    // Fetch sales data
    $salesQuery = "SELECT orders.orderID, products.productID, products.brandName, products.genericName, 
                   products.price, orders.quantity, orders.ordered_at 
                   FROM orders
                   JOIN products ON orders.productID = products.productID
                   WHERE $condition
                   ORDER BY orders.ordered_at DESC
                   LIMIT $offset, $itemsPerPage";

    $salesResult = $conn->query($salesQuery);

    // Check for errors
    if (!$salesResult) {
        die("Query failed: " . $conn->error);
    }

    // Check if there are rows in the result
    if ($salesResult->num_rows > 0) {
        $salesRows = $salesResult->fetch_all(MYSQLI_ASSOC);
    } else {
        $salesRows = [];
    }

    // Calculate total sales for the selected year
    $totalSalesQuery = "SELECT SUM(products.price * orders.quantity) as total 
                        FROM orders
                        JOIN products ON orders.productID = products.productID
                        WHERE $condition";
    $totalSalesResult = $conn->query($totalSalesQuery);
    $totalSales = $totalSalesResult->fetch_assoc()['total'] ?: 0;

    // Close the database connection
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="./css/sales.css">
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 5px;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: black;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        .year-filter {
            margin-bottom: 20px;
        }
        .year-filter select {
            padding: 8px;
            margin-right: 10px;
        }
        .year-filter button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .year-filter button:hover {
            background-color: #45a049;
        }
    </style>
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
                <li><a href="notif.php"><i class="fa-solid fa-envelope"></i>Notification</a></li>
                <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
            </ul>
        </div>
    </nav>
</header>

<!-- Display year filter and sales details -->
<section class="sales-section">
    <div class="sales-header">
        <h1>Sales Report</h1>
        <div class="year-filter">
            <form action="sales.php" method="get">
                <label for="year">Select Year:</label>
                <select id="year" name="year">
                    <?php foreach ($availableYears as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($selectedYear == $year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>
    </div>
    
    <?php if (empty($salesRows)): ?>
        <p>No sales data available for the selected year.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Brand Name</th>
                    <th>Generic Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Ordered At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesRows as $salesRow): ?>
                    <tr>
                        <td><?php echo $salesRow['orderID']; ?></td>
                        <td><?php echo $salesRow['brandName']; ?></td>
                        <td><?php echo $salesRow['genericName']; ?></td>
                        <td><?php echo $salesRow['price']; ?></td>
                        <td><?php echo $salesRow['quantity']; ?></td>
                        <td><?php echo $salesRow['price'] * $salesRow['quantity']; ?></td>
                        <td><?php echo $salesRow['ordered_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="?year=<?php echo $selectedYear; ?>&page=<?php echo ($page - 1); ?>">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<a href="?year=' . $selectedYear . '&page=1">1</a>';
                    if ($startPage > 2) {
                        echo '<span>...</span>';
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo '<a href="?year=' . $selectedYear . '&page=' . $i . '" class="' . $activeClass . '">' . $i . '</a>';
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span>...</span>';
                    }
                    echo '<a href="?year=' . $selectedYear . '&page=' . $totalPages . '">' . $totalPages . '</a>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?year=<?php echo $selectedYear; ?>&page=<?php echo ($page + 1); ?>">Next &raquo;</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Fixed bar for total sales -->
    <div class="total-bar">
        <p>Total Sales for <?php echo $selectedYear; ?>: <?php echo $totalSales; ?></p>
    </div>
</section>

<script>
    function toggleMenu() {
        const navigation = document.querySelector('.navigation');
        navigation.classList.toggle('show');
    }
</script>
</body>
</html>
