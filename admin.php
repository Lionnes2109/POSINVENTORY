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
    <title>Admin
    </title>
    <link rel="stylesheet" href="./css/admin.css">
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

            <p class="message">  Welcome Admin <?php echo $username; ?> </p>
    <script>
        function toggleMenu() {
            const navigation = document.querySelector('.navigation');
            navigation.classList.toggle('show');
        }
    </script>
</body>
</html>