<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../reglogin.php");
    exit();
}
// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../components/function.php';
$conn = db_connect();
$users = $conn->query('SELECT * FROM users ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users - SmartPark Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#20215B',
            secondary: '#64748B'
          },
          borderRadius: {
            'none': '0px',
            'sm': '4px',
            DEFAULT: '8px',
            'md': '12px',
            'lg': '16px',
            'xl': '20px',
            '2xl': '24px',
            '3xl': '32px',
            'full': '9999px',
            'button': '8px'
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    .fade-in { opacity: 0; transform: translateY(30px) scale(0.98); animation: fadeInUp 0.7s cubic-bezier(.4,2,.6,1) forwards; }
    @keyframes fadeInUp { to { opacity: 1; transform: none; } }
    
    /* Toast Notifications */
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 20px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      z-index: 10000;
      transform: translateX(100%);
      transition: transform 0.3s ease;
      max-width: 300px;
    }
    .toast.show {
      transform: translateX(0);
    }
    .toast.success {
      background-color: #10b981;
    }
    .toast.error {
      background-color: #ef4444;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include 'includes/header.php'; ?>
<div class="flex flex-1 flex-row gap-x-8 min-w-0 w-full">
  <?php include 'includes/sidebar.php'; ?>
  <main class="flex-1 min-w-0 w-full flex flex-col p-8">
    <h1 class="text-3xl font-bold mb-8 font-['Pacifico'] text-primary fade-in">Users</h1>
    <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay:0.1s">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-primary text-white">
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Email</th>
              <th class="px-4 py-2 text-left">Role</th>
              <th class="px-4 py-2 text-left">Registered</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr class="border-b hover:bg-primary/5 transition">
              <td class="px-4 py-2 font-semibold text-primary"><?php echo htmlspecialchars($u['name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($u['email']); ?></td>
              <td class="px-4 py-2"><?php echo ucfirst(htmlspecialchars($u['role'])); ?></td>
              <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($u['created_at'])); ?></td>
              <td class="px-4 py-2">
                <button class="text-primary hover:underline mr-2" onclick="viewUser(<?php echo $u['id']; ?>)"><i class="ri-eye-line"></i> View</button>
                <button class="text-red-500 hover:underline" onclick="showAdminModal('Delete User', 'Are you sure you want to delete this user?', function() { deleteUser(<?php echo $u['id']; ?>); })"><i class="ri-delete-bin-6-line"></i> Delete</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include 'includes/footer.php'; ?>
<?php include 'includes/modal.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.fade-in').forEach((el, i) => {
    el.style.animationDelay = (i * 0.1 + 0.1) + 's';
  });
});

function viewUser(id) {
  fetch('users_view.php?id=' + id)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      if (data.success && data.user) {
        const u = data.user;
        const roleColors = {
          'admin': 'bg-red-100 text-red-800',
          'user': 'bg-blue-100 text-blue-800'
        };
        const roleClass = roleColors[u.role] || 'bg-gray-100 text-gray-800';
        
        const html = `
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">User Information</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">User ID:</span> #${u.id}</div>
                  <div><span class="font-medium">Name:</span> ${u.name}</div>
                  <div><span class="font-medium">Email:</span> ${u.email}</div>
                  <div><span class="font-medium">Role:</span> <span class="px-2 py-1 rounded-full text-xs font-medium ${roleClass}">${u.role.charAt(0).toUpperCase() + u.role.slice(1)}</span></div>
                </div>
              </div>
              
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Account Details</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">Registered:</span> ${new Date(u.created_at).toLocaleString()}</div>
                  <div><span class="font-medium">Profile Image:</span> ${u.profile_image ? 'Yes' : 'No'}</div>
                </div>
              </div>
            </div>
            
            ${u.profile_image ? `
            <div class="bg-blue-50 p-4 rounded-lg">
              <h3 class="font-semibold text-primary mb-2">Profile Image</h3>
              <div class="flex justify-center">
                <img src="${u.profile_image.startsWith('uploads/') ? '/smartpark/user/' + u.profile_image : '/smartpark/' + u.profile_image}" alt="Profile Image" class="w-32 h-32 rounded-full object-cover border-4 border-primary shadow-lg">
              </div>
            </div>
            ` : ''}
            
            <div class="flex justify-center space-x-4 pt-4">
              <button onclick="editUser(${u.id}, '${u.name}', '${u.email}', '${u.role}', '${u.profile_image}')" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 transition">
                <i class="ri-edit-2-line mr-2"></i>Edit User
              </button>
            </div>
          </div>
        `;
        
        // Update modal content directly
        document.getElementById('adminModalTitle').textContent = 'User Details';
        document.getElementById('adminModalContent').innerHTML = html;
        document.getElementById('adminModal').classList.remove('hidden');
        
        // Hide the confirm button since this is just for viewing
        document.getElementById('adminModalConfirm').style.display = 'none';
        document.getElementById('adminModalCancel').textContent = 'Close';
        
      } else {
        showToast('User not found: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error fetching user details:', error);
      showToast('Error loading user details: ' + error.message, 'error');
    });
}

function editUser(id, name, email, role, profileImage = null) {
  const currentImageHtml = profileImage ? `
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Current Profile Image</label>
      <div class="flex justify-center">
        <img src="${profileImage.startsWith('uploads/') ? '/smartpark/user/' + profileImage : '/smartpark/' + profileImage}" alt="Current Profile" class="w-24 h-24 rounded-full object-cover border-2 border-gray-300">
      </div>
    </div>
  ` : '';
  
  const html = `
    <form id="editUserForm" class="space-y-4">
      <input type="hidden" name="id" value="${id}">
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="${name}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="${email}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
          <option value="user" ${role === 'user' ? 'selected' : ''}>User</option>
          <option value="admin" ${role === 'admin' ? 'selected' : ''}>Admin</option>
        </select>
      </div>
      
      ${currentImageHtml}
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Profile Image (Optional)</label>
        <input type="file" name="profile_image" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image. Max size: 2MB</p>
      </div>
      
      <div class="text-sm text-gray-600">
        <p><strong>Note:</strong> Password changes are not supported in this form.</p>
        <p>To change password, the user must use the password reset feature.</p>
      </div>
    </form>
  `;
  
  // Update modal for editing
  document.getElementById('adminModalTitle').textContent = 'Edit User';
  document.getElementById('adminModalContent').innerHTML = html;
  
  // Show confirm and cancel buttons
  document.getElementById('adminModalConfirm').style.display = 'inline-block';
  document.getElementById('adminModalConfirm').textContent = 'Save Changes';
  document.getElementById('adminModalCancel').textContent = 'Cancel';
  
  // Add event listener for form submission
  const confirmBtn = document.getElementById('adminModalConfirm');
  const cancelBtn = document.getElementById('adminModalCancel');
  
  // Remove existing listeners
  confirmBtn.replaceWith(confirmBtn.cloneNode(true));
  cancelBtn.replaceWith(cancelBtn.cloneNode(true));
  
  // Get new references
  const newConfirmBtn = document.getElementById('adminModalConfirm');
  const newCancelBtn = document.getElementById('adminModalCancel');
  
  newConfirmBtn.addEventListener('click', function() {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    
    fetch('users_edit.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('User updated successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast('Update failed: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error updating user:', error);
      showToast('Error updating user: ' + error.message, 'error');
    });
  });
  
  newCancelBtn.addEventListener('click', function() {
    closeAdminModal();
  });
}
function deleteUser(id) {
  fetch('users_delete.php', {
    method: 'POST', 
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    }, 
    body: 'id=' + id
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Network response was not ok: ' + response.status);
    }
    return response.json();
  })
  .then(res => {
    if (res.success) {
      showToast('User deleted successfully!', 'success');
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast('Delete failed: ' + (res.error || 'Unknown error'), 'error');
    }
  })
  .catch(error => {
    console.error('Error deleting user:', error);
    showToast('Error deleting user: ' + error.message, 'error');
  });
}

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  document.body.appendChild(toast);
  
  setTimeout(() => toast.classList.add('show'), 100);
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => document.body.removeChild(toast), 300);
  }, 3000);
}
</script>
</body>
</html>
