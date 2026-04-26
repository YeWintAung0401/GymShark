<?php 
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$upload_dir = 'trainer_profiles/'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-trainer.php"); 
    exit();
}

$errors = [];
$profile_picture_path = null;
$required_fields = ['trainer_name', 'trainer_gender', 'trainer_email', 'date_hired', 'specialization', 'salary', 'trainer_status'];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['status_message'] = "Field '{$field}' is required.";
    }
}

$trainerName    = mysqli_real_escape_string($conn, trim($_POST['trainer_name'] ?? ''));
$gender         = mysqli_real_escape_string($conn, $_POST['trainer_gender'] ?? '');
$trainerPhone   = mysqli_real_escape_string($conn, trim($_POST['trainer_phone'] ?? ''));
$trainerEmail   = mysqli_real_escape_string($conn, trim($_POST['trainer_email'] ?? ''));
$specialization = mysqli_real_escape_string($conn, $_POST['specialization'] ?? '');
$hired_date     = mysqli_real_escape_string($conn, $_POST['date_hired'] ?? '');
$trainerProfile = mysqli_real_escape_string($conn, trim($_POST['profile_picture'] ?? '')); 
$salary    = (float)($_POST['salary'] ?? 0.00);
$status         = mysqli_real_escape_string($conn, $_POST['trainer_status'] ?? 'Active');
$adminID        = 1; 

if (!filter_var($trainerEmail, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['status_message'] = "Invalid email format for trainer.";
    $errors[] = "Invalid email format.";
}

if (empty($errors)) {
    $check = $conn->prepare("SELECT trainerEmail FROM trainer WHERE trainerEmail = ?");
    $check->bind_param("s", $trainerEmail);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['status_message'] = "Trainer email is already registered.";
        $errors[] = "Trainer email already exists.";
    }
    $check->close();
}

if (empty($errors) && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            $_SESSION['status_message'] = "Failed to create upload directory.";
            $errors[] = "Failed to create upload directory.";
        }
    }

    if (empty($errors)) {
        $file = $_FILES['profile_picture'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($file_extension, $allowed_extensions)) {
            $_SESSION['status_message'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        } else {
            $file_name = uniqid('trainer_') . '.' . $file_extension;
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $profile_picture_path = $target_file; 
            } else {
                $_SESSION['status_message'] = "Failed to move the uploaded file.";
                $errors[] = "Failed to move the uploaded file.";
            }
        }
    }
}

if (empty($errors)) {
    $stmt = $conn->prepare("
        INSERT INTO trainer (
            trainerName, 
            gender, 
            trainerPhone, 
            trainerEmail, 
            specialization,
            hired_date, 
            trainerProfile,
            salary,
            status, 
            adminID
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->bind_param("sssssssdsi", 
        $trainerName, 
        $gender, 
        $trainerPhone, 
        $trainerEmail, 
        $specialization,
        $hired_date, 
        $profile_picture_path, 
        $salary,
        $status, 
        $adminID
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Trainer '{$trainerName}' registered successfully!";
        
        header("Location: trainers-list.php"); 
        exit();

    } else {
        $errors[] = "Trainer registration failed: " . $stmt->error;
        if ($profile_picture_path && file_exists($profile_picture_path)) {
            unlink($profile_picture_path);
        }
    }

    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['registration_errors'] = $errors;
    $_SESSION['old_input'] = $_POST;
    
    header("Location: add-trainer.php");
    exit();
}
?>