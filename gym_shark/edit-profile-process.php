<?php
include('./connection.php');
session_start();

define('UPLOAD_DIR', '../customer_profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

$customerId = $_SESSION['customerID'] ?? null;
$message_type = '';
$message_text = '';
$redirect_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customerId) {
    
    
    if (isset($_POST['upload']) || isset($_POST['customerName'])) {
        
        $name = mysqli_real_escape_string($conn, $_POST['customerName']);
        $email = mysqli_real_escape_string($conn, $_POST['customerEmail']);
        $phone = mysqli_real_escape_string($conn, $_POST['customerPhone']);
        $address = mysqli_real_escape_string($conn, $_POST['customerAddress']);
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            
            $file = $_FILES['profile_picture'];
            $file_type = $file['type'];
            $file_size = $file['size'];
            $file_tmp = $file['tmp_name'];
            
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

            if (!array_key_exists($file_type, $allowedTypes)) {
                $_SESSION['error_message'] = 'ERROR: Only JPG, PNG, and GIF allowed.';
                header("Location: edit-profile.php");
                exit();
            } elseif ($file_size > MAX_FILE_SIZE) {
                $_SESSION['error_message'] = 'ERROR: Data overflow. File must be under 5MB.';
                header("Location: edit-profile.php");
                exit();
            } else {
                $extension = $allowedTypes[$file_type];
                $new_file_name = uniqid('profile_', true) . '.' . $extension;
                $targetPath = UPLOAD_DIR . $new_file_name;

                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                
                if (move_uploaded_file($file_tmp, $targetPath)) {
                    $stmt = $conn->prepare("UPDATE customer SET customerName=?, customerEmail=?, customerPhone=?, customerAddress=?, gender=?, customerProfile=? WHERE customerID=?");
                    $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $gender, $targetPath, $customerId);
                }
            }
        } else {
            $stmt = $conn->prepare("UPDATE customer SET customerName=?, customerEmail=?, customerPhone=?, customerAddress=?, gender=? WHERE customerID=?");
            $stmt->bind_param("sssssi", $name, $email, $phone, $address, $gender, $customerId);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Saved Successfully.";
            header("Location: edit-profile.php");
        } else {
            $_SESSION['error_message'] = "DATABASE ERROR: Sync failed.";
            header("Location: edit-profile.php");
        }
        $stmt->close();
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}