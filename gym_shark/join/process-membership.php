<?php
include('../connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['customerID'])) {
    $customerID = $_SESSION['customerID'];
    $planID = intval($_POST['planID']); // Ensure it is an integer

    // 1. DUPLICATE PREVENTION: Check if user already has an active membership
    $checkActive = $conn->prepare("SELECT membershipID FROM membership WHERE customerID = ? AND status = 'Active' AND endDate >= CURDATE()");
    $checkActive->bind_param("i", $customerID);
    $checkActive->execute();
    $activeResult = $checkActive->get_result();

    if ($activeResult->num_rows > 0) {
        $_SESSION['error_message'] = "PROTOCOL DENIED: You already have an active membership.";
        header("Location: ../edit-profile.php");
        exit();
    }

    // 2. Get Plan Duration (Corrected table name to 'plans' to match your schema)
    $stmt = $conn->prepare("SELECT duration FROM plan WHERE planID = ?");
    $stmt->bind_param("i", $planID);
    $stmt->execute();
    $planData = $stmt->get_result()->fetch_assoc();

    // Safety check if plan actually exists
    if (!$planData) {
        $_SESSION['error_message'] = "ERROR: Plan ID not found in system.";
        header("Location: ../index.php#membership");
        exit();
    }

    $startDate = date('Y-m-d');
    $expiryDate = (stripos($planData['duration'], 'Year') !== false) 
                  ? date('Y-m-d', strtotime('+1 year')) 
                  : date('Y-m-d', strtotime('+1 month'));

    $qrToken = 'GYM-' . bin2hex(random_bytes(8)) . '-' . $customerID;

    // 3. Insert into Membership table
    $sql = "INSERT INTO membership (customerID, planID, startDate, endDate, status, qr_token) 
    VALUES (?, ?, ?, ?, 'Active', ?)";
    
    $insert = $conn->prepare($sql);
    $insert->bind_param("iisss", $customerID, $planID, $startDate, $expiryDate, $qrToken);

    if ($insert->execute()) {
        $_SESSION['success_message'] = "MEMBERSHIP SECURED: System access granted.";
        header("Location: ../edit-profile.php");
    } else {
        // Log the actual error for debugging
        error_log("Membership Insert Failed: " . $insert->error);
        $_SESSION['error_message'] = "CRITICAL ERROR: Failed to write to database.";
        header("Location: ../index.php#membership");
    }
    exit();
} else {
    // Redirect if they try to access this file without POST or being logged in
    header("Location: ../login.php");
    exit();
}