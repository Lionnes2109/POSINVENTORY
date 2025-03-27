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

// Backup logic
if (isset($_POST['backup'])) {
    $backupResult = backupDatabase();
}

// Restore logic
if (isset($_POST['restore'])) {
    if ($_FILES['restore_file']['error'] == UPLOAD_ERR_OK) {
        $restoreResult = restoreDatabase($_FILES['restore_file']['tmp_name']);
    } else {
        echo "Error uploading file. Please try again.";
    }
}

function backupDatabase()
{
    // Database configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $database_name = "posinventory";

    // Create a connection
    $conn = new mysqli($host, $username, $password, $database_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get all table names
    $tables = array();
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tables[] = $row['Tables_in_' . $database_name];
        }
    }

    // Create SQL dump
    $dump = "-- Database backup for $database_name\n\n";
    foreach ($tables as $table) {
        $dump .= "DROP TABLE IF EXISTS $table;\n";
        $show_create_table = $conn->query("SHOW CREATE TABLE $table")->fetch_assoc();
        $dump .= $show_create_table['Create Table'] . ";\n\n";
        $select = $conn->query("SELECT * FROM $table");
        while ($data = $select->fetch_assoc()) {
            $columns = implode(", ", array_keys($data));
            $values = implode("', '", array_values($data));
            $dump .= "INSERT INTO $table ($columns) VALUES ('$values');\n";
        }
        $dump .= "\n";
    }

    // Save the SQL dump to a file
    $backup_file_name = $database_name . '_backup_' . time() . '.sql';
    file_put_contents($backup_file_name, $dump);

    // Download the SQL backup file to the browser
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($backup_file_name));
    ob_clean();
    flush();
    readfile($backup_file_name);

    // Remove the backup file
    unlink($backup_file_name);

    exit();
    return "Backup successful!";
}

function restoreDatabase($sqlDumpFile)
{
    // Database configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $database_name = "posinventory";

    // Create a connection
    $conn = new mysqli($host, $username, $password, $database_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Read the SQL dump file
    $sqlScript = file_get_contents($sqlDumpFile);

    // Execute the SQL script
    $conn->multi_query($sqlScript);

    // Close the connection
    $conn->close();

    return "Database restored successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="./css/backup.css">
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

<div class="success-message" id="successMessage">
        <span class="close-btn" onclick="closeSuccessMessage()">&times;</span>
    </div>
    <form method="post" action="backup.php" enctype="multipart/form-data">
        <input type="submit" name="backup" value="Backup Database">
    </form>

    <form method="post" action="backup.php" enctype="multipart/form-data">
        <input type="file" name="restore_file" accept=".sql">
        <input type="submit" name="restore" value="Restore Database">
    </form>

    <script>
        function toggleMenu() {
            const navigation = document.querySelector('.navigation');
            navigation.classList.toggle('show');
        }

        function closeSuccessMessage() {
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'none';
        }

        // Function to show success message
        function showSuccessMessage(message) {
            const successMessage = document.getElementById('successMessage');
            successMessage.innerHTML = `<span class="close-btn" onclick="closeSuccessMessage()">&times;</span>${message}`;
            successMessage.style.display = 'block';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000); // 3 seconds
        }

        // Check if there is a backup or restore result and show the message
        <?php
            if (isset($backupResult)) {
                echo "showSuccessMessage('$backupResult');";
            }

            if (isset($restoreResult)) {
                echo "showSuccessMessage('$restoreResult');";
            }
        ?>
    </script>
</body>
</html>