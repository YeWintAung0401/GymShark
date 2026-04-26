<?php

include('../connection.php'); 
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$status_message = null;
if (isset($_GET['status']) && isset($_GET['message'])) {
    $status = $_GET['status'];
    $message = $_GET['message'];
    
    $message_map = [
        'Plan_Added' => 'Successfully registered a new membership plan.',
        'Plan_Updated' => 'Membership plan details updated successfully.',
        'Plan_Deleted' => 'Membership plan deleted successfully.',
        'Invalid_Input' => 'Error: Please ensure all required fields are filled correctly.',
        'Database_Error' => 'A database operation error occurred.',
    ];

    $display_message = $message_map[$message] ?? str_replace('_', ' ', $message);

    if ($status === 'success') {
        $status_message = ['type' => 'success', 'text' => $display_message, 'bg' => 'bg-green-100', 'text_color' => 'text-green-700', 'icon' => 'fas fa-check-circle'];
    } elseif ($status === 'error') {
        $status_message = ['type' => 'error', 'text' => $display_message, 'bg' => 'bg-red-100', 'text_color' => 'text-red-700', 'icon' => 'fas fa-times-circle'];
    }
}
$plans = [];
try {
    $sql = "SELECT 
                planID, 
                planName, 
                price, 
                duration, 
                description, 
                isPopular
            FROM plan ORDER BY planID ASC";
    
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
} catch (\Exception $e) {
    $error_message = "Error fetching trainers: " . $e->getMessage();
}



?>

<!DOCTYPE html>         
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management - Plans</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen font-sans antialiased text-gray-800">
    <?php include('sidebar.php'); ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <header class="flex justify-between items-center p-4 bg-white shadow-md z-30" style="padding-left: 80px;">
            <h2 class="text-2xl font-bold text-gray-800">Membership Plan Management</h2>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 md:p-8">

            <?php if ($status_message): ?>
            <div id="statusMessage" class="p-4 mb-6 rounded-lg <?php echo $status_message['bg']; ?> <?php echo $status_message['text_color']; ?> flex items-center shadow-md transition-all duration-300">
                <i class="<?php echo $status_message['icon']; ?> mr-3 text-lg"></i>
                <p class="font-medium"><?php echo htmlspecialchars($status_message['text']); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-800">Active Membership Plans (<?php echo count($plans); ?>)</h3>
                <button onclick="openModal('register')" class="py-2 px-4 bg-black text-white font-medium rounded-lg hover:bg-gray-800 transition crud-btn">
                    <i class="fas fa-plus mr-2"></i> Add New Plan
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                
                <?php if (empty($plans)): ?>
                <div class="lg:col-span-3 xl:col-span-4 p-6 text-center text-gray-500 bg-white rounded-xl shadow-lg">
                    No membership plans found. Click "Add New Plan" to create one.
                </div>
                <?php endif; ?>

                <?php foreach ($plans as $plan): ?>
                <div class="bg-white rounded-xl shadow-xl overflow-hidden transform hover:scale-[1.02] transition duration-300 border-t-4 border-black">
                    
                    <div class="p-6">
                        <span class="text-xs font-semibold uppercase text-gray-500">Plan ID: #<?php echo htmlspecialchars($plan['planID']); ?></span>
                        <h4 class="text-2xl font-bold text-gray-900 mt-1 mb-3 truncate">
                            <?php echo htmlspecialchars($plan['planName']); ?>
                        </h4>
                        
                        <div class="flex items-baseline justify-between mb-4">
                            <p class="text-4xl font-extrabold text-red-600">
                                $<?php echo number_format($plan['price'], 2); ?>
                            </p>
                            <p class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                <?php 
                                    echo htmlspecialchars($plan['duration']); 
                                ?>
                            </p>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4 h-12 overflow-hidden">
                            <?php echo htmlspecialchars($plan['description']); ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 p-4 border-t flex justify-around">
                        <button 
                            onclick="openModal('update', <?php echo htmlspecialchars(json_encode($plan)); ?>)"
                            class="text-blue-600 hover:text-blue-800 font-semibold transition flex items-center space-x-1" title="Edit Plan">
                            <i class="fas fa-edit text-sm"></i> <span>Edit</span>
                        </button>
                        <button 
                            onclick="handleDelete(<?php echo $plan['planID']; ?>)"
                            class="text-red-600 hover:text-red-800 font-semibold transition flex items-center space-x-1" title="Remove Plan">
                            <i class="fas fa-trash-alt text-sm"></i> <span>Remove</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                
            </div>

        </main>
    </div>

    <!-- 3. CRUD Modal (Register/Edit Form) -->
    <div id="planModal" class="modal fixed inset-0 z-50 flex items-center justify-center p-4 hidden modal-overlay">
        <div class="modal-content bg-white w-full max-w-lg rounded-xl shadow-2xl p-6 relative">
        
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </button>
        
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Plan Details</h2>

            <form id="planForm" method="POST" action="add_plan.php" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="register">
                <input type="hidden" name="planID" id="planID">
            
                <div>
                    <label for="planName" class="block text-sm font-medium text-gray-700">Plan Name</label>
                    <input type="text" name="planName" id="planName" required placeholder="e.g., Premium Plus" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 p-2 border">
                </div>
            
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                        <input type="number" step="0.01" name="price" id="price" required min="0" placeholder="59.99" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 p-2 border">
                    </div>
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700">Duration</label>
                        <select name="duration" id="duration" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 p-2 border bg-white">
                            <option value="" disabled selected>Select duration</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div>
                        <span class="block text-sm font-semibold text-gray-700">Most Popular</span>
                        <span class="text-xs text-gray-500">Highlight this plan on the frontend</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="isPopular" id="isPopularToggle" value="1" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                    </label>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description (comma separated)</label>
                    <textarea name="description" id="description" rows="3" required placeholder="Access to gym, Personal trainer, Weekly yoga" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 p-2 border"></textarea>
                </div>
            
                <button type="submit" id="submitBtn" class="w-full py-3 px-4 bg-black text-white font-bold rounded-lg hover:bg-gray-800 transition crud-btn mt-6">
                    Save Plan
                </button>
            </form>
        </div>
    </div>


    <script>
        lucide.createIcons();

        const statusMessage = document.getElementById('statusMessage');

        if (statusMessage) {
            const clearUrlParameters = () => {
                if (window.history.replaceState) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('status');
                    url.searchParams.delete('message');
            
                    window.history.replaceState({path: url.href}, '', url.href);
                }
            };

            setTimeout(() => {
                statusMessage.style.transition = 'opacity 0.3s ease-out';
                statusMessage.style.opacity = '0'; 
        
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                    clearUrlParameters(); 
            
                }, 300); 
        
            }, 3000); 
        }

        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('-translate-x-full');
            });
        }


        const modal = document.getElementById('planModal');
        const modalTitle = document.getElementById('modalTitle');
        const planForm = document.getElementById('planForm');
        const formAction = document.getElementById('formAction');
        const planIDInput = document.getElementById('planID');
        const submitBtn = document.getElementById('submitBtn');
        const planNameInput = document.getElementById('planName');
        const priceInput = document.getElementById('price');
        const durationInput = document.getElementById('duration');
        const descriptionInput = document.getElementById('description');

        function openModal(mode, planData = {}) {
             planForm.reset(); 

            if (mode === 'register') {
                modalTitle.textContent = 'Register New Plan';
                formAction.value = 'register';
                planIDInput.value = '';
                submitBtn.textContent = 'Save Plan';
                if (isPopularToggle) isPopularToggle.checked = false;
            } else if (mode === 'update') {
                modalTitle.textContent = 'Edit Plan (ID: ' + planData.planID + ')';
                formAction.value = 'update';
                planIDInput.value = planData.planID;
                submitBtn.textContent = 'Update Plan';
                
                planNameInput.value = planData.planName || '';
                priceInput.value = planData.price || 0.00;
                durationInput.value = planData.duration || '';
                descriptionInput.value = planData.description || '';
                if (isPopularToggle) {
                    isPopularToggle.checked = (planData.isPopular == 1);
                }
            }
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        function handleDelete(planId) {
            if (confirm(`Are you sure you want to delete Plan ID ${planId}? This action cannot be undone.`)) {
        
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = 'add_plan.php'; 
        
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'planID';
                idInput.value = planId;

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                tempForm.appendChild(idInput);
                tempForm.appendChild(actionInput);
                document.body.appendChild(tempForm);
                tempForm.submit();
            }
        }
    </script>
</body>