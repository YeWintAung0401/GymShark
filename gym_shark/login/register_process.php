<?php 
include('connection.php');
session_start();

if (isset($_POST['register-btn'])) {

    $errors = [];

    $name     = mysqli_real_escape_string($conn, trim($_POST['register-name']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['register-email']));
    $phone    = mysqli_real_escape_string($conn, trim($_POST['register-phone']));
    $address  = mysqli_real_escape_string($conn, trim($_POST['register-address']));
    $password = $_POST['register-password'];
    $confirm  = $_POST['register-confirm-password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    $check = $conn->prepare("SELECT customerEmail FROM customer WHERE customerEmail = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $errors[] = "Email is already registered";
    }
    $check->close();

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $profile = "";

        $stmt = $conn->prepare("
            INSERT INTO customer (customerName, customerEmail, customerPhone, customerAddress, customerPassword, customerProfile)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("ssssss", 
            $name, 
            $email, 
            $phone, 
            $address, 
            $hashedPassword, 
            $profile
        );

        if ($stmt->execute()) {

            $last_id = $conn->insert_id;
            $user_result = $conn->query("SELECT * FROM customer WHERE customerID = $last_id");

            if ($user_result && $user_result->num_rows === 1) {
                $customer = $user_result->fetch_assoc();
                $_SESSION['customer']   = $customer;
                $_SESSION['customerID'] = $customer['customerID'];

                echo "<script>
                        alert('Registration Successful! You can now log in.');
                        window.location.href = 'uploadProfile.php';
                      </script>";
                exit();
            }

        } else {
            $errors[] = "Registration failed: " . $stmt->error;
        }

        $stmt->close();
    }

    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        header("Location: register.php");
        exit();
    }
}
?>
