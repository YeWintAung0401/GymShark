<?php
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$action = $_GET['action'] ?? '';

if ($action == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM customer WHERE customerID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Customer deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete customer.";
    }
    header("Location: view-customer.php");
    exit();
}

if ($action == 'update' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['customerID'];
    $name = mysqli_real_escape_string($conn, $_POST['customerName']);
    $email = mysqli_real_escape_string($conn, $_POST['customerEmail']);
    $phone = mysqli_real_escape_string($conn, $_POST['customerPhone']);
    $address = mysqli_real_escape_string($conn, $_POST['customerAddress']);
    $gender = $_POST['gender'];

    $profilePath = $_POST['existingProfile']; 
    $targetDir = "../customer_profiles/";
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!empty($_FILES['profile_picture']['name'])) {
        $file = $_FILES['profile_picture'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            if ($file['size'] < 5 * 1024 * 1024) { 
                
                $fileName = "profile_" . $id . "_" . time() . "." . $fileExtension;
                $targetFilePath = $targetDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                    $profilePath = $targetDir . $fileName;

                    $oldFile = "../" . $_POST['existingProfile'];
                    if (!empty($_POST['existingProfile']) && file_exists($oldFile) && strpos($oldFile, 'default') === false) {
                        unlink($oldFile);
                    }
                } else {
                    $_SESSION['error_message'] = "System Error: Failed to move uploaded file.";
                }
            } else {
                $_SESSION['error_message'] = "Security Alert: File size exceeds 5MB limit.";
            }
        } else {
            $_SESSION['error_message'] = "Protocol Error: Invalid file format.";
        }
    }

    $stmt = $conn->prepare("UPDATE customer SET customerName=?, customerEmail=?, customerPhone=?, customerAddress=?, gender=?, customerProfile=? WHERE customerID=?");
    $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $gender, $profilePath, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "CRITICAL UPDATE: Customer data synced successfully.";
    } else {
        $_SESSION['error_message'] = "DATABASE ERROR: Update protocol failed.";
    }
    
    header("Location: view-customer.php");
    exit();
}


?>