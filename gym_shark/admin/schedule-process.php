<?php
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$action = $_GET['action'] ?? '';

if ($action == 'add') {
    $custID = $_POST['customerID'];
    $trainID = $_POST['trainerID'];
    $session = $_POST['session'];
    $date = $_POST['date'];
    $activity = $_POST['activity'];
    $start = $_POST['started_time'];
    $end = $_POST['ended_time'];

    $spec_lookup = $conn->prepare("SELECT specialization FROM trainer WHERE trainerID = ?");
    $spec_lookup->bind_param("i", $trainID);
    $spec_lookup->execute();
    $result = $spec_lookup->get_result();
    $trainer_data = $result->fetch_assoc();
    
    $specialization = ($trainer_data) ? $trainer_data['specialization'] : "General";

    $stmt = $conn->prepare("INSERT INTO schedule (customerID, trainerID, session, date, activity, started_time, ended_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $custID, $trainID, $session, $date, $specialization, $start, $end);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Schedule recorded successfully.";
    }
    header("Location: schedule-list.php");
}

if ($action == 'delete') {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM schedule WHERE scheduleID = $id");
    header("Location: schedule-list.php");
}


?>