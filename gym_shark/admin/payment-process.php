<?php
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add_bank') {
        $bankName = $_POST['bankName'];
        $accountHolder = $_POST['accountHolder'];
        $accountNumber = $_POST['accountNumber'];
        
        $uploadDir = "../banks/";
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

        $fileName = time() . "_" . basename($_FILES['bankLogo']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['bankLogo']['tmp_name'], $targetPath)) {
            $sql = "INSERT INTO payment (bankName, accountHolder, accountNumber, bankLogo) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $bankName, $accountHolder, $accountNumber, $targetPath);
            
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Bank Added Successfully!";
            }
            $stmt->close();
        }
    }
    header("Location: manage-payment.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $res = mysqli_query($conn, "SELECT bankLogo FROM payment WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    if ($row && file_exists($row['bankLogo'])) {
        unlink($row['bankLogo']);
    }

    mysqli_query($conn, "DELETE FROM payment WHERE id=$id");
    $_SESSION['msg'] = "Bank Deleted!";
    header("Location: manage-payment.php");
    exit();
}