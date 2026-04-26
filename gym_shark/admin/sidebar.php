<style>
    :root {
        --sidebar-bg: #1e293b;
        --sidebar-hover: #334155;
        --text-main: #f8fafc;
        --accent: #38bdf8;
        --logout: #ef4444;
        --width: 260px;
    }

    body {
        margin: 0;
        font-family: 'Inter', sans-serif;
        background-color: #f1f5f9;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: var(--width);
        background-color: var(--sidebar-bg);
        color: var(--text-main);
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
        z-index: 1000;
    }

    .sidebar-header {
        padding: 2rem 1.5rem;
        font-size: 1.25rem;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #334155;
    }

    .close-btn {
        display: none; 
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 5px;
        transition: color 0.2s;
    }

    .close-btn:hover {
        color: var(--accent);
    }

    .sidebar-nav {
        flex: 1;
        padding: 1rem 0;
        overflow-y: auto;
    }

    .nav-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        color: #cbd5e1;
        text-decoration: none;
        transition: all 0.2s;
        gap: 12px;
    }

    .nav-item i {
        width: 20px;
        height: 20px;
    }

    .nav-item:hover {
        background-color: var(--sidebar-hover);
        color: var(--accent);
    }

    .logout:hover {
        color: var(--logout);
    }

    .sidebar-footer {
        padding: 1rem 0;
        border-top: 1px solid #334155;
    }

    .mobile-toggle, .close-btn {
        display: block;
        background: none;
        border: none;
        color: white;
        cursor: pointer;
    }

        .sidebar {
            transform: translateX(-100%); 
        }

        .sidebar.active {
            transform: translateX(0); 
        }

        .mobile-toggle {
            display: block;
            position: fixed;
            top: 15px;
            left: 15px;
            background: var(--sidebar-bg);
            padding: 8px;
            border-radius: 6px;
            z-index: 999;
        }

        .close-btn {
            display: block;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        .sidebar-overlay.active {
            display: block;
        }
    /* } */
</style>

<button class="mobile-toggle" onclick="toggleSidebar()">
    <i data-lucide="menu"></i>
</button>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        Gym Manager
        <button class="close-btn" onclick="toggleSidebar()">
            <i data-lucide="x"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="admin.php" class="nav-item">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        <a href="staff_management.php" class="nav-item">
            <i data-lucide="users"></i>
            <span>Staffs</span>
        </a>
        <a href="add-trainer.php" class="nav-item">
            <i data-lucide="user-plus"></i>
            <span>Add Trainer</span>
        </a>
        <a href="trainers-list.php" class="nav-item">
            <i data-lucide="list-checks"></i>
            <span>View Trainers</span>
        </a>
        <a href="view-customer.php" class="nav-item">
            <i data-lucide="users"></i>
            <span>Customers</span>
        </a>
        <a href="plans.php" class="nav-item">
            <i data-lucide="file-text"></i>
            <span>Plans</span>
        </a>
        <a href="manage-payment.php" class="nav-item">
            <i data-lucide="dollar-sign"></i>
            <span>Payments</span>
        </a>
        <a href="schedule-list.php" class="nav-item">
            <i data-lucide="calendar-check"></i>
            <span>Schedules</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="../login/logout.php" class="nav-item logout">
            <i data-lucide="log-out"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
  lucide.createIcons();

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
    
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

</script>