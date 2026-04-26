<?php
include('connection.php'); 
session_start();

$customerID = isset($_SESSION['customerID']);


$plans = [];
try {
    $sql = "SELECT planID, planName, price, duration, description, isPopular FROM plan ORDER BY price ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
} catch (\Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

$trainers = [];
try {
    $sqlTrainer = "SELECT trainerName, specialization, trainerProfile FROM trainer WHERE status IN ('Active', 'Contract') ORDER BY trainerName ASC";
    $TrainerResult = $conn->query($sqlTrainer);
    if ($TrainerResult) {
        while ($TrainerRow = $TrainerResult->fetch_assoc()) {
            $trainers[] = $TrainerRow;
        }
    }
} catch (\Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}


$customer = [];
try {
    $sqlCustomer = "SELECT customerID, customerName, customerProfile FROM customer WHERE customerID = ?";
    $stmt = $conn->prepare($sqlCustomer);
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['customerID']);
        $stmt->execute();
        $customerResult = $stmt->get_result();
        if ($customerResult) {
            $customer = $customerResult->fetch_assoc();
        }
    }
} catch (\Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}


$query = "SELECT * FROM feedback ORDER BY submissionDate DESC LIMIT 5";
$result = $conn->query($query);

$all_feedbacks = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $all_feedbacks[] = $row;
    }
}

$payment_accounts = [];
try {
    $sqlPayment = "SELECT bankName, accountHolder, accountNumber, bankLogo FROM payment WHERE status = 'Active'";
    $paymentResult = $conn->query($sqlPayment);
    if ($paymentResult) {
        while ($pRow = $paymentResult->fetch_assoc()) {
            $payment_accounts[] = $pRow;
        }
    }
} catch (\Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}

?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMSHARK Fitness Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Inter:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/style.css">
    <script src="../admin/script.js"></script>
    
    <style>
        :root {
            --primary-blue: #00d4ff;
            --dark-bg: #050505;
            --card-bg: #111111;
            --accent-gray: #1a1a1a;
            --text-white: #ffffff;
            --text-gray: #b0b0b0;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0; 
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-white);
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        h1, h2, h3, .logo {
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Navigation */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background: rgba(0, 0, 0, 0.74);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--accent-gray);
        }

        .logo { 
            font-size: 1.4rem; 
            font-weight: bold; 
        }
        .logo span { 
            color: var(--primary-blue); 
            font-family: 'Orbitron', sans-serif;
        }

        .nav-links { display: flex; list-style: none; gap: 25px; }
        .nav-links a {
            color: var(--text-white);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
        }
        .nav-links a:hover { color: var(--primary-blue); }

        /* ===== AUTH / PROFILE SECTION ===== */
        .auth-section {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* Profile link wrapper */
        .profile-link {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== PROFILE AVATAR (CIRCLE IMAGE) ===== */
        .profile-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-blue);
            background: var(--card-bg);
            transition: var(--transition);
        }

        /* Hover effect */
        .profile-avatar:hover {
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.6);
            transform: scale(1.05);
        }

        /* ===== LOGOUT ICON ===== */
        .logout-btn {
            color: var(--text-gray);
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .logout-btn:hover {
            color: var(--primary-blue);
        }

        /* ===== JOIN BUTTON ===== */
        .join-btn {
            background: linear-gradient(135deg, var(--primary-blue), #009cff);
            color: #000;
            padding: 9px 22px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.4);
        }

        .join-btn:hover {
            box-shadow: 0 0 25px rgba(0, 212, 255, 0.8);
            transform: translateY(-2px);
        }


        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.2)), 
                        url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=2070&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            padding: 0 10%;
        }

        .hero-content { max-width: 700px; }
        .hero-content h1 { font-size: clamp(2.5rem, 6vw, 4.5rem); line-height: 1.1; margin-bottom: 20px; }
        .hero-content p { font-size: 1.1rem; color: var(--text-gray); margin-bottom: 35px; }
        
        .highlight { color: var(--primary-blue); }

        /* General UI Elements */
        .btn {
            padding: 16px 40px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            border: none;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        .btn-primary { background: var(--primary-blue); color: #000; }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 212, 255, 0.3); }

        .section { padding: 100px 10%; }
        .section-title { text-align: center; margin-bottom: 60px; }
        .section-title h2 { font-size: 2.5rem; }
        .section-title p { color: var(--text-gray); margin-top: 10px; }

        /* Trainers Grid */
        .trainer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }
        .trainer-card {
            background: var(--card-bg);
            border-radius: 10px;
            overflow: hidden;
            transition: var(--transition);
            position: relative;
        }
        .trainer-img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            filter: grayscale(100%);
            transition: var(--transition);
        }
        .trainer-card:hover .trainer-img { filter: grayscale(0%); transform: scale(1.05); }
        .trainer-info { padding: 20px; text-align: center; }
        .trainer-info h3 { font-size: 1.2rem; margin-bottom: 5px; color: var(--primary-blue); }
        .trainer-info p { font-size: 0.9rem; color: var(--text-gray); }

        /* Schedule Table */
        .schedule-container { overflow-x: auto; background: var(--card-bg); padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { padding: 20px; text-align: center; border-bottom: 1px solid var(--accent-gray); }
        th { color: var(--primary-blue); text-transform: uppercase; }
        .class-name { font-weight: bold; display: block; }
        .class-time { font-size: 0.8rem; color: var(--text-gray); }

        /* Ensure the pricing grid items have equal height */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            align-items: stretch; /* Makes all cards in a row the same height */
        }

        .price-card {
            display: flex;
            flex-direction: column;
            height: 100%; /* Ensures the card fills the grid space */
        }

        .price-card .btn-container {
            margin-top: auto; /* This is the magic: it pushes the button to the bottom */
            padding-top: 20px;
        }

        /* These stay for absolute positioning of the tag */
        .featured-tag {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Contact Section */
        .contact-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            background: var(--card-bg);
            padding: 50px;
            border-radius: 20px;
        }
        .contact-info i { color: var(--primary-blue); margin-right: 15px; margin-bottom: 25px; }
        input, textarea {
            width: 100%; padding: 15px; background: var(--dark-bg); border: 1px solid var(--accent-gray);
            color: white; margin-bottom: 15px; border-radius: 5px;
        }

        footer {
            padding: 50px 10%;
            background: #000;
            text-align: center;
            border-top: 1px solid var(--accent-gray);
        }
        .social-icons { margin: 20px 0; font-size: 1.5rem; }
        .social-icons i { margin: 0 15px; cursor: pointer; transition: 0.3s; }
        .social-icons i:hover { color: var(--primary-blue); }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .contact-box { grid-template-columns: 1fr; }
            .hero-content h1 { font-size: 2.8rem; }
        }

        /* Default star color */
        .star-rating label {
            color: #222; /* Dark gray for unselected */
        }

        /* Hover & Selection Logic */
        /* When a radio is checked, light up that star and all previous siblings */
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #00d4ff !important;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
        }

        /* Ensure that if we hover over a lower star while a higher one is checked, it follows the hover */
        .star-rating input:checked + label:hover,
        .star-rating input:checked ~ label:hover,
        .star-rating input:checked ~ label:hover ~ label,
        .star-rating label:hover ~ input:checked ~ label {
            color: #00d4ff;
        }

        /* Feedback  */
        .feedback-container {
            background-color: var(--dark-bg) !important;
        }

        .feedback-card-wrap {
            background: var(--card-bg) !important;
            border: 1px solid var(--accent-gray) !important;
        }

        .feedback-table th {
            background: #0a0a0a !important;
            color: var(--primary-blue) !important;
            border-bottom: 2px solid var(--accent-gray) !important;
        }

        .feedback-table td {
            border-bottom: 1px solid var(--accent-gray) !important;
            color: var(--text-white) !important;
        }

        .feedback-msg-box {
            background: #080808 !important;
            border: 1px solid var(--accent-gray) !important;
            color: var(--text-gray) !important;
        }

        .feedback-table tr:hover {
            background: rgba(0, 212, 255, 0.05) !important;
        }

        .payment-card:hover {
            border-color: var(--primary-blue) !important;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.1);
        }

    </style>
</head>
<body>

    <nav>
        <div class="logo">GYM<span>SHARK</span></div>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#trainers">Trainers</a></li>
            <li><a href="#schedule">Schedule</a></li>
            <li><a href="#membership">Pricing</a></li>
            <li><a href="contact-us.php">Contact</a></li>
        </ul>

        <div class="auth-section">
            <?php if (isset($_SESSION['customerID']) && !empty($customer)): ?>

                <?php
                    // Profile image fallback
                    $imageFileName = !empty($customer['customerProfile'])
                        ? $customer['customerProfile']
                        : '../admin/default-profile.jpg';

                    $newPath = preg_replace('/^\./', '', $imageFileName);
                ?>

                <a href="./edit-profile.php?id=<?php echo $customer['customerID']; ?>" class="profile-link">
                    <img src="<?php echo $newPath; ?>" alt="Profile" class="profile-avatar">
                </a>

                <a href="./login/logout.php" title="Logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>

            <?php else: ?>
                <a href="./login/login.php" class="join-btn">Join Now</a>
            <?php endif; ?>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="hero-content">
            <h1 data-aos="fade-up">EVOLVE BEYOND <br><span class="highlight">YOUR LIMITS</span></h1>
            <p>Experience the most advanced training facility in the city. Professional coaching, pro-grade equipment, and a culture of absolute discipline.</p>
            <div class="hero-btns">
                <a href="#membership" class="btn btn-primary">Start Your Journey</a>
            </div>
        </div>
    </section>  

    <section id="trainers" class="section">
        <div class="section-title">
            <h2>The Elite Staff</h2>
            <p>Our world-class trainers are here to guide your transformation.</p>
        </div>

        <div class="trainer-grid">
            <?php if (!empty($trainers)): ?>
                <?php foreach ($trainers as $trainer): ?>
                    <div class="trainer-card">
                        <?php if (!empty($trainer['trainerProfile'])): ?>
                            <img src="./admin/<?php echo htmlspecialchars($trainer['trainerProfile']); ?>" 
                                 class="trainer-img" 
                                 alt="<?php echo htmlspecialchars($trainer['trainerName']); ?>">
                        <?php else: ?>
                            <img src="./admin/default-profile.jpg" 
                             class="trainer-img" 
                             alt="<?php echo htmlspecialchars($trainer['trainerName']); ?>">
                        <?php endif; ?>
                    
                        <div class="trainer-info">
                            <h3><?php echo htmlspecialchars($trainer['trainerName']); ?></h3>
                            <p><?php echo htmlspecialchars($trainer['specialization']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No trainers found.</p>
                <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>
            <?php endif; ?>
        </div>
    </section>
    
    

    <section id="schedule" class="section" style="background: #080808;">
        <div class="section-title">
            <h2>Weekly Schedule</h2>
            <p>High-energy group sessions available daily.</p>
        </div>
        <div class="schedule-container">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Monday</th>
                        <th>Wednesday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>06:00 AM</td>
                        <td><span class="class-name">Morning Shred</span><span class="class-time">Alex</span></td>
                        <td><span class="class-name">Morning Shred</span><span class="class-time">Alex</span></td>
                        <td><span class="class-name">Yoga Flow</span><span class="class-time">Sarah</span></td>
                        <td><span class="class-name">Open Gym</span></td>
                    </tr>
                    <tr>
                        <td>12:00 PM</td>
                        <td><span class="class-name">Power Hour</span><span class="class-time">Marcus</span></td>
                        <td><span class="class-name">Boxing</span><span class="class-time">Guest</span></td>
                        <td><span class="class-name">Power Hour</span><span class="class-time">Marcus</span></td>
                        <td><span class="class-name">HIIT Blast</span><span class="class-time">Sarah</span></td>
                    </tr>
                    <tr>
                        <td>06:00 PM</td>
                        <td><span class="class-name">Night Pump</span><span class="class-time">Marcus</span></td>
                        <td><span class="class-name">Night Pump</span><span class="class-time">Marcus</span></td>
                        <td><span class="class-name">Core Focus</span><span class="class-time">Sarah</span></td>
                        <td><span class="class-name">Recovery Lab</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="membership" class="section" style="background-color: #050505; color: #ffffff; padding: 100px 0;">
        <div class="section-title" style="text-align: center; margin-bottom: 60px;">
            <h2 style="font-family: 'Orbitron', sans-serif; font-size: 2.5rem; text-transform: uppercase;">
                Membership <span style="color: #00d4ff;">Tiers</span>
            </h2>
            <p style="color: #b0b0b0; margin-top: 10px;">Commit to your growth with our transparent pricing.</p>
        </div>

        <div class="pricing-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; padding: 0 10%; max-width: 1400px; margin: 0 auto;">
    
            <?php if (!empty($plans)): ?>
                <?php foreach ($plans as $plan): ?>
                    <?php 
                        $isFeatured = (int)$plan['isPopular'] === 1; 
                    ?>
            
                    <div class="price-card" 
                         style="background: #111111; padding: 40px; border-radius: 15px; text-align: center; border: 2px solid <?php echo $isFeatured ? '#00d4ff' : '#1a1a1a'; ?>; position: relative; display: flex; flex-direction: column;">
    
                        <?php if ($isFeatured): ?>
                            <div class="featured-tag" style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #00d4ff; color: #000; padding: 5px 20px; border-radius: 20px; font-weight: 900; font-size: 0.75rem;">
                                MOST POPULAR
                            </div>
                        <?php endif; ?>

                        <h3 style="font-family: 'Orbitron', sans-serif; margin-bottom: 20px; font-size: 1.5rem;">
                            <?php echo htmlspecialchars($plan['planName']); ?>
                        </h3>

                        <div class="cost" style="font-size: 3rem; font-weight: 700; margin-bottom: 20px;">
                            $<?php echo number_format($plan['price'], 0); ?><span style="font-size: 1.2rem; color: #b0b0b0;"><?php echo htmlspecialchars($plan['duration']); ?></span>
                        </div>

                        <ul style="list-style: none; margin-bottom: 20px; color: #b0b0b0; text-align: left; padding: 0;">
                            <?php 
                                $features = explode(',', $plan['description']); 
                                foreach($features as $feature): 
                            ?>
                                <li style="margin-bottom: 12px; font-size: 0.95rem; display: flex; align-items: center;">
                                    <i class="fas fa-check" style="color: #00d4ff; margin-right: 10px;"></i>
                                    <?php echo htmlspecialchars(trim($feature)); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="btn-container" style="margin-top: auto;">
                            <a href="./join/record-membership.php?planID=<?php echo $plan['planID']; ?>" 
                               class="btn btn-primary" 
                               style="display: block; width: 100%; padding: 15px 0; background: '#00d4ff'; color: '#000'; ?>; text-decoration: none; font-weight: 900; border: 2px solid #00d4ff; text-align: center; text-transform: uppercase;">
                                Join <?php echo htmlspecialchars($plan['planName']); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; grid-column: 1 / -1; padding: 50px; background: #111; border-radius: 15px;">
                    <p style="color: #b0b0b0;">No membership plans are currently available. Check back soon!</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <section class="section feedback-container" style="padding-top: 50px;">
    <div class="section-title">
        <h2>Customer <span class="highlight">Feedbacks</span></h2>
        <div class="flex-grow h-px bg-slate-200" style="width: 100px; margin: 20px auto; background: var(--accent-gray);"></div>
    </div>

    <div class="feedback-card-wrap rounded-2xl shadow-2xl overflow-hidden" style="max-width: 90%; margin: 0 auto;">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse feedback-table">
                <thead>
                    <tr>
                        <th class="p-5 text-xs font-bold uppercase tracking-widest" style="font-family: 'Orbitron', sans-serif;">Customer Info</th>
                        <th class="p-5 text-xs font-bold uppercase tracking-widest" style="font-family: 'Orbitron', sans-serif;">Rating</th>
                        <th class="p-5 text-xs font-bold uppercase tracking-widest" style="font-family: 'Orbitron', sans-serif;">Review</th>
                        <th class="p-5 text-xs font-bold uppercase tracking-widest" style="font-family: 'Orbitron', sans-serif;">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php if (!empty($all_feedbacks)): ?>
                        <?php foreach ($all_feedbacks as $item): ?>
                            <tr class="transition-colors">
                                <td class="p-5">
                                    <div class="font-bold" style="color: var(--text-white);"><?php echo htmlspecialchars($item['fullName']); ?></div>
                                    <div class="text-[12px]" style="color: var(--text-gray);"><?php echo htmlspecialchars($item['email']); ?></div>
                                </td>

                                <td class="p-5">
                                    <div class="flex flex-col gap-1">
                                        <div class="star-display">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                                <i class="fas fa-star <?php echo ($i <= $item['rating']) ? 'star-active' : 'star-inactive'; ?>" 
                                                   style="color: <?php echo ($i <= $item['rating']) ? 'var(--primary-blue)' : '#222'; ?>;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase tracking-widest" style="color: var(--text-gray);">
                                            Score: <?php echo $item['rating']; ?>/5
                                        </span>
                                    </div>
                                </td>

                                <td class="p-5">
                                    <div class="feedback-msg-box p-4 rounded-lg">
                                        <p class="text-[14px] leading-relaxed italic">
                                            "<?php echo htmlspecialchars($item['feedback']); ?>"
                                        </p>
                                    </div>
                                </td>

                                <td class="p-5 whitespace-nowrap">
                                    <div class="text-[12px] font-bold" style="color: var(--primary-blue);">
                                        <?php echo date('M d, Y', strtotime($item['submissionDate'])); ?>
                                    </div>
                                    <div class="text-[11px] uppercase" style="color: var(--text-gray);">
                                        <?php echo date('h:i A', strtotime($item['submissionDate'])); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="p-16 text-center" style="color: var(--text-gray);">
                                <i class="fas fa-comment-slash block text-4xl mb-4" style="color: var(--accent-gray);"></i>
                                <p class="uppercase tracking-widest font-bold">No feedback received yet</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
        <div style="text-align: center; margin-top: 40px;">
            <a href="contact-us.php" class="join-btn" style="padding: 12px 35px;">Share Your Experience</a>
        </div>
    </section>

    <section id="payments" class="section" style="background: linear-gradient(to bottom, #050505, #0a0a0a);">
        <div class="section-title">
            <h2>Accepted <span class="highlight">Payments</span></h2>
            <p>Secure transfers via our authorized banking partners.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; padding: 0 5%; max-width: 1200px; margin: 0 auto;">
        
            <?php if (!empty($payment_accounts)): ?>
                <?php foreach ($payment_accounts as $pay): ?>
                    <div class="payment-card" style="background: linear-gradient(135deg, #111 0%, #1a1a1a 100%); border: 1px solid var(--accent-gray); padding: 30px; border-radius: 20px; position: relative; overflow: hidden; transition: var(--transition); group">
                    
                        <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: var(--primary-blue); opacity: 0.05; border-radius: 50%; filter: blur(40px);"></div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px;">
                            <div style="font-family: 'Orbitron', sans-serif; font-size: 1.2rem; color: var(--primary-blue); letter-spacing: 1px;">
                                <?php echo htmlspecialchars($pay['bankName']); ?>
                            </div>
                            <i class="fas fa-university text-2xl text-zinc-700"></i>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 10px; color: var(--text-gray); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px;">Account Number</label>
                            <div style="font-size: 1.4rem; font-weight: 700; letter-spacing: 3px; color: #fff;">
                                <?php echo htmlspecialchars($pay['accountNumber']); ?>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                            <div>
                                <label style="display: block; font-size: 10px; color: var(--text-gray); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px;">Account Holder</label>
                                <div style="font-weight: 600; text-transform: uppercase; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($pay['accountHolder']); ?>
                                </div>
                            </div>
                            <i class="fas fa-shield-alt" style="color: var(--primary-blue); opacity: 0.5;"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-gray); padding: 40px; border: 1px dashed var(--accent-gray); border-radius: 15px;">
                    No payment accounts currently displayed.
                </div>
            <?php endif; ?>

        </div>
    </section>

    <footer>
        <div class="logo">GYM<span>SHARK</span></div>
        <div class="social-icons">
            <i class="fab fa-instagram"></i>
            <i class="fab fa-facebook"></i>
            <i class="fab fa-youtube"></i>
            <i class="fab fa-tiktok"></i>
        </div>
        <p>&copy; 2025 GymShark Fitness Center. Built for the dedicated.</p>
    </footer>

    <script>
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('.section');
            sections.forEach(sec => {
                const top = window.scrollY;
                const offset = sec.offsetTop - 400;
                if (top >= offset) {
                    sec.style.opacity = '1';
                    sec.style.transform = 'translateY(0)';
                }
            });
        });
        
        window.addEventListener('DOMContentLoaded', () => {
            const notify = document.getElementById('notification-box');
            if (notify) {
                setTimeout(() => {
                    notify.style.opacity = '0';
                    setTimeout(() => notify.remove(), 500);
                }, 3000); 
            }
        });
    </script>
</body>
</html>