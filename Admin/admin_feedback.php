<?php
// FILE: admin_feedback.php

session_start();
require_once '../LoginPage/database_connection.php';
// Only allow access if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// ------------------------------------------------
// FETCH ALL FEEDBACK
// ------------------------------------------------
$feedback_sql = "SELECT 
                    uf.id, 
                    u.name AS user_name, 
                    uf.feedback_type, 
                    uf.feedback_text, 
                    uf.rating, 
                    uf.submission_date 
                 FROM user_feedback uf
                 JOIN users u ON uf.user_id = u.id
                 ORDER BY uf.submission_date DESC";

$feedback_list = [];
$result = $conn->query($feedback_sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $feedback_list[] = $row;
    }
}

// Function para mag-display ng stars
function display_stars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fa fa-star" style="color: gold;"></i>';
        } else {
            $stars .= '<i class="fa fa-star-o" style="color: #ccc;"></i>';
        }
    }
    return $stars;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Feedback</title>
    <link rel="stylesheet" href="css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* Basic styling kung walang admin.css */
        body { padding-top: 80px; }
        .admin-container { max-width: 1200px; margin: auto; padding-bottom: 40px; }
        .admin-title { text-align: center; color: #5c4033; margin-bottom: 25px; font-size: 2rem; }
        .admin-section { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .feedback-table { width: 100%; border-collapse: collapse; }
        .feedback-table th, .feedback-table td { padding: 12px; border: 1px solid #eee; text-align: left; vertical-align: top; }
        .feedback-table th { background: #f8f5f1; color: #A0522D; font-weight: 700; }
        .feedback-text { max-width: 400px; white-space: pre-wrap; word-wrap: break-word; font-size: 0.95rem; color: #444; }
        .rating-stars { min-width: 120px; }
        .feedback-type { font-weight: 600; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="admin-container">
        <div class="admin-title">Customer Feedback and Suggestions</div>

        <div class="admin-section">
            <?php if (empty($feedback_list)): ?>
                <p style="text-align: center; color: #A0522D;">No feedback received yet.</p>
            <?php else: ?>
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Rating</th>
                            <th>Feedback/Suggestion</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedback_list as $feedback): ?>
                            <tr>
                                <td><?= htmlspecialchars($feedback['id']) ?></td>
                                <td><?= htmlspecialchars($feedback['user_name']) ?></td>
                                <td class="feedback-type"><?= htmlspecialchars($feedback['feedback_type']) ?></td>
                                <td class="rating-stars">
                                    <?php 
                                        if ($feedback['rating'] > 0) {
                                            echo display_stars($feedback['rating']) . " (" . htmlspecialchars($feedback['rating']) . "/5)";
                                        } else {
                                            echo "N/A (Suggestion)";
                                        }
                                    ?>
                                </td>
                                <td class="feedback-text"><?= htmlspecialchars($feedback['feedback_text']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($feedback['submission_date']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>