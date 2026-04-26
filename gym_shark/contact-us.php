<?php
include('./connection.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$customerID = isset($_SESSION['customerID']);

$customer = [];
try {
    $sqlCustomer = "SELECT customerID, customerName, customerEmail, customerProfile FROM customer WHERE customerID = ?";
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullName'])) {
    if (!isset($_SESSION['customerID'])) {
        $_SESSION['error_message'] = "ACCESS DENIED: Please login to submit feedback.";
    } else {
        $customerID = $_SESSION['customerID'];
        $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $submitDate = date('Y-m-d H:i:s');

        if ($rating < 1 || $rating > 5) {
            $_SESSION['error_message'] = "VALIDATION ERROR: Please select a star rating.";
        } else {
            $stmt = $conn->prepare("INSERT INTO feedback (customerID, fullName, email, rating, feedback, submissionDate) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ississ", $customerID, $fullName, $email, $rating, $feedback, $submitDate);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "TRANSMISSION SUCCESSFUL: Thank you for your feedback.";
            } else {
                $_SESSION['error_message'] = "SYSTEM ERROR: Failed to log message.";
            }
            $stmt->close();
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | GymShark Yangon</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;900&family=Inter:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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

        
        .section { padding: 80px 10%; min-height: 50vh; display: flex; align-items: center; justify-content: center; }
        .contact-box { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 50px; 
            background: #111; 
            padding: 60px; 
            border-radius: 20px; 
            width: 100%;
            max-width: 1200px;
            border: 1px solid #222;
        }

        /* Star Rating Hover Effects */
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #00d4ff !important;
        }

        @media (max-width: 992px) {
            .contact-box { grid-template-columns: 1fr; padding: 30px; }
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
    </style>
</head>
<body>

    <nav>
        <div class="logo">GYM<span>SHARK</span></div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#trainers">Trainers</a></li>
            <li><a href="index.php#schedule">Schedule</a></li>
            <li><a href="index.php#membership">Pricing</a></li>
            <li><a href="contact-us.php">Contact</a></li>
        </ul>

        <div class="auth-section">
            <?php if (isset($_SESSION['customerID']) && !empty($customer)): ?>

                <?php
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

    <section id="contact" class="section">
        <div class="contact-box">
            
            <div class="contact-info">
                <h2 class="heading-font" style="font-size: 2.5rem; text-transform: uppercase; margin-bottom: 10px;">
                    Visit <span style="color: #00d4ff;">Us</span>
                </h2>
                <div style="width: 50px; height: 4px; background: #00d4ff; margin-bottom: 30px;"></div>
                
                <div style="margin-bottom: 15px;">
                    <a href="https://www.google.com/maps/search/?api=1&query=Sule+Road+and+Anawrahta+Road+Yangon" target="_blank" 
                       style="text-decoration: none; color: white; transition: color 0.3s; font-size: 1rem; display: flex; align-items: flex-start;" 
                       onmouseover="this.style.color='#00d4ff'" 
                       onmouseout="this.style.color='white'">
        
                        <i class="fas fa-location-dot" style="color: #00d4ff; margin-top: 5px; margin-right: 15px;"></i> 
        
                        <span>
                            14th Floor, GymShark,<br>
                            Sule Road & Anawrahta Road Corner, Yangon
                        </span>
                    </a>
                </div>
                
                <div style="margin-bottom: 20px; display: flex; align-items: center;">
                    <i class="fas fa-phone" style="color: #00d4ff; width: 30px; font-size: 1.2rem;"></i> 
                    <span>+95 9426661125</span>
                </div>
                
                <div style="margin-bottom: 20px; display: flex; align-items: center;">
                    <i class="fas fa-envelope" style="color: #00d4ff; width: 30px; font-size: 1.2rem;"></i> 
                    <span>info@gymshark-yangon.com</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column;">
                
                <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
                    <div id="notification">
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div style="background: rgba(0, 212, 255, 0.1); border-left: 4px solid #00d4ff; padding: 20px; margin-bottom: 20px;">
                                <i class="fas fa-check-circle" style="color: #00d4ff; margin-right: 10px;"></i>
                                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div style="background: rgba(255, 0, 0, 0.1); border-left: 4px solid #ff4d4d; padding: 20px; margin-bottom: 20px;">
                                <div style="margin-bottom: 15px;">
                                    <i class="fas fa-exclamation-triangle" style="color: #ff4d4d; margin-right: 10px;"></i>
                                    <?php echo $_SESSION['error_message']; ?>
                                </div>
                                <?php if (strpos($_SESSION['error_message'], 'ACCESS DENIED') !== false): ?>
                                    <a href="./login/login.php" style="display: inline-block; padding: 10px 25px; background: #ff4d4d; color: #fff; text-decoration: none; font-weight: bold; border-radius: 4px; font-size: 0.8rem; text-transform: uppercase;">
                                        Go to Login
                                    </a>
                                <?php endif; ?>
                                <?php unset($_SESSION['error_message']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <input type="text" name="fullName" placeholder="Full Name" 
                        value="<?php echo isset($customer['customerName']) ? htmlspecialchars($customer['customerName']) : ''; ?>"
                        <?php echo isset($customer['customerName']) ? 'readonly' : 'required'; ?>
                        style="background: #050505; border: 1px solid #222; color: <?php echo isset($customer['customerName']) ? '#888' : 'white'; ?>; padding: 18px; font-size: 1rem; border-radius: 8px;">
    
                    <input type="email" name="email" placeholder="Email Address" 
                        value="<?php echo isset($customer['customerEmail']) ? htmlspecialchars($customer['customerEmail']) : ''; ?>"
                        <?php echo isset($customer['customerEmail']) ? 'readonly' : 'required'; ?>
                        style="background: #050505; border: 1px solid #222; color: <?php echo isset($customer['customerEmail']) ? '#888' : 'white'; ?>; padding: 18px; font-size: 1rem; border-radius: 8px;">

                    <div>
                        <label class="heading-font" style="color: #00d4ff; display: block; margin-bottom: 12px; font-size: 0.75rem; letter-spacing: 2px;">RATE YOUR EXPERIENCE</label>
                        <div class="star-rating" style="display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 10px;">
                            <input type="radio" id="star5" name="rating" value="5" style="display: none;" />
                            <label for="star5" class="fas fa-star" style="font-size: 1.8rem; cursor: pointer; color: #333; transition: 0.3s;"></label>
                            
                            <input type="radio" id="star4" name="rating" value="4" style="display: none;" />
                            <label for="star4" class="fas fa-star" style="font-size: 1.8rem; cursor: pointer; color: #333; transition: 0.3s;"></label>
                            
                            <input type="radio" id="star3" name="rating" value="3" style="display: none;" />
                            <label for="star3" class="fas fa-star" style="font-size: 1.8rem; cursor: pointer; color: #333; transition: 0.3s;"></label>
                            
                            <input type="radio" id="star2" name="rating" value="2" style="display: none;" />
                            <label for="star2" class="fas fa-star" style="font-size: 1.8rem; cursor: pointer; color: #333; transition: 0.3s;"></label>
                            
                            <input type="radio" id="star1" name="rating" value="1" style="display: none;" checked/>
                            <label for="star1" class="fas fa-star" style="font-size: 1.8rem; cursor: pointer; color: #333; transition: 0.3s;"></label>
                        </div>
                    </div>

                    <textarea name="feedback" rows="5" placeholder="Your Feedback..."  required 
                        style="background: #050505; border: 1px solid #222; color: white; padding: 18px; resize: none; font-size: 1rem; border-radius: 8px;"></textarea>
                
                    <button type="submit" style="width: 100%; background: #00d4ff; color: #000; font-family: 'Orbitron', sans-serif; font-weight: 900; padding: 20px; border: none; cursor: pointer; text-transform: uppercase; font-size: 1rem; letter-spacing: 2px; border-radius: 8px; transition: 0.3s;">
                        Send Message
                    </button>
                </form>
            </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const alertBox = document.getElementById('notification');
        
            if (alertBox) {
                setTimeout(() => {
                    alertBox.style.opacity = '0';
                
                    setTimeout(() => {
                        alertBox.style.display = 'none';
                    }, 800); 
                
                }, 3000);
            }
        });
    </script>

</body>
</html>