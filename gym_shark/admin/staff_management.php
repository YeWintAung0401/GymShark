<?php
include '../connection.php'; 
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login/login.php");
    exit();
}
$query = "SELECT * FROM staff ORDER BY staffID DESC";
$result = $conn->query($query);
$all_staff = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $all_staff[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management | Gym Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .heading-font { font-family: 'Orbitron', sans-serif; }
        .star-active { color: #00d4ff !important; }
    </style>
</head>
<body>
<?php include 'sideBar.php'; ?>

<div class="p-8" style="padding-left: 100px;">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-2xl font-bold text-slate-800 heading-font uppercase">
            Staff <span style="color: #00d4ff;">Management</span>
        </h2>
        <button onclick="openStaffModal('register')" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition-all uppercase text-xs tracking-widest">
            + Register New Staff
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-widest">
                    <th class="p-4">Profile</th>
                    <th class="p-4">Name & Role</th>
                    <th class="p-4">Contact info</th>
                    <th class="p-4">Salary</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($all_staff as $staff): ?>
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="p-4">
                        <img src="default-profile.jpg" class="w-12 h-12 rounded-full object-cover border border-slate-200">
                    </td>
                    <td class="p-4">
                        <div class="font-bold text-slate-800"><?php echo htmlspecialchars($staff['staffName']); ?></div>
                        <div class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full inline-block font-semibold mt-1"><?php echo $staff['role']; ?></div>
                    </td>
                    <td class="p-4 text-sm text-slate-600">
                        <div><i class="fas fa-envelope mr-2 text-slate-400"></i><?php echo $staff['staffEmail']; ?></div>
                        <div class="mt-1"><i class="fas fa-phone mr-2 text-slate-400"></i><?php echo $staff['staffPhone']; ?></div>
                    </td>
                    <td class="p-4 font-bold text-slate-700">$<?php echo number_format($staff['salary'], 2); ?></td>
                    <td class="p-4 text-center">
                        <button onclick='openStaffModal("update", <?php echo json_encode($staff); ?>)' class="text-blue-500 hover:text-blue-700 p-2"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteStaff(<?php echo $staff['staffID']; ?>)" class="text-red-500 hover:text-red-700 p-2 ml-2"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="staffModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-6 border-b flex justify-between items-center bg-slate-50">
            <h3 id="modalTitle" class="heading-font text-lg font-bold text-slate-800 uppercase">Staff Entry</h3>
            <button onclick="closeStaffModal()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        <form id="staffForm" action="process_staff.php" method="POST" enctype="multipart/form-data" class="p-8 grid grid-cols-2 gap-6">
            <input type="hidden" name="action" id="formAction" value="register">
            <input type="hidden" name="staffID" id="staffID">
            
            <div class="col-span-1">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Full Name</label>
                <input type="text" name="staffName" id="staffName" required class="w-full p-3 border rounded-xl outline-none focus:border-blue-500" placeholder="Full Name">
            </div>
            <div class="col-span-1">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Role</label>
                <select name="role" id="role" class="w-full p-3 border rounded-xl outline-none">
                    <option value="Manager">Manager</option>
                    <option value="Receptionist">Receptionist</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Email Address</label>
                <input type="email" name="staffEmail" id="staffEmail" required class="w-full p-3 border rounded-xl outline-none" placeholder="Email Address">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Phone Number</label>
                <input type="text" name="staffPhone" id="staffPhone" required class="w-full p-3 border rounded-xl outline-none" placeholder="Phone Number">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Salary ($)</label>
                <input type="number" step="0.01" name="salary" id="salary" required class="w-full p-3 border rounded-xl outline-none" placeholder="Salary Amount">
            </div>
            <div class="col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Address</label>
                <textarea name="staffAddress" id="staffAddress" rows="2" class="w-full p-3 border rounded-xl outline-none" placeholder="Address"></textarea>
            </div>
            <div class="col-span-2 mt-4">
                <button type="submit" class="w-full py-4 bg-slate-900 text-white rounded-xl font-bold uppercase tracking-widest hover:bg-black transition-all">Save Record</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStaffModal(mode, data = null) {
    const form = document.getElementById('staffForm');
    form.reset();
    document.getElementById('formAction').value = mode;
    
    if (mode === 'update' && data) {
        document.getElementById('modalTitle').innerText = "Update Staff Member";
        document.getElementById('staffID').value = data.staffID;
        document.getElementById('staffName').value = data.staffName;
        document.getElementById('role').value = data.role;
        document.getElementById('staffEmail').value = data.staffEmail;
        document.getElementById('staffPhone').value = data.staffPhone;
        document.getElementById('salary').value = data.salary;
        document.getElementById('staffAddress').value = data.staffAddress;
    } else {
        document.getElementById('modalTitle').innerText = "Register New Staff";
        document.getElementById('staffID').value = "";
    }
    document.getElementById('staffModal').classList.remove('hidden');
}

function closeStaffModal() { document.getElementById('staffModal').classList.add('hidden'); }

function deleteStaff(id) {
    if(confirm('Delete this staff record permanently?')) {
        window.location.href = `process_staff.php?action=delete&id=${id}`;
    }
}
</script>
</body>
</html>