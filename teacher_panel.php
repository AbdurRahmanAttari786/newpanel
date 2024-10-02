<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    // Insert assignment into the database
    $stmt = $mysqli->prepare("INSERT INTO assignments (title, description, due_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $due_date);
    $stmt->execute();
}

// Fetch assignments from the database
$stmt = $mysqli->prepare("SELECT title, description, due_date FROM assignments");
$stmt->execute();
$result = $stmt->get_result();
$assignments = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Panel</title>
    <link rel="stylesheet" href="teacher.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4; /* Light background for the page */
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #28a745; /* Green background */
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            color: white;
        }
        .sidebar h2 {
            color: white; /* Make the heading white */
            padding: 20px; /* Add some padding */
            text-align: center; /* Center the heading */
            margin: 0; /* Remove default margin */
        }
        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            color: white;
            display: block;
        }
        .sidebar a:hover {
            background-color: #218838; /* Darker green on hover */
        }
        .content {
            margin-left: 260px; /* Leave space for the sidebar */
            padding: 20px;
        }
        .dashboard, .create-assignment, .view-assignments {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: none; /* Initially hidden */
        }
        .show {
            display: block; /* Show the section when toggled */
        }
        h3 {
            margin: 0 0 10px; /* Space below the heading */
        }
        form {
            margin-top: 20px;
        }
        input[type="text"],
        input[type="datetime-local"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
    </style>
    <script>
        function showSection(section) {
            const dashboard = document.getElementById('dashboard');
            const createAssignment = document.getElementById('create-assignment');
            const viewAssignments = document.getElementById('view-assignments');

            // Hide all sections
            dashboard.classList.remove('show');
            createAssignment.classList.remove('show');
            viewAssignments.classList.remove('show');

            // Show the selected section
            if (section === 'dashboard') {
                dashboard.classList.add('show');
            } else if (section === 'create-assignment') {
                createAssignment.classList.add('show');
            } else if (section === 'view-assignments') {
                viewAssignments.classList.add('show');
            }
        }

        // Default to show dashboard on load
        window.onload = function() {
            showSection('dashboard');
        };
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Teacher Panel</h2>
        <a href="#" onclick="showSection('dashboard');">Dashboard</a>
        <a href="#" onclick="showSection('create-assignment');">Create Assignment</a>
        <a href="#" onclick="showSection('view-assignments');">View Assignments</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="content">
        <div id="dashboard" class="dashboard show">
            <h3>Welcome to the Dashboard</h3>
            <p>Select an option from the sidebar to get started.</p>
        </div>

        <div id="create-assignment" class="create-assignment">
            <h3>Create Assignment</h3>
            <form method="post">
                Title: <input type="text" name="title" required><br>
                Description: <textarea name="description" required></textarea><br>
                Due Date: <input type="datetime-local" name="due_date" required><br>
                <input type="submit" value="Create Assignment">
            </form>
        </div>

        <div id="view-assignments" class="view-assignments">
            <h3>View Assignments</h3>
            <table>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Due Date</th>
                </tr>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="3">No assignments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>
</html>
