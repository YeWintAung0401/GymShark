<?php
include('connection.php'); 
session_start();

$message_type = ''; 
$message_text = '';
$redirect_url = '';

const MAX_ATTEMPTS = 3;
const LOCK_DURATION = 600; 

if (!isset($_SESSION['login_counter'])) {
    $_SESSION['login_counter'] = 0;
}

if (isset($_COOKIE['login_counter'])) { 
    $message_type = 'warning';
    $message_text = "Login temporarily locked. Please wait 10 minutes.";
    $redirect_url = 'loginTimer.php';
}

function handleFailedAttempt() {
    global $message_type, $message_text, $redirect_url;
    
    $_SESSION['login_counter']++;
    $counter = $_SESSION['login_counter'];

    if ($counter >= MAX_ATTEMPTS) {
        setcookie("login_counter", "c", time() + LOCK_DURATION, "/"); 
        
        $message_type = 'warning';
        $message_text = "Too many failed attempts. Please wait 10 minutes before trying again. Redirecting to timer...";
        $redirect_url = 'loginTimer.php'; 

    } else {
        $message_type = 'error';
        $message_text = "Invalid Password! Attempt $counter of " . MAX_ATTEMPTS . ".";
        $redirect_url = 'login.php'; 
    }
}

if (isset($_POST['btnLogin']) && !isset($_COOKIE['login_counter'])) {
    
    $email = trim($_POST['useremail']);
    $password = $_POST['password'];
    $login_attempted = true;

    $admin_query = "SELECT adminID, adminName, adminPhone, adminAddress, adminEmail, adminPassword, adminProfile FROM admin WHERE adminEmail = ?";
    $stmt = $conn->prepare($admin_query);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin_result = $stmt->get_result();
        $stmt->close();

        if ($admin_result->num_rows === 1) {
            $admin = $admin_result->fetch_assoc();
            $plain_pw = $admin['adminPassword'];
            $adminID = $admin['adminID'];

            if ($password === $plain_pw) {
                $_SESSION['admin'] = $admin;
                $_SESSION['adminID'] = $adminID;
                $_SESSION['login_counter'] = 0; 
                $message_type = 'success';
                $message_text = 'Admin Login Successful! Redirecting...';
                $redirect_url = '../admin/admin.php';
            } else {
                handleFailedAttempt();
            }
        }
    }

    $user_query = "SELECT customerID, customerName, customerPhone, customerAddress, customerEmail, customerPassword, customerProfile FROM customer WHERE customerEmail = ?";
    $stmt = $conn->prepare($user_query);
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $stmt->close();
        
        if ($user_result->num_rows === 1) {
            $user = $user_result->fetch_assoc();
            $hashed_pw = $user['customerPassword'];

                if (password_verify($password, $hashed_pw)) {
                    $_SESSION['customer'] = $user;
                    $_SESSION['customerID'] = $user['customerID']; 
                    $_SESSION['login_counter'] = 0; 
                    
                    $message_type = 'success';
                    $message_text = 'Login Successful! Welcome! Redirecting...';
                    $redirect_url = '../index.php'; 
                } else {
                    handleFailedAttempt();
                }
        }
    }

    if (empty($message_type)) {
        handleFailedAttempt();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymshark Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-image: url('https://placehold.co/1920x1080/0d111c/ffffff?text=GYM+TRAINING+SCENE');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            
            background-color: rgba(0, 0, 0, 0.75); 
            background-blend-mode: overlay; 
        }

        .login-card {
            background-color: rgba(255, 255, 255, 0.95);
            z-index: 10;
            position: relative;
        }
        
        .focus-ring-black:focus {
            --tw-ring-color: #000000;
            --tw-border-color: #000000;
        }

        .message-box {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            max-width: 350px;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            transition: opacity 0.3s;
        }
        .message-box.success { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .message-box.error { background-color: #fee2e2; color: #b91c1c; border: 1px solid #ef4444; }
        .message-box.warning { background-color: #fffbeb; color: #92400e; border: 1px solid #fbbf24; }
        .message-icon { margin-right: 0.75rem; }
        .close-btn { 
            margin-left: 1rem; 
            cursor: pointer; 
            font-weight: 700;
            opacity: 0.7;
            transition: opacity 0.2s;
            color: inherit;
        }
        .close-btn:hover { opacity: 1; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div id="message-container">
        <?php if (!empty($message_text)): ?>
            <div id="php-message-box" class="message-box <?php echo htmlspecialchars($message_type); ?>">
                <i class="message-icon fas 
                    <?php 
                        if ($message_type === 'success') echo 'fa-check-circle';
                        elseif ($message_type === 'error') echo 'fa-times-circle';
                        elseif ($message_type === 'warning') echo 'fa-exclamation-triangle';
                        else echo 'fa-info-circle';
                    ?>
                "></i>
                <span id="message-text" class="flex-grow"><?php echo htmlspecialchars($message_text); ?></span>
                <span class="close-btn" onclick="document.getElementById('php-message-box').remove();">&times;</span>
            </div>
        <?php endif; ?>
    </div>
 
    <div class="login-card w-full max-w-sm md:max-w-md rounded-xl shadow-2xl p-6 sm:p-8 space-y-6">
 
        <div class="text-center mb-8">
            <i class="fas fa-dumbbell text-4xl text-black mb-3"></i>
            <h1 class="text-3xl font-bold uppercase tracking-wider text-black">Sign In</h1>
            <p class="text-gray-500 text-sm mt-1">Access your account and track your progress.</p>
        </div>
 
        <form class="space-y-5" method="POST" action="login.php">
            
            <div class="form-group">
                <label for="useremail" class="block text-xs font-semibold uppercase text-gray-700 mb-1">Email Address</label>
                <div class="input-group flex items-center border-b border-gray-300 py-1">
                    <i class="fas fa-envelope text-gray-400 mr-3"></i>
                    <input type="email" id="useremail" name="useremail" placeholder="email@example.com" required 
                           class="w-full p-2 text-sm focus:ring-0 focus-border-black border-none bg-transparent outline-none transition duration-150 ease-in-out">
                </div>
            </div>

            <div class="form-group">
                <div class="flex justify-between items-end mb-1">
                    <label for="password" class="block text-xs font-semibold uppercase text-gray-700">Password</label>
                </div>
                
                <div class="input-group flex items-center border-b border-gray-300 py-1">
                    <i class="fas fa-lock text-gray-400 mr-3"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required
                           class="w-full p-2 text-sm focus:ring-0 focus-border-black border-none bg-transparent outline-none transition duration-150 ease-in-out">
                    <i class="fas fa-eye text-gray-400 cursor-pointer hover:text-black ml-3" id="toggle-pw"></i>
                </div>
            </div>
 
            <div class="text-right">
                <a href="forgotPassword.php" class="text-xs font-semibold text-gray-500 hover:text-black transition duration-150">Forgot your password?</a>
            </div>
 
            <button type="submit"
                    name="btnLogin"
                    id="login-btn"
                    class="w-full py-3 px-4 bg-black text-white font-bold text-base uppercase rounded-lg shadow-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 transition duration-150">
                LOG IN
            </button>
 
        </form>
 
        <div class="pt-6 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-500">
                Don't have an account?
                <a href="register.php" class="font-bold text-black hover:underline">Create Account</a>
            </p>
        </div>
 
    </div>
 
    <script>
        document.getElementById('toggle-pw').addEventListener('click', function () {
            const pwInput = document.getElementById('password');
            const icon = this;
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        const messageBox = document.getElementById('php-message-box');
        const redirectUrl = "<?php echo $redirect_url; ?>";
        const messageType = "<?php echo $message_type; ?>";
        
        if (messageBox && redirectUrl && (messageType === 'success' || messageType === 'warning')) {
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1500); 
        }
        
    </script>
</body>
</html>