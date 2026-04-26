<?php 
include('../connection.php'); 
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$customers = [];
try {
    $sql = "SELECT 
                customerID, 
                customerName, 
                customerEmail, 
                customerPhone, 
                customerAddress, 
                gender, 
                customerProfile 
            FROM customer ORDER BY customerID ASC";
    
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
} catch (\Exception $e) {
    $error_message = "Error fetching customers: " . $e->getMessage();
}

$imgSrc = !empty($customer['customerProfile']) 
    ? $customer['customerProfile'] 
    : './admin/default-profile.jpg'; 

$newPath = preg_replace('/^\./', '', $imgSrc);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management - Customer List</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 flex h-screen font-sans antialiased text-gray-800">
    
    <?php include('sidebar.php'); ?>

    <main class="flex-grow p-6 overflow-y-auto">
        <header class="mb-6 pb-4 border-b flex justify-between items-center" style="padding-left: 80px;">
            <h1 class="text-3xl font-bold italic tracking-tighter">CUSTOMER <span class="text-red-600">LIST</span></h1>
            <a href="../login/register.php" class="flex items-center space-x-2 px-4 py-2 bg-black text-white font-semibold rounded-lg shadow-md hover:bg-gray-800 transition duration-150">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                <span>Add New Customer</span>
            </a>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="flash-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="flash-message bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No customers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($customer['customerID']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <?php if (!empty($customer['customerProfile'])): ?>
                                            <img class="h-10 w-10 rounded-full object-cover mr-3" src="<?php echo htmlspecialchars($customer['customerProfile']); ?>" alt="Profile">
                                        <?php else: ?>
                                            <i data-lucide="user" class="h-10 w-10 text-gray-400 rounded-full bg-gray-100 p-1 mr-3"></i>
                                        <?php endif; ?>

                                        
                                        <?php echo htmlspecialchars($customer['customerName']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($customer['customerEmail']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($customer['customerPhone']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate">
                                    <?php echo htmlspecialchars($customer['customerAddress']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 max-w-xs truncate">
                                    <?php echo htmlspecialchars($customer['gender']); ?>
                                </td>
                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($customer['customerID'])); ?>)" 
                                            class="text-blue-600 hover:text-blue-900 mx-2 transition">
                                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                                    </button>
                                    <button onclick="if(confirm('Delete customer <?php echo htmlspecialchars($customer['customerName']); ?>?')) { deleteCustomer(<?php echo $customer['customerID']; ?>) }" 
                                            class="text-red-600 hover:text-red-900 transition">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function deleteCustomer(id) {
            window.location.href = 'customer_management.php?action=delete&id=' + id;
        }

        function openEditModal(id) {
            window.location.href = 'edit-customer.php?id=' + id;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.flash-message');
        
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.transition = "opacity 0.5s ease";
                    message.style.opacity = "0";
                
                    setTimeout(() => message.remove(), 500);
                }, 3000); 
            });
        });
    </script>
</body>
</html>