<?php
// FILE: ../Feedback/save_feedback.php
// Ito ang iisang file na nag-ha-handle ng lahat ng feedback submissions.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Tiyakin na ang path ay tama
require_once '../LoginPage/database_connection.php'; 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) {
        echo json_encode(["success" => false, "message" => "User not logged in."]);
        exit();
    }

    // Kuhanin ang data mula sa form
    // Ang 'feedback_type' ay magiging: 'One-time Prompt', 'Suggestions Page', o 'Order Specific'
    $rating = intval($_POST['rating'] ?? 0);
    $feedback_text = trim($_POST['feedback_text'] ?? '');
    $feedback_type = trim($_POST['feedback_type'] ?? 'General'); 
    $submission_date = date('Y-m-d H:i:s');
    
    // Simpleng validation
    // Kung ang feedback ay HINDI 'Suggestions Page' (at hindi rin 'Order Specific' - kung kasama 'yun), kailangang may rating.
    if (($rating < 1 || $rating > 5) && $feedback_type !== 'Suggestions Page') {
        // NOTE: Ang logic na ito ay nakadepende sa kung ang 'Order Specific' ay nagpapadala rin ng rating o hindi.
        // Assuming ang Suggestions Page lang ang walang rating:
        // Kung walang rating, tatanggapin lang kung 'Suggestions Page' ang type.
        if ($rating === 0 && $feedback_type !== 'Suggestions Page') {
             // Maaari mong baguhin ang error message para mas akma sa context.
             // echo json_encode(["success" => false, "message" => "Invalid rating value."]);
             // exit();
        }
    }
    
    // Tiyakin na may text ang suggestion
    if (empty($feedback_text) && $feedback_type === 'Suggestions Page') {
         echo json_encode(["success" => false, "message" => "Suggestion text cannot be empty."]);
        exit();
    }

    $conn->begin_transaction(); // Simulan ang transaction

    try {
        // 1. I-INSERT ang feedback sa `user_feedback` table
        $sql_insert = "INSERT INTO user_feedback (user_id, feedback_type, feedback_text, rating, submission_date) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        
        if (!$stmt_insert) {
            throw new Exception("SQL Prepare (Insert) failed: " . $conn->error);
        }
        
        $stmt_insert->bind_param("issis", $user_id, $feedback_type, $feedback_text, $rating, $submission_date);
        
        if (!$stmt_insert->execute()) {
            throw new Exception("Feedback INSERT failed: " . $stmt_insert->error);
        }
        $stmt_insert->close();
        
        // 2. I-UPDATE ang `users` table KUNG ang feedback ay 'One-time Prompt'
        if ($feedback_type === 'One-time Prompt') {
            $sql_update = "UPDATE users SET has_given_onetime_feedback = 1 WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            
            if (!$stmt_update) {
                throw new Exception("SQL Prepare (Update) failed: " . $conn->error);
            }
            
            $stmt_update->bind_param("i", $user_id);
            
            if (!$stmt_update->execute()) {
                throw new Exception("User UPDATE failed: " . $stmt_update->error);
            }
            $stmt_update->close();
        }
        
        // Kung walang error, i-commit ang transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully.']);
        
    } catch (Exception $e) {
        // Kung may error, i-rollback ang transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, "message" => "Invalid request method."]);
}
?>