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

// Fetch details from the deletedemployees table
$query = "SELECT employeeID, firstName, lastName, positionID, username, deleted_at FROM deletedemployees";
$result = $conn->query($query);

// Check if there are rows in the result
if ($result->num_rows > 0) {
    $deletedEmployees = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $deletedEmployees = [];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="./css/r_employees.css">
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

    <!-- Display deleted employees details -->
    <section class="deleted-employees-section">
        <h1>Deleted Employees</h1>
        <table>
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Position ID</th>
                    <th>Username</th>
                    <th>Deleted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deletedEmployees as $employee): ?>
                    <tr>
                        <td><?php echo $employee['employeeID']; ?></td>
                        <td><?php echo $employee['firstName']; ?></td>
                        <td><?php echo $employee['lastName']; ?></td>
                        <td><?php echo $employee['positionID']; ?></td>
                        <td><?php echo $employee['username']; ?></td>
                        <td><?php echo $employee['deleted_at']; ?></td>
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