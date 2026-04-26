<?php
include('./connection.php');
session_start();

if (!isset($_SESSION['customerID'])) {
    $_SESSION['error_message'] = "ACCESS DENIED: Please login to submit feedback.";
    header("Location: ./login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerID = $_SESSION['customerID'];
    
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $submitDate = date('Y-m-d H:i:s');

    if ($rating < 1 || $rating > 5) {
        $_SESSION['error_message'] = "VALIDATION ERROR: Please select a star rating.";
        header("Location: index.php#contact");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO feedback (customerID, fullName, email, rating, feedback, submissionDate) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $customerID, $fullName, $email, $rating, $feedback, $submitDate);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "TRANSMISSION SUCCESSFUL: Thank you for your feedback.";
    } else {
        $_SESSION['error_message'] = "SYSTEM ERROR: Failed to log message to database.";
    }

    $stmt->close();
    header("Location: index.php#contact");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>