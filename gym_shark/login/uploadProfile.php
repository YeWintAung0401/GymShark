<?php
include('connection.php');
session_start();

define('UPLOAD_DIR', '../customer_profiles/'); 
define('DB_DIR', 'customer_profiles/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); 
$message_type = '';
$message_text = '';
$redirect_url = ''; 
$customerId = $_SESSION['customerID'] ?? null;

if (!$customerId) {
    $message_type = 'red';
    $message_text = 'Error: You must be logged in to upload a profile image.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customerId) {
    
    if (isset($_POST['skip'])) {
        $message_type = 'redirect';
        $message_text = 'Image upload skipped. Redirecting...';
        $redirect_url = '../index.php'; 
    } 
    
    elseif (isset($_POST['upload'])) {
        
        if (isset($_FILES['profile_image_file']) && $_FILES['profile_image_file']['error'] === UPLOAD_ERR_OK) {
            
            $file = $_FILES['profile_image_file'];
            $file_name = basename($file['name']);
            $file_type = $file['type'];
            $file_size = $file['size'];
            $file_tmp = $file['tmp_name'];
            
            $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];

            if (!array_key_exists($file_type, $allowedTypes)) {
                $message_type = 'red';
                $message_text = 'Error: Only JPG, PNG, and GIF files are allowed.';
            } elseif ($file_size > MAX_FILE_SIZE) {
                $message_type = 'red';
                $message_text = 'Error: File size must be under 5MB.';
            } else {
                
                $extension = $allowedTypes[$file_type];
                $new_file_name = uniqid('profile_', true) . '.' . $extension;
                $targetPath = UPLOAD_DIR . $new_file_name;

                if (!is_dir(UPLOAD_DIR)) {
                    if (!mkdir(UPLOAD_DIR, 0755, true)) {
                        $message_type = 'red';
                        $message_text = 'Error: Failed to create upload directory. Check server permissions.';
                    }
                }
                
                if (move_uploaded_file($file_tmp, $targetPath)) {
                    
                    $stmt = $conn->prepare("UPDATE customer SET customerProfile = ? WHERE customerID = ?");
                    
                    if ($stmt === false) {
                        $message_type = 'red';
                        $message_text = 'Database error: Could not prepare update statement.';
                    } else {
                        $stmt->bind_param("si", $targetPath, $customerId);
                        
                        if ($stmt->execute()) {
                            $message_type = 'redirect';
                            $message_text = "Profile successfully updated. Redirecting to home page...";
                            $redirect_url = '../index.php'; 
                        } else {
                            $message_type = 'red';
                            $message_text = 'Error executing database update: ' . $stmt->error;
                        }
                        $stmt->close();
                    }

                } else {
                    $message_type = 'red';
                    $message_text = 'Error: Failed to move the uploaded file. Check server permissions.';
                }
            }
            
        } elseif (isset($_POST['upload'])) {
             $message_type = 'red';
             $message_text = 'Please select a file to upload.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Image - Gymshark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .gymshark-btn {
            transition: background-color 0.2s, opacity 0.2s;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            border-radius: 0;
        }
        .gymshark-primary-btn {
            background-color: black;
            color: white;
            border: 1px solid black;
        }
        .gymshark-primary-btn:hover:not(:disabled) {
            background-color: #333;
        }
        .gymshark-primary-btn:disabled {
            background-color: #e5e5e5;
            border-color: #e5e5e5;
            color: #a3a3a3;
            cursor: not-allowed;
        }
        .gymshark-secondary-btn {
            background-color: white;
            color: black;
            border: 1px solid black;
        }
        .gymshark-secondary-btn:hover {
            background-color: #f5f5f5;
        }
        #profile-image-container {
            width: 150px;
            height: 150px;
            background-color: #f3f4f6; 
            cursor: pointer;
            border: 2px solid #e5e7eb; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        #profile-image {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            color: white;
            display: none; 
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            text-align: center;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans">
    
    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner mb-4"></div>
        <div class="text-xl font-bold uppercase tracking-wider mb-2">
            <i class="fas fa-check-circle text-green-400 mr-2"></i>
            Image Updated!
        </div>
        <p id="loading-message" class="text-gray-300 text-sm">Redirecting in a moment...</p>
    </div>

    <div id="content-container" class="w-full max-w-lg bg-white p-6 sm:p-10 shadow-xl border border-gray-200 flex flex-col items-center">

        <div class="text-center mb-10">
            <i class="fas fa-camera text-4xl text-black mb-3"></i>
            <h1 class="text-2xl font-bold uppercase tracking-wider text-black">Add a Profile Photo</h1>
            <p class="text-gray-500 text-sm mt-2">Show the community who you are. This step is optional.</p>
        </div>
        
        <?php if (!empty($message_text) && $message_type !== 'redirect'): ?>
            <div id="php-message-box" class="w-full mb-4 px-4 py-3 rounded text-sm 
                <?php 
                    if ($message_type === 'green') { 
                        echo 'bg-green-100 border border-green-400 text-green-700'; 
                    } elseif ($message_type === 'red') { 
                        echo 'bg-red-100 border border-red-400 text-red-700'; 
                    } elseif ($message_type === 'blue') { 
                        echo 'bg-blue-100 border border-blue-400 text-blue-700'; 
                    } 
                ?>
            " role="alert">
                <span id="message-text" class="block sm:inline"><?php echo htmlspecialchars($message_text); ?></span>
            </div>
        <?php endif; ?>


        <form action="uploadProfile.php" method="POST" enctype="multipart/form-data" class="w-full flex flex-col items-center">
            
            <div class="mb-10 flex flex-col items-center">
                
                <input type="file" id="image-upload-input" name="profile_image_file" accept="image/*" class="hidden">

                <div id="profile-image-container" class="rounded-full flex-shrink-0 mb-4 transition duration-200 hover:border-black" 
                     title="Click to upload an image">
                    
                    <img id="profile-image" src="https://placehold.co/150x150/000000/FFFFFF?text=+" alt="Profile Placeholder" class="hidden">
                    
                    <div id="placeholder-content" class="text-center">
                        <i class="fas fa-plus text-4xl text-gray-400"></i>
                        <p class="text-sm text-gray-500 mt-1">Tap to add</p>
                    </div>
                </div>
                
                <p id="file-name-display" class="text-sm text-gray-600 hidden mt-2"></p>
            </div>

            <div class="w-full space-y-4">
                
                <button type="submit" name="upload" id="upload-btn" class="gymshark-btn gymshark-primary-btn w-full py-4" disabled>
                    Upload Image
                </button>
                
                <button type="submit" name="skip" id="skip-btn" class="gymshark-btn gymshark-secondary-btn w-full py-4">
                    Skip for Now
                </button>
            </div>

        </form>

    </div>

    <script>
        const messageType = "<?php echo $message_type; ?>";
        const redirectUrl = "<?php echo $redirect_url; ?>";
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        const contentContainer = document.getElementById('content-container');

        if (messageType === 'redirect' && redirectUrl) {
            contentContainer.classList.add('hidden');
            loadingMessage.textContent = "<?php echo htmlspecialchars($message_text); ?>";
            loadingOverlay.style.display = 'flex';

            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 2000);
        }

        const imageContainer = document.getElementById('profile-image-container');
        const fileInput = document.getElementById('image-upload-input');
        const profileImage = document.getElementById('profile-image');
        const placeholderContent = document.getElementById('placeholder-content');
        const uploadBtn = document.getElementById('upload-btn');
        const fileNameDisplay = document.getElementById('file-name-display');

        function handleImageSelection(event) {
            const file = event.target.files[0];
            
            if (file) {
                if (!file.type.startsWith('image/')) {
                    console.error('Please select a valid image file.');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                    profileImage.classList.remove('hidden');
                    placeholderContent.classList.add('hidden');
                    
                    uploadBtn.disabled = false;
                    uploadBtn.classList.remove('bg-gray-400', 'border-gray-400');

                    fileNameDisplay.textContent = file.name;
                    fileNameDisplay.classList.remove('hidden');
                };
                reader.readAsDataURL(file);

            } else {
                profileImage.classList.add('hidden');
                placeholderContent.classList.remove('hidden');
                uploadBtn.disabled = true;
                uploadBtn.classList.add('bg-gray-400', 'border-gray-400');
                fileNameDisplay.classList.add('hidden');
            }
        }

        imageContainer.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', handleImageSelection);

        document.addEventListener('DOMContentLoaded', () => {
            if(uploadBtn.disabled) {
                uploadBtn.classList.add('bg-gray-400', 'border-gray-400');
            }
        });

    </script>
</body>
</html>