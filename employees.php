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

// Handle removing employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['removeEmployee'])) {
    $employeeID = $_POST['employeeID'];

    // Prepare and execute the SQL statement to remove the employee
    $removeEmployeeQuery = "DELETE FROM employees WHERE employeeID = ?";
    $stmt = $conn->prepare($removeEmployeeQuery);
    $stmt->bind_param('i', $employeeID);

    if ($stmt->execute()) {
        // Employee removed successfully, you may want to add a success message or redirect
        // For example: header('Location: employees.php');
    } else {
        // Handle error, you may want to add an error message
        // For example: echo "Error: " . $conn->error;
    }

    // Close the statement
    $stmt->close();
}

// Fetch employees from the database
$employeesQuery = "SELECT * FROM employees";
$employeesResult = $conn->query($employeesQuery);

// Check for errors
if (!$employeesResult) {
    die("Query failed: " . $conn->error);
}

// Check if there are rows in the result
if ($employeesResult->num_rows > 0) {
    $employeesRows = $employeesResult->fetch_all(MYSQLI_ASSOC);
} else {
    $employeesRows = [];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees</title>
    <link rel="stylesheet" href="./css/employees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<header>
        <nav>
            <div class="logo">Botika</div>
            <div class="burger-menu" onclick="toggleMenu()">☰</div>
            <div class="navigation">
                <ul>
                    <li><a href="admin.php"><i class="fa-solid fa-house"></i>Home</a></li>
                    <li> <a href="employees.php"><i class="fa-solid fa-user"></i>Employees</a></li>
                    <li><a href="backup.php"><i class="fa-solid fa-database"></i>Backup/Restore Database</a></li>
                    <li><a href="register.php"><i class="fa-solid fa-user-pen"></i>Register</a></li>
                    <li><a href="?logout=1"><i class="fa-solid fa-right-from-bracket" style="color: #FF0000"></i>Logout <?php echo $username; ?></a></li>
                </ul>
            </div>
        </nav>
    </header>

    <section class="employees-section">
        <h1>Employee List</h1>
        <a href="r_employees.php"> Removed Employees....</a>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Position ID</th>
                    <th>Username</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employeesRows as $employeeRow): ?>
                    <tr>
                        <td><?php echo $employeeRow['employeeID']; ?></td>
                        <td><?php echo $employeeRow['firstName']; ?></td>
                        <td><?php echo $employeeRow['lastName']; ?></td>
                        <td><?php echo $employeeRow['positionID']; ?></td>
                        <td><?php echo $employeeRow['username']; ?></td>
                        <td><?php echo $employeeRow['created_at']; ?></td>
                        <td>
                <a href="confirmdelete.php?employeeID=<?php echo $employeeRow['employeeID']; ?>" class="confirm-delete">Remove Employee</a>
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
