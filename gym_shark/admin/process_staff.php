<?php
include '../connection.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $name = $_POST['staffName'];
    $phone = $_POST['staffPhone'];
    $email = $_POST['staffEmail'];
    $address = $_POST['staffAddress'];
    $role = $_POST['role'];
    $salary = $_POST['salary'];
    $staffID = $_POST['staffID'] ?? null;
    $adminID = $_SESSION['adminID'];

    if ($action == 'register') {
        $sql = "INSERT INTO staff (staffName, staffPhone, staffEmail, staffAddress, role, salary, adminID) 
                VALUES ('$name', '$phone', '$email', '$address', '$role', '$salary', '$adminID')";
    } elseif ($action == 'update') {
        $sql = "UPDATE staff SET staffName='$name', staffPhone='$phone', staffEmail='$email', 
                staffAddress='$address', role='$role', salary='$salary' 
                WHERE staffID='$staffID'";
    }

    if ($conn->query($sql)) {
        header("Location: staff_management.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    $sql = "DELETE FROM staff WHERE staffID='$id'";
    if ($conn->query($sql)) {
        header("Location: staff_management.php?status=deleted");
    }
}
?>