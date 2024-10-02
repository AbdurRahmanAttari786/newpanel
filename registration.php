<?php
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $teacher_id = isset($_POST['teacher_id']) ? trim($_POST['teacher_id']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';

    // Input validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($email)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } elseif ($role === 'teacher' && (empty($teacher_id) || empty($department))) {
        $error = "Teacher ID and Department are required for teachers.";
    } else {
        // Check for existing username or email
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM userp WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the insert statement
            $stmt = $mysqli->prepare("INSERT INTO userp (username, password, email, role, teacher_id, department) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssss", $username, $hashed_password, $email, $role, $teacher_id, $department);
                if ($stmt->execute()) {
                    // Redirect to login page
                    header('Location: login.php');
                    exit; // Ensure no further code is executed after redirect
                } else {
                    $error = "Failed to register. Please try again.";
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $mysqli->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
            margin-left: -30%;
            margin-top: -10%;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        label {
            display: block;
            margin: 5px 0;
        }

        .account-link {
            text-align: center;
            margin-top: 10px;
            color: #007BFF; /* Lighter color for link */
        }

        .account-link a {
            color: #007BFF; /* Light blue color for link */
            text-decoration: none; /* Remove underline */
        }

        .account-link a:hover {
            text-decoration: underline; /* Underline on hover */
        }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .teacher-fields {
            display: none; /* Hidden by default */
        }
    </style>
    <script>
        function showModal(message) {
            const modal = document.getElementById("errorModal");
            document.getElementById("modalMessage").innerText = message;
            modal.style.display = "block";
        }

        function closeModal() {
            document.getElementById("errorModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("errorModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };

        function toggleTeacherFields() {
            const roleSelect = document.getElementById("role");
            const teacherFields = document.getElementById("teacherFields");
            teacherFields.style.display = roleSelect.value === "teacher" ? "block" : "none";
        }
    </script>
</head>
<body>
    <h2>Register</h2>
    <?php if ($error): ?>
        <script>
            alert(<?= json_encode($error) ?>);
        </script>
    <?php endif; ?>

    <form method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required placeholder="Enter your username"><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required placeholder="Enter your email address"><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required placeholder="Enter your password"><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required placeholder="Confirm your password"><br>

        <label for="role">Role:</label>
        <select name="role" id="role" onchange="toggleTeacherFields()">
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select><br>

        <div id="teacherFields" class="teacher-fields">
            <label for="teacher_id">Teacher ID:</label>
            <input type="text" name="teacher_id" id="teacher_id" placeholder="Enter your teacher ID"><br>

            <label for="department">Department:</label>
            <input type="text" name="department" id="department" placeholder="Enter your department"><br>
        </div>

        <input type="submit" value="Register">
        
        <div class="account-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>

    <!-- The Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modalMessage"></p>
        </div>
    </div>
</body>
</html>
