<?php
include('../connection.php');
session_start();

// Security: Check Login
if (!isset($_SESSION['customerID'])) {
    $_SESSION['error_message'] = "ACCESS DENIED: Please login to select a plan.";
    header("Location: ../login/login.php");
    exit();
}

$planID = isset($_GET['planID']) ? intval($_GET['planID']) : 0;
$customerID = $_SESSION['customerID'];

// Fetch selected plan
$stmt = $conn->prepare("SELECT * FROM plan WHERE planID = ?");
$stmt->bind_param("i", $planID);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    header("Location: ../index.php#membership");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Membership | Orbitron</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-blue: #00d4ff;
            --dark-bg: #050505;
            --card-bg: #111111;
        }
        body { background-color: var(--dark-bg); color: white; font-family: 'Inter', sans-serif; }
        .heading-font { font-family: 'Orbitron', sans-serif; text-transform: uppercase; letter-spacing: 2px; }
        
        /* Cyberpunk Glow Effect */
        .glow-border {
            border: 1px solid var(--primary-blue);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.1);
        }
        
        .plan-confirm-card {
            background: linear-gradient(145deg, #111, #080808);
            border: 1px solid #1a1a1a;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="max-w-lg w-full px-6 py-12">
        <div class="text-center mb-8">
            <h1 class="heading-font text-2xl">Confirm <span class="text-[#00d4ff]">Selection</span></h1>
            <p class="text-gray-500 text-xs mt-2 uppercase tracking-widest">Securing Tier: <?php echo htmlspecialchars($plan['planName']); ?></p>
        </div>

        <div class="plan-confirm-card rounded-2xl p-8 glow-border">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="heading-font text-lg"><?php echo htmlspecialchars($plan['planName']); ?></h3>
                    <p class="text-gray-400 text-sm">Access Level: Standard</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-[#00d4ff]">$<?php echo number_format($plan['price'], 2); ?></p>
                    <p class="text-[10px] text-gray-500 uppercase"><?php echo htmlspecialchars($plan['duration']); ?></p>
                </div>
            </div>

            <hr class="border-[#1a1a1a] mb-6">

            <div class="space-y-4 mb-8">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Activation Date</span>
                    <span class="text-white"><?php echo date('d M, Y'); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Billing Cycle</span>
                    <span class="text-white"><?php echo htmlspecialchars($plan['duration']); ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Payment Status</span>
                    <span class="text-[#00d4ff] font-bold">PENDING_SYNC</span>
                </div>
            </div>

            <form id="membership-form" action="process-membership.php" method="POST">
                <input type="hidden" name="planID" value="<?php echo $plan['planID']; ?>">
                <input type="hidden" name="price" value="<?php echo $plan['price']; ?>">
                
                <button type="submit" id="confirm-btn" class="heading-font w-full py-4 bg-[#00d4ff] text-black font-black text-sm hover:shadow-[0_0_30px_rgba(0,212,255,0.4)] transition-all">
                    Activate Membership
                </button>
            </form>

            <a href="../index.php" class="block text-center mt-6 text-xs text-gray-600 hover:text-white transition-colors uppercase tracking-widest">
                Cancel Transaction
            </a>
        </div>
    </div>

    <script>
    </script>
</body>
</html>