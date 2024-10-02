// Add this below the assignment creation form
<h3>View Submissions</h3>
<table>
    <tr>
        <th>Assignment Title</th>
        <th>Student</th>
        <th>File</th>
        <th>Submitted At</th>
    </tr>
    <?php
    $submissions = $mysqli->query("SELECT submissions.*, assignments.title, users.username 
                                    FROM submissions 
                                    JOIN assignments ON submissions.assignment_id = assignments.id 
                                    JOIN users ON submissions.user_id = users.id")->fetch_all(MYSQLI_ASSOC);

    foreach ($submissions as $submission): ?>
        <tr>
            <td><?= htmlspecialchars($submission['title']) ?></td>
            <td><?= htmlspecialchars($submission['username']) ?></td>
            <td><a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank">View File</a></td>
            <td><?= htmlspecialchars($submission['submitted_at']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
