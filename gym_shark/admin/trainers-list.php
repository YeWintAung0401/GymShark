<?php 
include('../connection.php'); 
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$trainers = [];
try {
    $sql = "SELECT 
                trainerID, 
                trainerName, 
                trainerEmail, 
                trainerPhone, 
                specialization, 
                salary, 
                status,
                trainerProfile 
            FROM trainer ORDER BY trainerName ASC";
    
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $trainers[] = $row;
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
    <title>Gym Management - Trainer List</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 flex h-screen font-sans antialiased text-gray-800">
    
    <?php include('sidebar.php'); ?>

    <main class="flex-grow p-6 overflow-y-auto">
        <header class="mb-6 pb-4 border-b flex justify-between items-center" style="padding-left: 80px;"> 
            <h1 class="text-3xl font-bold">Trainer List</h1>
            <a href="add-trainer.php" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-150">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                <span>Add New Trainer</span>
            </a>
        </header>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainer Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($trainers)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No trainers found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($trainers as $trainer): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($trainer['trainerID']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <?php if (!empty($trainer['trainerProfile'])): ?>
                                            <img class="h-8 w-8 rounded-full object-cover mr-3" src="<?php echo htmlspecialchars($trainer['trainerProfile']); ?>" alt="Profile">
                                        <?php else: ?>
                                            <i data-lucide="user" class="h-8 w-8 text-gray-400 rounded-full bg-gray-100 p-1 mr-3"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($trainer['trainerName']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($trainer['trainerEmail']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($trainer['trainerPhone']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$<?php echo number_format($trainer['salary'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo ($trainer['status'] == 'Active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo htmlspecialchars($trainer['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        onclick="openEditModal(<?php echo $trainer['trainerID']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mx-2"
                                        title="Edit Trainer">
                                        <i data-lucide="pencil" class="w-5 h-5"></i>
                                    </button>
                                    <button 
                                        onclick="if(confirm('Are you sure you want to delete <?php echo htmlspecialchars($trainer['trainerName']); ?>?')) { deleteTrainer(<?php echo $trainer['trainerID']; ?>) }" 
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete Trainer">
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

    <div id="editTrainerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden modal-overlay">
        <div class="modal-content bg-white w-full max-w-lg rounded-xl shadow-2xl p-6 relative">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Trainer Information</h2>

            <form id="editTrainerForm" method="POST" action="trainer_management.php?action=update" class="space-y-4" enctype="multipart/form-data"> 
                <input type="hidden" name="trainerID" id="edit_trainerID">
                
                <div>
                    <label for="edit_trainerName" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="trainerName" id="edit_trainerName" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_trainerEmail" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="trainerEmail" id="edit_trainerEmail" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                    <div>
                        <label for="edit_trainerPhone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="trainerPhone" id="edit_trainerPhone" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_specialization" class="block text-sm font-medium text-gray-700">Specialization</label>
                        <select name="specialization" id="edit_specialization" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                            <option value="Weight Training">Weight Training</option>
                            <option value="Cardio/Endurance">Cardio/Endurance</option>
                            <option value="Yoga/Pilates">Yoga/Pilates</option>
                            <option value="Nutrition">Nutrition</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_salary" class="block text-sm font-medium text-gray-700">Salary Rate ($)</label>
                        <input type="number" name="salary" id="edit_salary" step="0.01" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                </div>
                
                <div>
                    <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="edit_status" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Contract">Contract</option>
                    </select>
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                    <div class="flex items-center space-x-4 mt-2">
                        <div id="edit_image_container" class="w-24 h-24 rounded-full border-4 border-gray-200 bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                            
                            <input type="hidden" name="trainerProfile" id="edit_existing_image_path">
                            
                            <i id="edit_default_icon" data-lucide="user" class="w-12 h-12 text-gray-400"></i>
                            <img id="edit_profile_picture_preview" src="#" alt="Profile Preview" class="hidden w-full h-full object-cover">
                        </div>
        
                        <label class="cursor-pointer bg-blue-500 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-md hover:bg-blue-600 transition duration-150">
                            Choose New File
                            <input type="file" name="profile_picture" id="edit_profile_picture_file_input" accept="image/*" class="hidden" onchange="previewEditImage(event)">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Select a new file to change the image. Leave blank to keep the current image.</p>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition mt-6">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <script>
        function previewEditImage(event) {
            const fileInput = event.target;
            const preview = document.getElementById('edit_profile_picture_preview');
            const defaultIcon = document.getElementById('edit_default_icon');
        
            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    defaultIcon.classList.add('hidden');
                }
                reader.readAsDataURL(fileInput.files[0]);
            } else {
                const existingPath = document.getElementById('edit_existing_image_path').value;
                if (existingPath) {
                    preview.src = existingPath;
                    preview.classList.remove('hidden');
                    defaultIcon.classList.add('hidden');
                } else {
                    preview.classList.add('hidden');
                    preview.src = '#';
                    defaultIcon.classList.remove('hidden');
                }
            }
        }

        function openModal() {
             document.getElementById('editTrainerModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('editTrainerModal').classList.add('hidden');
        }
        
        function openEditModal(trainerID) {
            document.getElementById('edit_profile_picture_file_input').value = ''; 
            
            fetch('trainer_management.php?action=fetch&id=' + trainerID)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const trainer = data.trainer;
                        
                        document.getElementById('edit_trainerID').value = trainer.trainerID;
                        document.getElementById('edit_trainerName').value = trainer.trainerName;
                        document.getElementById('edit_trainerEmail').value = trainer.trainerEmail;
                        document.getElementById('edit_trainerPhone').value = trainer.trainerPhone;
                        document.getElementById('edit_specialization').value = trainer.specialization;
                        document.getElementById('edit_salary').value = trainer.salary; 
                        document.getElementById('edit_status').value = trainer.status;
                        
                        const preview = document.getElementById('edit_profile_picture_preview');
                        const defaultIcon = document.getElementById('edit_default_icon');
                        const pathInput = document.getElementById('edit_existing_image_path'); 
                        
                        const imagePath = trainer.trainerProfile || '';
                        pathInput.value = imagePath;
                        
                        if (imagePath) {
                            preview.src = imagePath;
                            preview.classList.remove('hidden');
                            defaultIcon.classList.add('hidden');
                        } else {
                            preview.classList.add('hidden');
                            preview.src = '#';
                            defaultIcon.classList.remove('hidden');
                        }

                        openModal();
                    } else {
                        alert('Failed to fetch trainer data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('An error occurred while fetching trainer details.');
                });
        }
        
        function deleteTrainer(trainerID) {
            window.location.href = 'trainer_management.php?action=delete&id=' + trainerID;
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        

    </script>
</body>
</html>