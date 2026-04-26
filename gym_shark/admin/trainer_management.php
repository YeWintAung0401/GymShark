<?php
include('../connection.php'); 
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$target_dir = "trainer_profiles/";
$action = $_GET['action'] ?? $_POST['action'] ?? null;
$trainerID = $_GET['id'] ?? $_POST['trainerID'] ?? null;

if (!$action) {
    die("Action not specified.");
}

function redirectWithError($message) {
    $_SESSION['error_message'] = "<span class='text-red-700'>Error: </span>" . $message;
    header("Location: trainers-list.php");
    exit();
}

if ($action === 'fetch' && $trainerID) {
    header('Content-Type: application/json');
    try {
        $stmt = $conn->prepare("SELECT 
                                    trainerID, trainerName, trainerEmail, trainerPhone, 
                                    specialization, salary , status, trainerProfile
                                FROM trainer WHERE trainerID = ?");
        $stmt->bind_param("i", $trainerID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($trainer = $result->fetch_assoc()) {
            echo json_encode(['success' => true, 'trainer' => $trainer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Trainer not found.']);
        }
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'update' && $trainerID) {
    
    $trainerName = mysqli_real_escape_string($conn, trim($_POST['trainerName'] ?? ''));
    $trainerEmail = mysqli_real_escape_string($conn, trim($_POST['trainerEmail'] ?? ''));
    $trainerPhone = mysqli_real_escape_string($conn, trim($_POST['trainerPhone'] ?? ''));
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization'] ?? '');
    $salary = (float)($_POST['salary'] ?? 0.00); 
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Active');
    
    $current_profile_path = mysqli_real_escape_string($conn, trim($_POST['trainerProfile'] ?? ''));

    if (empty($trainerName) || empty($trainerEmail) || $salary <= 0) {
        redirectWithError("Missing required fields (Name, Email, Rate).");
    }

    $profile_picture_path = $current_profile_path; 

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file = $_FILES['profile_picture'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($file_extension, $allowed_extensions)) {
            redirectWithError("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
        } 
        
        $new_filename = uniqid('trainer_') . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $profile_picture_path = $target_file;
            
            if ($current_profile_path && file_exists($current_profile_path) && $current_profile_path !== $target_file) {
                unlink($current_profile_path);
            }
        } else {
            $_SESSION['error_message'] = "Error uploading new profile picture. Keeping old image.";
        }
    }
    try {
        $sql = "UPDATE trainer SET
                    trainerName = ?, 
                    trainerEmail = ?, 
                    trainerPhone = ?, 
                    specialization = ?, 
                    salary = ?, 
                    status = ?, 
                    trainerProfile = ? 
                WHERE trainerID = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdssi", 
            $trainerName, 
            $trainerEmail, 
            $trainerPhone, 
            $specialization, 
            $salary, 
            $status, 
            $profile_picture_path, 
            $trainerID
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Trainer **{$trainerName}** updated successfully.";
        } else {
            redirectWithError("Update failed: " . $stmt->error);
        }
        
    } catch (\Exception $e) {
        redirectWithError("A database error occurred during update: " . $e->getMessage());
    }
    
    header("Location: trainers-list.php");
    exit();
}

if ($action === 'delete' && $trainerID) {
    try {
        $path_stmt = $conn->prepare("SELECT trainerProfile FROM trainer WHERE trainerID = ?");
        $path_stmt->bind_param("i", $trainerID);
        $path_stmt->execute();
        $path_result = $path_stmt->get_result();
        $trainer_data = $path_result->fetch_assoc();
        $file_to_delete = $trainer_data['trainerProfile'] ?? null;
        $path_stmt->close();

        $stmt = $conn->prepare("DELETE FROM trainer WHERE trainerID = ?");
        $stmt->bind_param("i", $trainerID);
        
        if ($stmt->execute()) {
            if ($file_to_delete && file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
            $_SESSION['success_message'] = "Trainer ID: **{$trainerID}** deleted successfully, along with the profile picture.";
        } else {
            redirectWithError("Deletion failed: " . $stmt->error);
        }
        
    } catch (\Exception $e) {
        redirectWithError("A database error occurred during deletion: " . $e->getMessage());
    }

    header("Location: trainers-list.php");
    exit();
}

redirectWithError("Invalid or missing action.");
?>