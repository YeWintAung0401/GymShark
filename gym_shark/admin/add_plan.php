<?php

include('../connection.php'); 
session_start();


function redirect_with_status($status, $message) {
    $location = 'plans.php?status=' . $status . '&message=' . $message;
    header('Location: ' . $location);
    exit();
}

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {

    try {
        $planID = $_POST['planID'] ?? null;
        $planID = filter_var($planID, FILTER_VALIDATE_INT);
        
        if ($action === 'register' || $action === 'update') {
            $planName = trim($_POST['planName'] ?? '');
            $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
            $duration = trim($_POST['duration'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isPopular = isset($_POST['isPopular']) ? 1 : 0;

            if (empty($planName) || $price === false || $price < 0 || empty($duration) || empty($description)) {
                redirect_with_status('error', 'Invalid_Input');
            }
        }

        switch ($action) {
            case 'register':
                $stmt = $conn->prepare("INSERT INTO plan (planName, price, duration, description, isPopular) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sdssi", $planName, $price, $duration, $description, $isPopular);

                if ($stmt->execute()) {
                    redirect_with_status('success', 'Plan_Added');
                } else {
                    error_log("Database Error (Register Plan): " . $stmt->error);
                    redirect_with_status('error', 'Database_Error');
                }
                $stmt->close();
                break;

            case 'update':
                if (empty($planID) || $planID === false) {
                    redirect_with_status('error', 'Invalid_Input'); 
                }

                $stmt = $conn->prepare("UPDATE plan SET planName = ?, price = ?, duration = ?, description = ?, isPopular = ? WHERE planID = ?");
                $stmt->bind_param("sdssii", $planName, $price, $duration, $description, $isPopular, $planID);

                if ($stmt->execute()) {
                    redirect_with_status('success', 'Plan_Updated');
                } else {
                    error_log("Database Error (Update Plan): " . $stmt->error);
                    redirect_with_status('error', 'Database_Error');
                }
                $stmt->close();
                break;

            case 'delete':
                if (empty($planID) || $planID === false) {
                    redirect_with_status('error', 'Invalid_Input'); 
                }

                $stmt = $conn->prepare("DELETE FROM plan WHERE planID = ?");
                $stmt->bind_param("i", $planID);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        redirect_with_status('success', 'Plan_Deleted');
                    } else {
                        redirect_with_status('error', 'Plan_Not_Found');
                    }
                } else {
                    error_log("Database Error (Delete Plan): " . $stmt->error);
                    redirect_with_status('error', 'Database_Error');
                }
                $stmt->close();
                break;

            default:
                redirect_with_status('error', 'Unknown_Action');
                break;
        }

    } catch (Exception $e) {
        error_log("General Error in add_plan.php: " . $e->getMessage());
        redirect_with_status('error', 'Internal_Server_Error');
    }

} else {
    redirect_with_status('error', 'Access_Denied');
}

if (isset($conn)) {
    $conn->close();
}

?>