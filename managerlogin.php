<?php
// Include the database connection
include './php/connection.php';

// Initialize a variable to check if login is unsuccessful
$loginFailed = false;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the hashed password from the database based on the username
    $query = "SELECT * FROM employees WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];

        // Verify the entered password with the stored hashed password
        if (password_verify($password, $hashedPassword) && $row['positionID'] == 2) {
            // Start a session and store the username
            session_start();
            $_SESSION['username'] = $username;

            // Redirect to inventory.php if login is successful
            header('Location: inventory.php');
            exit();
        }
    }

    // Set login failed flag to true
    $loginFailed = true;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manager</title>
    <link rel="stylesheet" href="./css/loginadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Botika</div>
            <div class="burger-menu" onclick="toggleMenu()">☰</div>
            <div class="navigation">
                <ul>
                    <li><a href="index.html"><i class="fa-solid fa-house"></i>Home</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <section class="login-section">
        <h1>Login - Manager</h1>
        <!-- Display alert if login fails -->
        <?php if ($loginFailed): ?>
            <script>
                alert("Incorrect username or password. Please try again.");
            </script>
        <?php endif; ?>
        <form class="login-form" action="managerlogin.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
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
