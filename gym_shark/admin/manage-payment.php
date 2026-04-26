<?php
include('../connection.php');
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}
$query = "SELECT * FROM payment ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Payments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-50 flex">
    <?php include('sidebar.php'); ?>

    <div class="main-content flex-1 p-8">
        <h1 class="text-2xl font-bold mb-6 pl-[50px]">Gym Banking Methods</h1>

        <?php if(isset($_SESSION['msg'])): ?>
            <div id="alert-msg" class="bg-green-100 border border-green-400 text-green-700 p-3 rounded mb-4 transition-opacity duration-500">
                <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-sm border mb-8">
            <form action="payment-process.php" method="POST" enctype="multipart/form-data" 
                  class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
        
                <input type="hidden" name="action" value="add_bank">
        
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Bank Name</label>
                    <input type="text" name="bankName" placeholder="Bank Name" required class="border p-2 rounded-lg outline-none focus:ring-2 focus:ring-black">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Account Holder</label>
                    <input type="text" name="accountHolder" placeholder="Account Holder" required class="border p-2 rounded-lg outline-none focus:ring-2 focus:ring-black">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Account Number</label>
                    <input type="text" name="accountNumber" placeholder="Account Number" required class="border p-2 rounded-lg outline-none focus:ring-2 focus:ring-black">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Bank Logo</label>
                    <input type="file" name="bankLogo" accept="image/*" required class="text-xs border p-1.5 rounded-lg bg-gray-50">
                </div>

                <button type="submit" class="bg-black text-white py-2.5 px-4 rounded-lg font-bold hover:bg-slate-800 transition-all active:scale-95">
                    Save Account
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
            <table class="w-full text-left">
                <thead class="bg-slate-100 font-bold text-slate-600 text-sm">
                    <tr>
                        <th class="p-4">Logo</th>
                        <th class="p-4">Bank & Holder</th>
                        <th class="p-4">Account No.</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-t">
                        <td class="p-4"><img src="<?= $row['bankLogo'] ?>" class="h-20 w-20 object-contain"></td>
                        <td class="p-4">
                            <div class="font-bold"><?= $row['bankName'] ?></div>
                            <div class="text-xs text-slate-400"><?= $row['accountHolder'] ?></div>
                        </td>
                        <td class="p-4 font-mono"><?= $row['accountNumber'] ?></td>
                        <td class="p-4 text-center">
                            <a href="payment-process.php?delete=<?= $row['id'] ?>" class="text-red-500 font-bold" onclick="return confirm('Delete this bank?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const alertMsg = document.getElementById('alert-msg');
        if (alertMsg) {
            setTimeout(() => {
                alertMsg.style.transition = "opacity 0.5s ease";
                alertMsg.style.opacity = "0";
                setTimeout(() => alertMsg.remove(), 500);
            }, 3000);
        }
    </script>
</body>
</html>