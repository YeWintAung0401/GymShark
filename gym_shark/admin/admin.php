<?php
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$queryMembers = "SELECT COUNT(*) as total FROM customer";
$resultMembers = mysqli_query($conn, $queryMembers);
$totalMembers = mysqli_fetch_assoc($resultMembers)['total'];

$queryRevenue = "SELECT SUM(p.price) as total_revenue FROM membership m JOIN plan p ON p.planID = m.planID";
$resultRevenue = mysqli_query($conn, $queryRevenue);
$revenueData = mysqli_fetch_assoc($resultRevenue);
$totalRevenue = $revenueData['total_revenue'] ?? 0;

$queryTrainers = "SELECT COUNT(*) as total FROM trainer WHERE status IN ('Active', 'Contract')";
$resultTrainers = mysqli_query($conn, $queryTrainers);
$totalTrainers = mysqli_fetch_assoc($resultTrainers)['total'];

$queryStaff = "SELECT COUNT(*) as totalStaff FROM staff";
$resultStaff = mysqli_query($conn, $queryStaff);
$totalStaff = mysqli_fetch_assoc($resultStaff)['totalStaff'];

$plans = [];
$queryPlans = "SELECT * FROM plan"; 
$resultPlans = mysqli_query($conn, $queryPlans);
if ($resultPlans) {
    while ($row = mysqli_fetch_assoc($resultPlans)) {
        $plans[] = $row;
    }
}

$all_feedbacks = [];
$feedback_query = "SELECT feedbackID, customerID, fullName, email, rating, feedback, submissionDate FROM feedback ORDER BY submissionDate DESC";
$feedback_result = $conn->query($feedback_query);

if ($feedback_result && $feedback_result->num_rows > 0) {
    while ($row = $feedback_result->fetch_assoc()) {
        $all_feedbacks[] = $row;
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            align-items: stretch; 
        }

        .price-card {
            display: flex;
            flex-direction: column;
            height: 100%; 
        }

        .price-card .btn-container {
            margin-top: auto; 
            padding-top: 20px;
        }

        .star-icon-active {
            color: #00d4ff !important; 
        }
        .star-icon-inactive {
            color: #333 !important;
        }
        .rating-container {
            display: flex;
            gap: 2px;
            align-items: center;
        }
    </script>
</head>
<body class="bg-gray-50 flex h-screen font-sans">

<?php include 'sidebar.php'; ?>

<div class="main-content w-full p-8" style="padding-left: 100px; background-color: #f8fafc; min-height: 100vh;">
    <header class="mb-10">
        <h1 class="text-4xl font-bold text-slate-800" style="font-family: 'Orbitron', sans-serif; letter-spacing: 1px;">
            Gym <span style="color: #0ea5e9;">Dashboard</span>
        </h1>
        <p class="text-slate-500 font-medium">System Overview & Management</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <h3 style="font-family: 'Orbitron', sans-serif; color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Total Members</h3>
                <div class="p-2 bg-blue-50 rounded-lg">
                    <i data-lucide="users" class="text-blue-500 w-5 h-5"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900" style="font-family: 'Orbitron', sans-serif;"><?php echo number_format($totalMembers); ?></p>
            <p class="text-xs text-green-600 mt-2 font-semibold">Active in system</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <h3 style="font-family: 'Orbitron', sans-serif; color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Total Revenue</h3>
                <div class="p-2 bg-emerald-50 rounded-lg">
                    <i data-lucide="dollar-sign" class="text-emerald-500 w-5 h-5"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900" style="font-family: 'Orbitron', sans-serif;">$<?php echo number_format($totalRevenue, 2); ?></p>
            <p class="text-xs text-slate-400 mt-2">Gross membership value</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <h3 style="font-family: 'Orbitron', sans-serif; color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Total Staffs</h3>
                <div class="p-2 bg-blue-50 rounded-lg">
                    <i data-lucide="users" class="text-blue-500 w-5 h-5"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900" style="font-family: 'Orbitron', sans-serif;"><?php echo number_format($totalStaff); ?></p>
            <p class="text-xs text-green-600 mt-2 font-semibold">Certified Staffs</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-4">
                <h3 style="font-family: 'Orbitron', sans-serif; color: #64748b; font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">Active Trainers</h3>
                <div class="p-2 bg-orange-50 rounded-lg">
                    <i data-lucide="dumbbell" class="text-orange-500 w-5 h-5"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900" style="font-family: 'Orbitron', sans-serif;"><?php echo $totalTrainers; ?></p>
            <p class="text-xs text-slate-400 mt-2">Certified Trainers</p>
        </div>
    </div>

    <div class="flex items-center gap-4 mb-8">
        <h2 class="text-2xl font-bold text-slate-800" style="font-family: 'Orbitron', sans-serif; text-transform: uppercase;">
            Membership <span class="text-blue-600">Tiers</span>
        </h2>
        <div class="flex-grow h-px bg-slate-200"></div>
    </div>

    <div class="pricing-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin: 0 auto; padding-bottom: 50px;">
        <?php if (!empty($plans)): ?>
            <?php foreach ($plans as $plan): ?>
                <?php $isFeatured = isset($plan['isPopular']) && (int)$plan['isPopular'] === 1; ?>
                
                <div class="price-card" style="background: white; padding: 40px; border-radius: 20px; text-align: center; border: 2px solid <?php echo $isFeatured ? '#3b82f6' : '#f1f5f9'; ?>; position: relative; display: flex; flex-direction: column; transition: all 0.3s ease; box-shadow: <?php echo $isFeatured ? '0 10px 25px -5px rgba(59, 130, 246, 0.1)' : '0 4px 6px -1px rgba(0, 0, 0, 0.05)'; ?>;">
                    
                    <?php if ($isFeatured): ?>
                        <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: #3b82f6; color: white; padding: 4px 16px; border-radius: 20px; font-weight: 800; font-size: 0.7rem; font-family: 'Orbitron', sans-serif; letter-spacing: 1px;">
                            POPULAR CHOICE
                        </div>
                    <?php endif; ?>

                    <h3 style="font-family: 'Orbitron', sans-serif; margin-bottom: 15px; font-size: 1.4rem; color: #1e293b; text-transform: uppercase; font-weight: 700;">
                        <?php echo htmlspecialchars($plan['planName']); ?>
                    </h3>

                    <div style="margin-bottom: 25px;">
                        <span style="font-size: 2.5rem; font-weight: 800; color: #0f172a; font-family: 'Orbitron', sans-serif;">$<?php echo number_format($plan['price'], 0); ?></span>
                        <span style="font-size: 1rem; color: #64748b; font-weight: 500;">/<?php echo htmlspecialchars($plan['duration']); ?></span>
                    </div>

                    <ul style="list-style: none; margin-bottom: 35px; color: #475569; text-align: left; padding: 0;">
                        <?php 
                            $features = explode(',', $plan['description']); 
                            foreach($features as $feature): 
                        ?>
                            <li style="margin-bottom: 14px; font-size: 0.95rem; display: flex; align-items: center;">
                                <div style="background: #f0f9ff; border-radius: 50%; padding: 4px; margin-right: 12px;">
                                    <i class="fas fa-check" style="color: #0ea5e9; font-size: 0.75rem;"></i>
                                </div>
                                <?php echo htmlspecialchars(trim($feature)); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="btn-container" style="margin-top: auto;">
                        <a href="plans.php?id=<?php echo $plan['planID']; ?>" 
                           style="display: block; width: 100%; padding: 14px 0; background: <?php echo $isFeatured ? '#3b82f6' : '#ffffff'; ?>; color: <?php echo $isFeatured ? '#ffffff' : '#3b82f6'; ?>; text-decoration: none; font-weight: 700; border: 2px solid #3b82f6; text-align: center; text-transform: uppercase; font-family: 'Orbitron', sans-serif; transition: all 0.2s ease; border-radius: 12px; font-size: 0.85rem;"
                           onmouseover="this.style.background='#2563eb'; this.style.color='#ffffff'; this.style.borderColor='#2563eb';"
                           <?php if(!$isFeatured): ?>
                           onmouseout="this.style.background='#ffffff'; this.style.color='#3b82f6'; this.style.borderColor='#3b82f6';"
                           <?php else: ?>
                           onmouseout="this.style.background='#3b82f6'; this.style.color='#ffffff'; this.style.borderColor='#3b82f6';"
                           <?php endif; ?>>
                           Edit Tier Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- feedbacks -->
    <div class="main-content w-full p-8" style="padding-left: 100px; background-color: #f8fafc;">
        <div class="flex items-center gap-4 mb-8">
            <h2 class="text-2xl font-bold text-slate-800" style="font-family: 'Orbitron', sans-serif; text-transform: uppercase;">
                Customer <span class="text-blue-600">Feedbacks</span>
            </h2>
            <div class="flex-grow h-px bg-slate-200"></div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="p-4 text-xs font-bold uppercase text-slate-500" style="font-family: 'Orbitron', sans-serif;">Customer Info</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500" style="font-family: 'Orbitron', sans-serif;">Rating</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500" style="font-family: 'Orbitron', sans-serif;">Feedback Message</th>
                            <th class="p-4 text-xs font-bold uppercase text-slate-500" style="font-family: 'Orbitron', sans-serif;">Date Submitted</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (!empty($all_feedbacks)): ?>
                            <?php foreach ($all_feedbacks as $item): ?>
                                <tr class="hover:bg-blue-50/20 transition-colors">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($item['fullName']); ?></div>
                                        <div class="text-[12px] text-slate-400"><?php echo htmlspecialchars($item['email']); ?></div>
                                    </td>

                                    <td class="p-4">
                                        <div class="flex flex-col gap-1">
                                            <div class="star-display">
                                                <?php 
                                                for($i=5; $i>=1; $i--): ?>
                                                    <i class="fas fa-star <?php echo ($i <= $item['rating']) ? 'star-active' : 'star-inactive'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-[12px] font-bold text-slate-400 uppercase tracking-widest">
                                                Score: <?php echo $item['rating']; ?>/5
                                            </span>
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                            <p class="text-[14px] font-medium text-slate-500">
                                                <?php echo htmlspecialchars($item['feedback']); ?>
                                            </p>
                                        </div>
                                    </td>

                                    <td class="p-4">
                                        <div class="text-[12px] font-medium text-slate-500">
                                            <?php echo date('M d, Y', strtotime($item['submissionDate'])); ?>
                                        </div>
                                        <div class="text-[12px] text-slate-400 uppercase">
                                            <?php echo date('h:i A', strtotime($item['submissionDate'])); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="p-10 text-center text-slate-400 italic">
                                    <i class="fas fa-comment-slash block text-2xl mb-2"></i>
                                    No feedback has been submitted yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<script src="sidebar.js"></script>
<script>
    lucide.createIcons();
</script>

</body>
</html>