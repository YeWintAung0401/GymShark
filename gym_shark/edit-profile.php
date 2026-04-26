<?php
include('./connection.php');
session_start();

if (!isset($_SESSION['customerID'])) {
    header("Location: login.php");
    exit();
}

$customerID = $_SESSION['customerID'];

$today_str = date('Y-m-d');
$conn->query("UPDATE membership SET status = 'Expired' WHERE endDate < '$today_str' AND status = 'Active'");

define('UPLOAD_DIR', 'customer_profiles/'); 
define('DB_DIR', '../customer_profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name = mysqli_real_escape_string($conn, $_POST['customerName']);
    $email = mysqli_real_escape_string($conn, $_POST['customerEmail']);
    $phone = mysqli_real_escape_string($conn, $_POST['customerPhone']);
    $address = mysqli_real_escape_string($conn, $_POST['customerAddress']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $existingProfile = $_POST['existingProfile'];

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $db_path = $existingProfile; 

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        
        $file = $_FILES['profile_picture'];
        $file_type = $file['type'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

        if (!array_key_exists($file_type, $allowedTypes)) {
            $_SESSION['error_message'] = 'ERROR: Only JPG, PNG, and GIF allowed.';
        } elseif ($file_size > MAX_FILE_SIZE) {
            $_SESSION['error_message'] = 'ERROR: File too large (Max 5MB).';
        } else {
            $extension = $allowedTypes[$file_type];
            $new_file_name = 'profile_' . $customerID . '_' . time() . '.' . $extension;
            $destination = UPLOAD_DIR . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $db_path = DB_DIR . $new_file_name;

                if (!empty($existingProfile) && file_exists($existingProfile) && strpos($existingProfile, 'default') === false) {
                    unlink($existingProfile);
                }
            }
        }
    }

    $stmt = $conn->prepare("UPDATE customer SET customerName=?, customerEmail=?, customerPhone=?, customerAddress=?, gender=?, customerProfile=? WHERE customerID=?");
    $stmt->bind_param("ssssssi", $name, $email, $phone, $address, $gender, $db_path, $customerID);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Saved Successfully.";
    } else {
        $_SESSION['error_message'] = "DATABASE ERROR: Sync failed.";
    }
    
    $stmt->close();
    header("Location: edit-profile.php"); 
    exit();
}

$stmt = $conn->prepare("SELECT customerID, customerName, customerEmail, customerPhone, customerAddress, gender, customerProfile FROM customer WHERE customerID = ?");
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

if (!$customer) {
    die("Profile not found.");
}

if (!empty($customer['customerProfile'])) {
    $imgSrc = ltrim($customer['customerProfile'], './');
} else {
    $imgSrc = './admin/default-profile.jpg';
}
$newPath = $imgSrc;

$memStmt = $conn->prepare("
    SELECT m.startDate, m.endDate, m.status, m.qr_token, 
    p.planName 
    FROM membership m 
    JOIN plan p ON m.planID = p.planID 
    WHERE m.customerID = ? AND m.status = 'Active' 
    LIMIT 1
");
$memStmt->bind_param("i", $customerID);
$memStmt->execute();
$membership = $memStmt->get_result()->fetch_assoc();

$historyStmt = $conn->prepare("
    SELECT m.*, p.planName, p.price 
    FROM membership m 
    JOIN plan p ON m.planID = p.planID 
    WHERE m.customerID = ? 
    ORDER BY m.startDate DESC
");
$historyStmt->bind_param("i", $customerID);
$historyStmt->execute();
$historyResult = $historyStmt->get_result();

$isExpired = false;
if ($membership) {
    $today = new DateTime();
    $expiry = new DateTime($membership['endDate']);
    if ($today > $expiry) {
        $isExpired = true;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Profile | Orbitron Fitness</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-blue: #00d4ff;
            --dark-bg: #050505;
            --card-bg: #111111;
            --accent-gray: #1a1a1a;
            --text-white: #ffffff;
            --text-gray: #b0b0b0;
        }
        body { background-color: var(--dark-bg); color: var(--text-white); font-family: 'Inter', sans-serif; }
        .heading-font { font-family: 'Orbitron', sans-serif; text-transform: uppercase; letter-spacing: 2px; }
        .cyber-input {
            background: transparent; border: none; border-bottom: 2px solid var(--accent-gray);
            transition: all 0.3s ease; color: var(--text-white); font-size: 1rem; width: 100%;
        }
        .cyber-input:focus { border-bottom-color: var(--primary-blue); outline: none; }
        .flash-message { animation: fadeInDown 0.4s ease forwards; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="pt-20">

    <nav class="flex justify-between items-center px-[5%] py-4 bg-black/90 fixed w-full top-0 z-[1000] border-b border-[#1a1a1a]">
        <div class="heading-font text-[1.4rem] font-bold">GYM<span class="text-[#00d4ff]">SHARK</span></div>
        <div class="flex items-center gap-6">
            <a href="./index.php" class="text-[1rem] font-bold text-white hover:text-[#00d4ff] transition-all uppercase tracking-[0.2em]">Exit</a>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-10">
        <header class="mb-10 text-center md:text-left">
            <h2 class="heading-font text-3xl md:text-4xl">System <span class="text-[#00d4ff]">Profile</span></h2>
            <p class="text-[#b0b0b0] text-sm mt-2 uppercase tracking-widest">Authorized Access: ID - <?php echo htmlspecialchars($customer['customerID']); ?></p>
        </header>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="flash-message bg-[#00d4ff] text-black font-bold px-6 py-4 rounded-sm mb-8 flex justify-between items-center shadow-[0_0_20px_rgba(0,212,255,0.3)]">
                <span class="text-sm uppercase tracking-tighter italic"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
                <button onclick="this.parentElement.remove()" class="text-black">✕</button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="flash-message bg-red-600 text-black font-bold px-6 py-4 rounded-sm mb-8 flex justify-between items-center">
                <span class="text-sm uppercase italic"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
                <button onclick="this.parentElement.remove()" class="text-white">✕</button>
            </div>
        <?php endif; ?>

        <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="customerID" value="<?php echo $customer['customerID']; ?>">
            <input type="hidden" name="existingProfile" value="<?php echo htmlspecialchars($customer['customerProfile']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 bg-[#111] p-8 md:p-12 border border-[#1a1a1a] rounded-xl">
                
                <div class="md:col-span-1 flex flex-col items-center space-y-6">
                    <div class="relative group">
                        <img id="preview" src="<?php echo htmlspecialchars($newPath); ?>" 
                             class="w-40 h-40 rounded-full object-cover border-2 border-[#00d4ff] p-1 shadow-[0_0_15px_rgba(0,212,255,0.2)]" alt="Avatar">
                        
                        <label for="file-upload" class="absolute bottom-1 right-1 bg-[#00d4ff] text-black p-2.5 rounded-full cursor-pointer hover:scale-110 transition shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <input id="file-upload" name="profile_picture" type="file" class="hidden" accept="image/*" onchange="previewImage(event)">
                        </label>
                    </div>
                    <p class="text-[11px] text-gray-500 uppercase tracking-widest text-center">Click icon to upload<br>JPG, PNG or WEBP</p>
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col gap-1">
                        <label class="heading-font text-sm text-[#00d4ff]">Full Name</label>
                        <input type="text" name="customerName" required value="<?php echo htmlspecialchars($customer['customerName']); ?>" class="cyber-input py-2">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="heading-font text-sm text-[#00d4ff]">Gender</label>
                        <select name="gender" class="cyber-input py-2 bg-[#111]">
                            <option value="Male" <?php echo ($customer['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($customer['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($customer['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            <option value="Prefer not to say" <?php echo ($customer['gender'] == 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="heading-font text-sm text-[#00d4ff]">Email</label>
                        <input type="email" name="customerEmail" required value="<?php echo htmlspecialchars($customer['customerEmail']); ?>" class="cyber-input py-2">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="heading-font text-sm text-[#00d4ff]">Phone</label>
                        <input type="text" name="customerPhone" value="<?php echo htmlspecialchars($customer['customerPhone']); ?>" class="cyber-input py-2">
                    </div>

                    <div class="md:col-span-2 flex flex-col gap-1">
                        <label class="heading-font text-sm text-[#00d4ff]">Address</label>
                        <textarea name="customerAddress" rows="2" class="cyber-input py-2 resize-none"><?php echo htmlspecialchars($customer['customerAddress']); ?></textarea>
                    </div>

                    <div class="md:col-span-2 pt-4">
                        <button type="submit" name="upload" class="heading-font w-full py-4 bg-[#00d4ff] text-black font-black text-sm hover:shadow-[0_0_30px_rgba(0,212,255,0.5)] transition-all transform active:scale-[0.98]">
                            Sync Profile Data
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-10 border-t border-[#1a1a1a]">
                <h3 class="heading-font text-xl mb-6 text-white">Access <span class="text-[#00d4ff]">Protocol</span></h3>
            
                <?php if ($membership): ?>
                    <div class="bg-[#111] p-8 rounded-xl border border-[#00d4ff]/20 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#00d4ff]/5 rounded-full blur-2xl group-hover:bg-[#00d4ff]/10 transition-all"></div>
                    
                        <div class="flex flex-col md:flex-row items-center gap-12 relative z-10">
        
                            <div class="flex flex-col items-center gap-6">
                                <div class="bg-white p-4 rounded-xl shadow-[0_0_25px_rgba(255,255,255,0.15)] border-8 border-[#00d4ff]">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo $membership['qr_token']; ?>&color=000&bgcolor=fff" 
                                         alt="Access QR" 
                                         class="w-40 h-40 md:w-48 md:h-48">
                                </div>
                                <p class="text-xs font-mono text-[#00d4ff] tracking-[0.3em] bg-[#00d4ff]/10 px-4 py-2 rounded-full border border-[#00d4ff]/20">
                                    ID: <?php echo substr($membership['qr_token'], 0, 30); ?>
                                </p>
                            </div>

                            <div class="flex-1 space-y-6 text-center md:text-left">
                                <div>
                                    <h4 class="heading-font text-3xl md:text-4xl text-white mb-2">ENTRY <span class="text-[#00d4ff]">PASS</span></h4>
                                    <p class="text-sm text-gray-400 uppercase tracking-[0.4em] font-bold">Plan: <?php echo htmlspecialchars($membership['planName']); ?></p>
                                </div>

                                <div class="bg-[#1a1a1a] p-6 rounded-lg border-l-8 border-[#00d4ff] shadow-inner">
                                    <div class="flex items-center gap-3 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00d4ff" stroke-width="3"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                        <span class="heading-font text-sm text-[#00d4ff] tracking-widest">Entry Instructions:</span>
                                    </div>
                                    <p class="text-base text-gray-200 leading-relaxed font-semibold">
                                        1. Increase your phone brightness to maximum.<br>
                                        2. Hold this QR code 5-10cm away from the entrance scanner.<br>
                                        3. Wait for the green light and the door to unlock.
                                    </p>
                                </div>

                                <div class="flex justify-between items-center pt-4 border-t border-white/10">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold tracking-widest">Access Valid Until</p>
                                        <p class="heading-font text-[#00d4ff] text-2xl"><?php echo date('d M, Y', strtotime($membership['endDate'])); ?></p>
                                    </div>
                                    <div class="hidden md:block">
                                        <span class="px-5 py-2 bg-green-500 text-black text-xs font-black uppercase tracking-tighter rounded-full shadow-[0_0_15px_rgba(34,197,94,0.4)]">
                                            Active Member
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php 
                            $start = strtotime($membership['startDate']);
                            $end = strtotime($membership['endDate']);
                            $total = $end - $start;
                            $now = time();
                            $percent = ($total > 0) ? min(100, max(0, (($now - $start) / $total) * 100)) : 100;
                        ?>
                        <div class="mt-8">
                            <div class="flex justify-between text-[9px] uppercase tracking-tighter mb-2 text-gray-500">
                                <span>Plan Cycle Progress</span>
                                <span><?php echo round($percent); ?>%</span>
                            </div>
                            <div class="w-full h-1 bg-[#1a1a1a] rounded-full overflow-hidden">
                                <div class="h-full bg-[#00d4ff] shadow-[0_0_10px_#00d4ff]" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-[#111] p-10 rounded-xl border border-dashed border-[#222] text-center">
                        <p class="text-gray-500 text-[10px] uppercase tracking-[0.3em] mb-4">No Active Access Protocol Found</p>
                        <a href="index.php#membership" class="inline-block heading-font text-[10px] bg-[#1a1a1a] text-[#00d4ff] border border-[#00d4ff]/30 px-8 py-3 hover:bg-[#00d4ff] hover:text-black transition-all">Purchase Plan</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-20 pt-12 border-t-2 border-[#1a1a1a]">
                <div class="flex justify-between items-center mb-10">
                    <h3 class="heading-font text-3xl text-white tracking-widest">BILLING <span class="text-[#00d4ff]">LOGS</span></h3>
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-[0.2em] bg-[#111] px-4 py-2 border border-[#1a1a1a] rounded">
                        Records: <?php echo $historyResult->num_rows; ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b-2 border-[#1a1a1a] bg-[#0a0a0a]">
                                <th class="py-7 px-4 heading-font text-[1rem] text-[#00d4ff]">MEMBERSHIP PLAN</th>
                                <th class="py-7 px-4 heading-font text-[1rem] text-[#00d4ff]">ACTIVE PERIOD</th>
                                <th class="py-7 px-4 heading-font text-[1rem] text-[#00d4ff]">TOTAL PAID</th>
                                <th class="py-7 px-4 heading-font text-[1rem] text-[#00d4ff]">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($historyResult->num_rows > 0): ?>
                                <?php while($row = $historyResult->fetch_assoc()): ?>
                                    <tr class="border-b border-[#1a1a1a] hover:bg-[#00d4ff]/5 transition-all group">
                                        <td class="py-7 px-4">
                                            <p class="text-xl font-black text-white group-hover:text-[#00d4ff] transition-colors">
                                                <?php echo htmlspecialchars($row['planName']); ?>
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1 font-mono uppercase">Reference: #ORB-<?php echo str_pad($row['membershipID'], 5, '0', STR_PAD_LEFT); ?></p>
                                        </td>
                            
                                        <td class="py-7 px-4">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-base text-gray-200 font-semibold"><?php echo date('M d, Y', strtotime($row['startDate'])); ?></span>
                                                <span class="text-[10px] text-[#00d4ff] font-bold uppercase opacity-50">▼ Valid Until ▼</span>
                                                <span class="text-base text-gray-200 font-semibold"><?php echo date('M d, Y', strtotime($row['endDate'])); ?></span>
                                            </div>
                                        </td>
                            
                                        <td class="py-7 px-4">
                                            <span class="text-2xl font-black text-white font-mono">
                                                $<?php echo number_format($row['price'], 2); ?>
                                            </span>
                                        </td>
                            
                                        <td class="py-7 px-4">
                                            <?php 
                                                $statusClasses = 'bg-gray-800 text-gray-400 border-gray-600';
                                                if ($row['status'] == 'Active') $statusClasses = 'bg-[#00d4ff]/10 text-[#00d4ff] border-[#00d4ff] shadow-[0_0_15px_rgba(0,212,255,0.2)]';
                                                if ($row['status'] == 'Expired') $statusClasses = 'bg-red-900/20 text-red-500 border-red-500/50';
                                            ?>
                                            <span class="px-5 py-2 border-2 text-xs font-black uppercase tracking-widest rounded-md <?php echo $statusClasses; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="py-20 text-center">
                                        <p class="text-gray-600 heading-font text-sm italic tracking-widest">No verified transactions found in the database.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>


        </form>
    </main>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('preview');
                output.src = reader.result;
            }
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.flash-message');
            messages.forEach(function(msg) {
                setTimeout(function() {
                    msg.style.opacity = "0";
                    msg.style.transform = "translateY(-10px)";
                    msg.style.transition = "all 0.4s ease";
                    setTimeout(() => msg.remove(), 400);
                }, 3000);
            });
        });
    </script>
</body>
</html>