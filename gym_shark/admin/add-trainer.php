<?php

include('../connection.php'); 
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$is_editing = false;
$trainer = []; 
$page_title = "Add New Trainer";
$form_action = "trainer_processor.php"; 

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $trainerID = $_GET['id'];
    $is_editing = true;
    $page_title = "Edit Trainer (ID: {$trainerID})";
    
    $form_action = "trainer_processor.php?action=update&id={$trainerID}";
    
    $sql = "
        SELECT 
            trainerID, trainerName, gender, trainerEmail, trainerPhone, 
            specialization, hired_date, salary, status, trainerProfile
        FROM trainer 
        WHERE trainerID = ? 
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trainerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $trainer = $result->fetch_assoc();
    } else {
        $_SESSION['status_message'] = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>Trainer not found.</div>";
        header("Location: trainers-list.php");
        exit();
    }
    $stmt->close();
}
// ------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Management - Add Trainer</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
</head>
    <body class="bg-gray-50 flex h-screen font-sans antialiased text-gray-800">
    
        <?php include('sidebar.php'); ?>

        <main class="flex-grow p-6 overflow-y-auto">
            <header class="mb-6 pb-4 border-b" style="padding-left: 80px;">
                <h1 class="text-3xl font-bold"><?php echo $page_title; ?></h1>
            </header>

            <?php if (isset($_SESSION['status_message'])): ?>
                <?php echo $_SESSION['status_message']; unset($_SESSION['status_message']); ?>
            <?php endif; ?>

            <form id="add-trainer-form" class="bg-white p-8 rounded-xl shadow-lg" method="POST" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
            
                <h2 class="text-xl font-semibold mb-6 border-b pb-3">Trainer Personal & Professional Details</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

                    <?php if ($is_editing): ?>
                        <input type="hidden" name="trainerID" value="<?php echo htmlspecialchars($trainer['trainerID']); ?>">
                    <?php endif; ?>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                        <div class="flex items-center space-x-4 mt-2">
                            <?php 
                                $img_path = $trainer['profile_picture_path'] ?? '';
                                $is_img_visible = !empty($img_path) ? '' : 'hidden';
                                $is_icon_visible = empty($img_path) ? '' : 'hidden';
                            ?>
                            <div id="profile-picture-preview-container" class="w-24 h-24 rounded-full border-4 border-gray-200 bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                <i id="default-icon" data-lucide="user" class="w-12 h-12 text-gray-400 <?php echo $is_icon_visible; ?>"></i>
                                <img id="profile-picture-preview" 
                                     src="<?php echo htmlspecialchars($img_path); ?>" 
                                     alt="Profile Preview" 
                                     class="<?php echo $is_img_visible; ?> w-full h-full object-cover">
                            </div>
                        
                            <label class="cursor-pointer bg-blue-500 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-600 transition duration-150">
                                Choose File
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="hidden" onchange="previewImage(event)">
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="trainer_name" class="block text-sm font-medium text-gray-700 required-label">Full Name</label>
                        <input type="text" id="trainer_name" name="trainer_name" required 
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($trainer['trainerName'] ?? ''); ?>">
                    </div>
                
                    <div>
                        <label for="trainer_gender" class="block text-sm font-medium text-gray-700 required-label">Select Gender</label>
                        <div class="mt-1 relative">
                            <select id="trainer_gender" name="trainer_gender" required 
                                    class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 pr-10">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($trainer['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($trainer['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($trainer['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="trainer_email" class="block text-sm font-medium text-gray-700 required-label">Email Address</label>
                        <input type="email" id="trainer_email" name="trainer_email" required 
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($trainer['trainerEmail'] ?? ''); ?>">
                    </div>
                
                    <div>
                        <label for="trainer_phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="trainer_phone" name="trainer_phone" 
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($trainer['trainerPhone'] ?? ''); ?>">
                    </div>

                    <div>
                        <label for="specialization" class="block text-sm font-medium text-gray-700 required-label">Specialization</label>
                        <div class="mt-1 relative">
                            <select id="specialization" name="specialization" required 
                                    class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 pr-10">
                                <option value="">Choose Specialty</option>
                                <option value="Weight Training" <?php echo ($trainer['specialization'] ?? '') == 'Weight Training' ? 'selected' : ''; ?>>Weight Training</option>
                                <option value="Cardio/Endurance" <?php echo ($trainer['specialization'] ?? '') == 'Cardio/Endurance' ? 'selected' : ''; ?>>Cardio/Endurance</option>
                                <option value="Yoga/Pilates" <?php echo ($trainer['specialization'] ?? '') == 'Yoga/Pilates' ? 'selected' : ''; ?>>Yoga/Pilates</option>
                                <option value="Nutrition" <?php echo ($trainer['specialization'] ?? '') == 'Nutrition' ? 'selected' : ''; ?>>Nutrition</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="salary" class="block text-sm font-medium text-gray-700 required-label">Salary Rate ($)</label>
                        <input type="number" id="salary" name="salary" step="0.01" required 
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($trainer['salary'] ?? '500.00'); ?>">
                    </div>
                
                    <div>
                        <label for="date_hired" class="block text-sm font-medium text-gray-700 required-label">Date Hired</label>
                        <div class="mt-1 relative">
                            <input type="date" id="date_hired" name="date_hired" required 
                                   class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="<?php echo htmlspecialchars($trainer['hired_date'] ?? date('Y-m-d')); ?>">
                        </div>
                    </div>

                    <div>
                        <label for="trainer_status" class="block text-sm font-medium text-gray-700 required-label">Status</label>
                        <div class="mt-1 relative">
                            <select id="trainer_status" name="trainer_status" required 
                                    class="form-input block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 pr-10">
                                <option value="Active" <?php echo ($trainer['status'] ?? 'Active') == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo ($trainer['status'] ?? '') == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="Contract" <?php echo ($trainer['status'] ?? '') == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                            </select>
                        </div>
                    </div>
                
                </div> 

                <div class="mt-8 flex space-x-4">
                    <button type="submit" class="flex items-center space-x-2 px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-150">
                        <?php if ($is_editing): ?>
                            <i data-lucide="save" class="w-5 h-5"></i>
                            <span>Update Trainer</span>
                        <?php else: ?>
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                            <span>Add Trainer</span>
                        <?php endif; ?>
                    </button>

                    <button type="button" onclick="clearFormAndImage()" class="flex items-center space-x-2 px-6 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition duration-150">
                        <i data-lucide="refresh-ccw" class="w-5 h-5"></i>
                        <span>Clear Form</span>
                    </button>
                </div>
            </form>
        </main>

        <script>
            function previewImage(event) {
                const fileInput = event.target;
                const preview = document.getElementById('profile-picture-preview');
                const defaultIcon = document.getElementById('default-icon');

                if (fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        defaultIcon.classList.add('hidden');
                    }
                    reader.readAsDataURL(fileInput.files[0]);
                } else {
                    if (!document.querySelector('input[name="trainerID"]')) {
                        preview.classList.add('hidden');
                        preview.src = '#';
                        defaultIcon.classList.remove('hidden');
                    }
                }
            }

            function clearFormAndImage() {
                const form = document.getElementById('add-trainer-form');
                const preview = document.getElementById('profile-picture-preview');
                const defaultIcon = document.getElementById('default-icon');

                form.reset();

                preview.classList.add('hidden');
                preview.src = '#'; 
                defaultIcon.classList.remove('hidden');
            }

            document.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
            
                const currentPath = window.location.pathname.split('/').pop();
                const navItems = document.querySelectorAll('.nav-item');

                navItems.forEach(item => {
                    const itemPath = item.getAttribute('href').split('/').pop();
                
                    if (itemPath === currentPath) {
                        item.classList.add('active');
                    }
                });
            });
        </script>
    </body>
</html>