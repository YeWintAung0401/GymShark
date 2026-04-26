<?php
include('../connection.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $customerID = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM customer WHERE customerID = ?");
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    if (!$customer) {
        die("Customer not found.");
    }
} else {
    header("Location: view-customer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - GYMSHARK</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen font-sans antialiased text-gray-800">

    <?php include('sidebar.php'); ?>

    <main class="flex-grow p-8 overflow-y-auto">
        <header class="mb-8 flex justify-between items-center border-b pb-4" style="padding-left: 80px;">
            <div>
                <h1 class="text-3xl font-black italic uppercase">Edit <span class="text-red-600">Customer</span></h1>
                <p class="text-gray-600 mt-1">Modify customer details and update their profile information.</p>
            </div>
            <a href="view-customer.php" class="text-gray-600 hover:text-black flex items-center gap-2 font-semibold">
                <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to List
            </a>
        </header>

        <div class="max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden">
            <form action="customer_management.php?action=update" method="POST" enctype="multipart/form-data" class="p-8">
                <input type="hidden" name="customerID" value="<?php echo $customer['customerID']; ?>">
                <input type="hidden" name="existingProfile" value="<?php echo $customer['customerProfile']; ?>">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div class="md:col-span-1 flex flex-col items-center space-y-4 border-r pr-8">
                        <label class="block text-xs font-bold uppercase text-gray-400">Profile Picture</label>
                        <div class="relative group">
                            <?php 
                                $imgSrc = !empty($customer['customerProfile']) ? ltrim($customer['customerProfile']) : '../admin/default-profile.jpg';

                                
                            ?>
                            <img id="preview" src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                 class="w-40 h-40 rounded-full object-cover border-4 border-gray-100 shadow-lg" alt="Profile">
                            
                            <label for="file-upload" class="absolute bottom-2 right-2 bg-black text-white p-2 rounded-full cursor-pointer hover:bg-gray-800 transition shadow-md">
                                <i data-lucide="camera" class="w-5 h-5"></i>
                                <input id="file-upload" name="profile_picture" type="file" class="hidden" accept="image/*" onchange="previewImage(event)">
                            </label>
                        </div>
                        <p class="text-[10px] text-gray-400 text-center uppercase">Click the icon to upload a new image</p>
                    </div>

                    <div class="md:col-span-2 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Full Name</label>
                                <input type="text" name="customerName" required value="<?php echo htmlspecialchars($customer['customerName']); ?>"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Gender</label>
                                <select name="gender" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black outline-none transition">
                                    <option value="Male" <?php if($customer['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if($customer['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if($customer['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                                    <option value="Prefer not to say" <?php if($customer['gender'] == 'Prefer not to say') echo 'selected'; ?>>Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Email Address</label>
                                <input type="email" name="customerEmail" required value="<?php echo htmlspecialchars($customer['customerEmail']); ?>"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Phone Number</label>
                                <input type="text" name="customerPhone" value="<?php echo htmlspecialchars($customer['customerPhone']); ?>"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Address</label>
                            <textarea name="customerAddress" rows="3" 
                                      class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black outline-none transition"><?php echo htmlspecialchars($customer['customerAddress']); ?></textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-black text-white font-black py-4 rounded-xl hover:bg-gray-800 transition-all transform active:scale-95 shadow-lg uppercase tracking-widest">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        lucide.createIcons();

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('preview');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>