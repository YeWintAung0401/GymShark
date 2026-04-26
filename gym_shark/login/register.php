<?php 
include('connection.php');
session_start();

if (isset($_SESSION['registration_errors']) && is_array($_SESSION['registration_errors'])) {
    echo "<script>";
    foreach ($_SESSION['registration_errors'] as $error) {
        echo "alert('" . addslashes($error) . "');";
    }
    echo "</script>";
    unset($_SESSION['registration_errors']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymshark Create Account</title>

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
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div class="login-card w-full max-w-sm md:max-w-md rounded-xl shadow-2xl p-6 sm:p-8 space-y-6">

        <div class="text-center space-y-2">
            <i class="fas fa-dumbbell text-4xl text-black mb-3"></i>
            <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
            <p class="text-gray-500 text-sm">Join the Gymshark community to start your journey.</p>
        </div>

        <div id="message-box" class="hidden p-3 rounded-lg text-sm font-medium text-center" role="alert"></div>

        <div id="register-view">
            <form class="space-y-5" id="registrationForm" method="POST" action="register_process.php">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="register-name" name="register-name" required
                           placeholder="Please Enter Your Name"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus-ring-black transition text-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="register-email" name="register-email" required
                           placeholder="Please Enter Your Email"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus-ring-black transition text-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" id="register-phone" name="register-phone" required
                           placeholder="Please Enter Your Phone Number"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus-ring-black transition text-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" id="register-address" name="register-address" required
                           placeholder="Please Enter Your Address"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus-ring-black transition text-gray-900">
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select id="gender" name="gender" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black transition text-gray-900 bg-white">
                        <option value="" disabled selected>Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer-not-to-say">Prefer not to say</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 bg-white">
                        <input type="password" id="register-password" name="register-password" required
                               placeholder="********"
                               class="w-full py-3 focus:ring-0 border-none outline-none text-gray-900">
                        <i class="fas fa-eye text-gray-500 cursor-pointer" id="toggle-reg-pw"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="flex items-center border border-gray-300 rounded-lg px-3 bg-white">
                        <input type="password" id="register-confirm-password" name="register-confirm-password" required
                               placeholder="********"
                               class="w-full py-3 focus:ring-0 border-none outline-none text-gray-900">
                        <i class="fas fa-eye text-gray-500 cursor-pointer" id="toggle-reg-confirm"></i>
                    </div>
                </div>

                <button type="submit" id="register-btn" name="register-btn"
                        class="w-full py-3 px-4 bg-black text-white font-bold text-base uppercase rounded-lg shadow-md hover:bg-gray-800 transition">
                    CREATE ACCOUNT
                </button>
            </form>

            <div class="border-t border-gray-100 mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Already have an account?
                    <a href="login.php" class="font-bold text-black hover:underline">Sign In</a>
                </p>
            </div>
        </div>

    </div>

    <script>
    document.getElementById('toggle-reg-pw').addEventListener('click', function() {
        const passwordInput = document.getElementById('register-password');
        const icon = this;

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    document.getElementById('toggle-reg-confirm').addEventListener('click', function() {
        const confirmInput = document.getElementById('register-confirm-password');
        const icon = this;

        if (confirmInput.type === 'password') {
            confirmInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            confirmInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        let valid = true;

        const email = document.getElementById('register-email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            valid = false;
        }

        const password = document.getElementById('register-password').value;
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
        if (!passwordRegex.test(password)) {
            alert('Password must be at least 8 characters and include uppercase, lowercase, number, and special character.');
            valid = false;
        }

        const confirmPassword = document.getElementById('register-confirm-password').value;
        if (password !== confirmPassword) {
            alert('Passwords do not match');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
    </script>

</body>
</html>
