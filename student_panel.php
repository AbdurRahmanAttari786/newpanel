<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $user_id = $_SESSION['user_id'];
    $target_file = "uploads/" . basename($_FILES["file"]["name"]);
    $file_type = pathinfo($target_file, PATHINFO_EXTENSION);

    // Allowed file types
    $allowed_types = ['jpg',  'png', 'pdf', 'doc', 'docx'];

    // Check file type
    if (in_array(strtolower($file_type), $allowed_types)) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $stmt = $mysqli->prepare("INSERT INTO submissions (assignment_id, user_id, file_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $assignment_id, $user_id, $target_file);
            $stmt->execute();
        } else {
            $error_message = "File upload failed.";
        }
    } else {
        $error_message = "Invalid file type. Only JPG, PNG, PDF, and Word files are allowed.";
    }
}

$assignments = $mysqli->query("SELECT * FROM assignments")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Panel</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateFileType(input) {
            const allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            const fileName = input.value;
            const fileExtension = fileName.split('.').pop().toLowerCase();

            if (!allowedTypes.includes(fileExtension)) {
                alert("Invalid file type. Only JPG, PNG, PDF, and Word files are allowed.");
                input.value = ''; // Clear the file input
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Student Panel</h2>
    <?php if ($error_message): ?>
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>
    <h3>Available Assignments</h3>
    <table>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Due Date</th>
            <th>Submit</th>
        </tr>
        <?php foreach ($assignments as $assignment): ?>
            <tr>
                <td><?= htmlspecialchars($assignment['title']) ?></td>
                <td><?= htmlspecialchars($assignment['description']) ?></td>
                <td><?= htmlspecialchars($assignment['due_date']) ?></td>
                <td>
                    <form method="post" enctype="multipart/form-data" onsubmit="return validateFileType(this.file);">
                        <input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
                        Upload File: <input type="file" name="file" required onchange="validateFileType(this);">
                        <input type="submit" value="Submit">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <a href="logout.php">Logout</a>
</body>
</html>
