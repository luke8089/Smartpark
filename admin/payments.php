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
$payments = $conn->query('SELECT m.*, u.name as user_name FROM mpesa_payments m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC')->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments - SmartPark Admin</title>
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
    
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #10B981;
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 9999;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    .toast.show {
      transform: translateX(0);
    }
    .toast.success {
      background: #10B981;
    }
    .toast.error {
      background: #EF4444;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include 'includes/header.php'; ?>
<div class="flex flex-1 flex-row gap-x-8 min-w-0 w-full">
  <?php include 'includes/sidebar.php'; ?>
  <main class="flex-1 min-w-0 w-full flex flex-col p-8">
    <h1 class="text-3xl font-bold mb-8 font-['Pacifico'] text-primary fade-in">Payments</h1>
    <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay:0.1s">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-primary text-white">
              <th class="px-4 py-2 text-left">User</th>
              <th class="px-4 py-2 text-left">Phone</th>
              <th class="px-4 py-2 text-left">Amount</th>
              <th class="px-4 py-2 text-left">Status</th>
              <th class="px-4 py-2 text-left">Reference</th>
              <th class="px-4 py-2 text-left">Date</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
            <tr class="border-b hover:bg-primary/5 transition">
              <td class="px-4 py-2 font-semibold text-primary"><?php echo htmlspecialchars($p['user_name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($p['phone']); ?></td>
              <td class="px-4 py-2 text-primary font-semibold">Ksh<?php echo number_format($p['amount'],2); ?></td>
              <td class="px-4 py-2"><?php echo ucfirst(htmlspecialchars($p['status'])); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($p['mpesa_ref']); ?></td>
              <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($p['created_at'])); ?></td>
              <td class="px-4 py-2">
                <button class="text-primary hover:underline mr-2" onclick="showAdminModal('View Payment', 'Loading...', function() { viewPayment(<?php echo $p['id']; ?>); })"><i class="ri-eye-line"></i> View</button>
                <button class="text-green-600 hover:underline mr-2" onclick="editPaymentStatus(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['status']); ?>', '<?php echo htmlspecialchars($p['user_name']); ?>', '<?php echo htmlspecialchars($p['amount']); ?>')"><i class="ri-edit-2-line"></i> Status</button>
                <button class="text-red-600 hover:underline" onclick="deletePayment(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['user_name']); ?>', '<?php echo htmlspecialchars($p['amount']); ?>')"><i class="ri-delete-bin-line"></i> Delete</button>
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

function viewPayment(id) {
  console.log('Fetching payment details for ID:', id);
  
  fetch('payments_view.php?id=' + id)
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      console.log('Payment data received:', data);
      
      if (data.success && data.payment) {
        const p = data.payment;
        const statusColors = {
          'pending': 'bg-yellow-100 text-yellow-800',
          'completed': 'bg-green-100 text-green-800',
          'failed': 'bg-red-100 text-red-800',
          'cancelled': 'bg-gray-100 text-gray-800'
        };
        const statusClass = statusColors[p.status] || 'bg-gray-100 text-gray-800';
        
        const html = `
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Payment Information</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">Payment ID:</span> #${p.id}</div>
                  <div><span class="font-medium">Amount:</span> <span class="text-primary font-semibold">Ksh${parseFloat(p.amount).toFixed(2)}</span></div>
                  <div><span class="font-medium">Phone:</span> ${p.phone}</div>
                  <div><span class="font-medium">MPESA Ref:</span> <code class="bg-gray-200 px-1 rounded">${p.mpesa_ref || 'N/A'}</code></div>
                  <div><span class="font-medium">Status:</span> <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${p.status.charAt(0).toUpperCase() + p.status.slice(1)}</span></div>
                </div>
              </div>
              
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">User Information</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">User Name:</span> ${p.user_name}</div>
                  <div><span class="font-medium">User ID:</span> #${p.user_id}</div>
                  <div><span class="font-medium">Reservation ID:</span> ${p.reservation_id ? '#' + p.reservation_id : 'N/A'}</div>
                </div>
              </div>
            </div>
            
            ${p.reservation_id ? `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Reservation Details</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">Booking Ref:</span> <code class="bg-blue-200 px-1 rounded">${p.booking_ref || 'N/A'}</code></div>
                  <div><span class="font-medium">Entry Time:</span> ${p.entry_time ? new Date(p.entry_time).toLocaleString() : 'N/A'}</div>
                  <div><span class="font-medium">Exit Time:</span> ${p.exit_time ? new Date(p.exit_time).toLocaleString() : 'N/A'}</div>
                </div>
              </div>
              
              <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Vehicle Information</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">License Plate:</span> <span class="font-mono">${p.license_plate || 'N/A'}</span></div>
                  <div><span class="font-medium">Vehicle Type:</span> ${p.vehicle_type ? p.vehicle_type.charAt(0).toUpperCase() + p.vehicle_type.slice(1) : 'N/A'}</div>
                  <div><span class="font-medium">Vehicle Model:</span> ${p.vehicle_model || 'N/A'}</div>
                </div>
              </div>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
              <h3 class="font-semibold text-primary mb-2">Parking Spot Information</h3>
              <div class="space-y-2 text-sm">
                <div><span class="font-medium">Spot Name:</span> ${p.spot_name || 'N/A'}</div>
                <div><span class="font-medium">Location:</span> ${p.spot_location || 'N/A'}</div>
              </div>
            </div>
            ` : ''}
            
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="font-semibold text-primary mb-2">Timeline</h3>
              <div class="space-y-2 text-sm">
                <div><span class="font-medium">Created:</span> ${new Date(p.created_at).toLocaleString()}</div>
                ${p.updated_at ? `<div><span class="font-medium">Last Updated:</span> ${new Date(p.updated_at).toLocaleString()}</div>` : ''}
              </div>
            </div>
          </div>
        `;
        
        // Use the new showViewModal function
        showViewModal('Payment Details', html);
        
      } else {
        showToast('Payment not found: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error fetching payment details:', error);
      showToast('Error loading payment details: ' + error.message, 'error');
    });
}

function viewReservation(reservationId) {
  // This function can be implemented to show reservation details
  // For now, we'll just show a message
  showToast('Reservation details feature coming soon!', 'success');
}

function editPaymentStatus(id, currentStatus, userName, amount) {
  const statusOptions = ['pending', 'completed', 'failed', 'cancelled'];
  const optionsHtml = statusOptions.map(status => 
    `<option value="${status}" ${status === currentStatus ? 'selected' : ''}>${status.charAt(0).toUpperCase() + status.slice(1)}</option>`
  ).join('');
  
  const form = `<form id='editPaymentForm' class='space-y-4'>
    <div class='mb-4'>
      <p><strong>User:</strong> ${userName}</p>
      <p><strong>Amount:</strong> Ksh${amount}</p>
    </div>
    <div>
      <label class='block text-gray-700 font-medium mb-2'>Payment Status</label>
      <select name='status' class='w-full border rounded p-2' required>
        ${optionsHtml}
      </select>
    </div>
  </form>`;
  
  showAdminModal('Edit Payment Status', form, function() {
    const formEl = document.getElementById('editPaymentForm');
    const fd = new FormData(formEl);
    fd.append('id', id);
    
    fetch('payments_update_status.php', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.success){ 
          showToast('Payment status updated successfully!', 'success');
          setTimeout(() => location.reload(), 1500);
        }
        else{ showToast('Update failed: ' + (res.error || 'Unknown error'), 'error'); }
      });
  });
}

function deletePayment(id, userName, amount) {
  showAdminModal('Confirm Deletion', `
    <div class="text-center py-4">
      <p><strong>User:</strong> ${userName}</p>
      <p><strong>Amount:</strong> Ksh${amount}</p>
      <p>This payment will be permanently deleted.</p>
    </div>
  `, function() {
    fetch('payments_delete.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `id=${id}`
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Payment deleted successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast('Delete failed: ' + (res.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error deleting payment:', error);
      showToast('Error deleting payment: ' + error.message, 'error');
    });
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
