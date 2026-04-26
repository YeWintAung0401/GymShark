<?php
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}
$trainers = $conn->query("SELECT trainerID, trainerName, specialization FROM trainer WHERE status='Active'");
$customers = $conn->query("SELECT customerID, customerName FROM customer");

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM schedule WHERE scheduleID = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Session removed successfully.";
    } else {
        $_SESSION['error'] = "Could not remove the session.";
    }

    header("Location: schedule-list.php");
    exit();
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedules - GYMSHARK Admin</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 flex h-screen font-sans">

    <?php include('sidebar.php'); ?>

    <main class="flex-grow p-8 overflow-y-auto">
        <header class="mb-8 border-b pb-4" style="padding-left: 50px;">
            <h1 class="text-3xl font-black italic uppercase">Schedule <span class="text-red-600">Control</span></h1>
            <p class="text-gray-500 text-sm">Assign trainers to members and manage gym sessions.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-lg border-t-4 border-black">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="text-red-600"></i> Record Session
                    </h2>
                    
                    <form action="schedule-process.php?action=add" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Customer</label>
                            <select name="customerID" required class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-black">
                                <option value="">Select Member</option>
                                <?php while($c = $customers->fetch_assoc()): ?>
                                    <option value="<?= $c['customerID'] ?>"><?= $c['customerID'] . ' - ' . $c['customerName'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Trainer</label>
                            <select name="trainerID" required class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-black">
                                <option value="">Select Trainer</option>
                                <?php while($t = $trainers->fetch_assoc()): ?>
                                    <option value="<?= $t['trainerID'] ?>">
                                        <?= $t['trainerName'] . ' - ' . $t['specialization'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                       

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Date</label>
                                <input type="date" name="date" required class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-black">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Session</label>
                                <select name="session" class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-black">
                                    <option value="Morning">Morning</option>
                                    <option value="Afternoon">Afternoon</option>
                                    <option value="Evening">Evening</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Start Time</label>
                                <input type="time" name="started_time" required class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-black">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">End Time</label>
                                <input type="time" name="ended_time" required class="w-full p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-black">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-blue-600 transition shadow-lg uppercase tracking-wider">
                            Confirm Schedule
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-black text-white">
                            <tr>
                                <th class="p-4 text-12 uppercase font-bold">Member</th>
                                <th class="p-4 text-12 uppercase font-bold">Date/Time</th>
                                <th class="p-4 text-12 uppercase font-bold">Trainer</th>
                                <th class="p-4 text-12 uppercase font-bold">Activity</th>
                                <th class="p-4 text-right text-12 uppercase font-bold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php
                            $sql = "SELECT s.*, c.customerName, t.trainerName 
                                    FROM schedule s
                                    JOIN customer c ON s.customerID = c.customerID
                                    JOIN trainer t ON s.trainerID = t.trainerID
                                    ORDER BY s.date DESC, s.started_time ASC";
                            $schedules = $conn->query($sql);
                            while($row = $schedules->fetch_assoc()):
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 text-sm font-medium"><?= htmlspecialchars('ID - ' . $row['customerID'] . '. ' . $row['customerName']) ?></td>
                                <td class="p-4">
                                    <div class="font-bold text-sm"><?= date('M d, Y', strtotime($row['date'])) ?></div>
                                    <div class="text-xs text-gray-400"><?= $row['started_time'] ?> - <?= $row['ended_time'] ?></div>
                                </td>
                                <td class="p-4 text-sm text-gray-600 italic"><?= htmlspecialchars($row['trainerName']) ?></td>
                                <td class="p-4">
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-[12px] font-bold uppercase">
                                        <?= htmlspecialchars($row['activity']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button onclick="deleteSchedule(<?= $row['scheduleID'] ?>)" class="text-gray-300 hover:text-red-600">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
        function deleteSchedule(id) {
            if(confirm('Are you sure you want to remove this session?')) {
                window.location.href = 'schedule-process.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>