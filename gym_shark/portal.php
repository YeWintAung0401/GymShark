<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gymshark Access Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-color: #00e0ff; /* Electric Teal */
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Dark Slate */
            overflow: hidden; /* Prevent body scroll */
        }

        .dynamic-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #111827 0%, #0c121e 25%, #111827 50%, #0c121e 75%, #111827 100%);
            background-size: 400% 400%;
            animation: gradientShift 40s ease infinite;
            opacity: 0.8;
            z-index: 0;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-stagger-in {
            opacity: 0;
            transform: translateY(20px);
            animation: slideUpFade 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards; 
        }

        @keyframes slideUpFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.3s; }
        .stagger-3 { animation-delay: 0.5s; }
        .stagger-4 { animation-delay: 0.7s; }

        .role-card {
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .role-card:hover {
            border-color: var(--accent-color);
            background-color: #1f2937;
        }

        .action-button {
            background-color: #374151; 
            border: 1px solid var(--accent-color);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            color: #ffffff;
        }
        
        .action-button:hover {
            background-color: var(--accent-color);
            color: #111827; /* Dark text on bright hover */
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 224, 255, 0.5); /* Glow effect */
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(17, 24, 39, 0.95); /* Darker semi-transparent overlay */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid var(--accent-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div id="loading-overlay" class="loading-overlay">
        <div class="spinner mb-4"></div>
        <p class="text-xl text-white font-semibold">ACCESS GRANTED. STANDBY...</p>
    </div>

    <div class="dynamic-bg"></div>

    <div class="relative z-10 w-full max-w-4xl mx-auto text-center">

        <h1 class="text-7xl lg:text-8xl font-extrabold text-white mb-4 animate-stagger-in stagger-1 tracking-tight" style="color: var(--accent-color);">
            GYMSHARK
        </h1>
        <p class="text-2xl font-light text-gray-300 mb-12 animate-stagger-in stagger-2">
            The World. Your Arena. Choose Your Role.
        </p>

        <div class="flex flex-col md:flex-row justify-center gap-8 mb-12">

            <div id="role-athlete"
                 class="role-card flex-1 p-8 rounded-xl bg-gray-800 text-white shadow-2xl animate-stagger-in stagger-3"
                 onclick="selectRole('athlete')">
                <div class="text-6xl mb-4">🏋️</div>
                <h2 class="text-3xl font-bold mb-2">ATHLETE</h2>
                <p class="text-gray-400">Access training programs, order history, and community features.</p>
                <button class="mt-6 w-full py-3 rounded-lg font-semibold action-button">
                    Enter Portal
                </button>
            </div>

            <div id="role-manager"
                 class="role-card flex-1 p-8 rounded-xl bg-gray-800 text-white shadow-2xl animate-stagger-in stagger-4"
                 onclick="selectRole('manager')">
                <div class="text-6xl mb-4">📊</div>
                <h2 class="text-3xl font-bold mb-2">MANAGER</h2>
                <p class="text-gray-400">View analytics, inventory, and manage user accounts and content.</p>
                <button class="mt-6 w-full py-3 rounded-lg font-semibold action-button">
                    Enter Admin
                </button>
            </div>
        </div>

    </div>

    <script>
        const loadingOverlay = document.getElementById('loading-overlay'); 

        window.onload = () => {
            document.querySelectorAll('.animate-stagger-in').forEach((el, index) => {
                el.style.animationFillMode = 'forwards';
                el.style.animationDelay = `${(index + 1) * 0.15}s`;
            });
            document.querySelectorAll('.action-button').forEach(button => {
                button.parentElement.onclick = null; 
                button.onclick = (event) => {
                    event.stopPropagation();
                    const role = button.parentElement.id === 'role-athlete' ? 'athlete' : 'manager';
                    selectRole(role);
                };
            });
        };

        function selectRole(role) {
            let redirectUrl;

            if (role === 'athlete') {
                // User role
                redirectUrl = 'login/login.php';
            } else if (role === 'manager') {
                // Admin role
                redirectUrl = 'login/login.php'; 
            } else {
                console.error("Invalid role selected.");
                return;
            }
            
            // Show loading interface
            loadingOverlay.classList.add('active'); // show overlay

            setTimeout(() => {
                loadingOverlay.classList.remove('active'); // fade out overlay
            }, 2000);

            setTimeout(() => {
                window.location.href = redirectUrl; // redirect after fade-out
            }, 2000);

        }

    </script>
</body>
</html>
