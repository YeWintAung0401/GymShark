<?php
session_start();

const LOCK_DURATION = 600; // 10 minutes (600 seconds)
const COOKIE_NAME = 'login_counter';

$message_type = 'warning';
$message_title = 'ACCOUNT LOCKED';
$remaining_seconds = 0;
$locked = false;

if (isset($_COOKIE[COOKIE_NAME])) {
    $locked = true;
    $message_text = "You have exceeded the maximum login attempts. You must wait before trying again.";

} else {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Locked - Wait Time</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-image: url('https://placehold.co/1920x1080/0d111c/ffffff?text=GYM+REST+SCENE');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0.75);
            background-blend-mode: overlay;
        }

        .lock-card {
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .timer-text {
            font-variant-numeric: tabular-nums; 
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="lock-card w-full max-w-md md:max-w-lg rounded-xl shadow-2xl p-8 sm:p-10 space-y-6 text-center">

        <div class="text-center mb-6">
            <i class="fas fa-lock text-5xl text-red-600 mb-4 animate-pulse"></i>
            <h1 class="text-3xl font-bold uppercase tracking-wider text-red-700"><?php echo htmlspecialchars($message_title); ?></h1>
            <p class="text-gray-600 text-base mt-2"><?php echo htmlspecialchars($message_text); ?></p>
        </div>

        <div class="bg-gray-100 p-6 rounded-lg border-2 border-red-200">
            <p class="text-sm font-semibold uppercase text-gray-500 mb-2">Time Remaining Until Unlock:</p>
            <div id="countdown" class="text-5xl font-extrabold text-black timer-text">
                00:00
            </div>
        </div>

        <button onclick="window.location.href='login.php'"
                id="redirect-btn"
                disabled
                class="w-full py-3 px-4 bg-gray-300 text-gray-600 font-bold text-base uppercase rounded-lg transition duration-150 cursor-not-allowed">
            Unlocked (Redirecting in 3s)
        </button>
        
        <div class="pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-500">
                Please ensure cookies are enabled to track the lock duration correctly.
            </p>
        </div>

    </div>

    <script>
        const cookieName = '<?php echo COOKIE_NAME; ?>';
        const lockDurationSeconds = <?php echo LOCK_DURATION; ?>;
        const countdownElement = document.getElementById('countdown');
        const redirectButton = document.getElementById('redirect-btn');
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        let timerInterval;
        let startTime = Date.now();
        let endTime = startTime + (lockDurationSeconds * 1000);

        function updateCountdown() {
            const cookieValue = getCookie(cookieName);
            if (!cookieValue) {
                clearInterval(timerInterval);
                countdownElement.textContent = "00:00";
                
                redirectButton.disabled = false;
                redirectButton.classList.remove('bg-gray-300', 'text-gray-600', 'cursor-not-allowed');
                redirectButton.classList.add('bg-black', 'text-white', 'hover:bg-gray-800');
                redirectButton.textContent = 'LOG IN NOW';
                
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000); 

                return;
            }

            const now = Date.now();
            const elapsed = Math.floor((now - startTime) / 1000);
            let remaining = lockDurationSeconds - elapsed;


            if (remaining <= 0) {
                document.cookie = `${cookieName}=; Max-Age=-99999999; path=/`;
                
                clearInterval(timerInterval);
                updateCountdown(); 
                return;
            }

            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;

            const displayMinutes = String(minutes).padStart(2, '0');
            const displaySeconds = String(seconds).padStart(2, '0');

            countdownElement.textContent = `${displayMinutes}:${displaySeconds}`;
        }

        updateCountdown();
        timerInterval = setInterval(updateCountdown, 1000);
    </script>
</body>
</html>