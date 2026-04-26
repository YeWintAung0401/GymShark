<?php
include('connection.php');
session_start();

$message_type = '';
$message_text = '';
$email = $_GET['email'] ?? ''; 

if (isset($_POST['btnUpdatePassword'])) {
    $email = trim($_POST['user_email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($email)) {
        $message_type = 'error';
        $message_text = "Please enter your email address.";
    } elseif ($new_password !== $confirm_password) {
        $message_type = 'error';
        $message_text = "Passwords do not match!";
    } else {
        $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE customer SET customerPassword = ? WHERE customerEmail = ?");
        $stmt->bind_param("ss", $hashed_pw, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message_type = 'success';
            $message_text = "Password updated successfully! Redirecting to login...";
            header("refresh:3;url=login.php");
        } else {
            $message_type = 'error';
            $message_text = "Update failed. Ensure the email is correct and a new password is used.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password | Gymshark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-black min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black uppercase tracking-tighter italic">Create New Password</h1>
            <p class="text-gray-500 text-sm mt-2">Updating account: <span class="font-bold text-black"><?php echo htmlspecialchars($email); ?></span></p>
        </div>

        <?php if ($message_text): ?>
            <div class="mb-4 p-3 rounded text-sm font-bold <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $message_text; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Email Address</label>
                <input type="email" name="user_email" required 
                       value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="Please enter your email"
                       class="w-full border-b-2 border-gray-200 py-3 focus:border-black outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">New Password</label>
                <div class="relative flex items-center">
                    <input type="password" name="new_password" id="new_password" required minlength="8"
                           class="w-full border-b-2 border-gray-200 py-3 focus:border-black outline-none transition"
                           placeholder="••••••••">
                    <i class="fas fa-eye absolute right-2 text-gray-400 cursor-pointer hover:text-black toggle-icon" data-target="new_password"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Confirm New Password</label>
                <div class="relative flex items-center">
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8"
                           class="w-full border-b-2 border-gray-200 py-3 focus:border-black outline-none transition"
                           placeholder="••••••••">
                    <i class="fas fa-eye absolute right-2 text-gray-400 cursor-pointer hover:text-black toggle-icon" data-target="confirm_password"></i>
                </div>
            </div>

            <button type="submit" name="btnUpdatePassword"
                    class="w-full py-4 bg-black text-white font-black uppercase tracking-widest hover:bg-zinc-800 transition shadow-xl mt-4">
                Update Password
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="login.php" class="text-xs font-bold text-gray-400 hover:text-black transition">
                <i class="fas fa-arrow-left mr-1"></i> BACK TO LOGIN
            </a>
        </div>
    </div>

    <script>
        const toggleIcons = document.querySelectorAll('.toggle-icon');

        toggleIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const pwInput = document.getElementById(targetId);

                if (pwInput.type === 'password') {
                    pwInput.type = 'text';
                    this.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    pwInput.type = 'password';
                    this.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    </script>
</body>
</html>