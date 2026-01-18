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
$reservations = $conn->query('SELECT r.*, u.name as user_name, s.name as spot_name FROM reservations r JOIN users u ON r.user_id = u.id JOIN parking_spots s ON r.spot_id = s.id ORDER BY r.created_at DESC')->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservations - SmartPark Admin</title>
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
    <h1 class="text-3xl font-bold mb-8 font-['Pacifico'] text-primary fade-in">Reservations</h1>
    <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay:0.1s">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="bg-primary text-white">
              <th class="px-4 py-2 text-left">User</th>
              <th class="px-4 py-2 text-left">Spot</th>
              <th class="px-4 py-2 text-left">Plate</th>
              <th class="px-4 py-2 text-left">Vehicle</th>
              <th class="px-4 py-2 text-left">Entry</th>
              <th class="px-4 py-2 text-left">Exit</th>
              <th class="px-4 py-2 text-right">Fee</th>
              <th class="px-4 py-2 text-left">Payment</th>
              <th class="px-4 py-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr class="border-b hover:bg-primary/5 transition">
              <td class="px-4 py-2 font-semibold text-primary"><?php echo htmlspecialchars($r['user_name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($r['spot_name']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($r['license_plate']); ?></td>
              <td class="px-4 py-2"><?php echo htmlspecialchars($r['vehicle_type'] . ' - ' . $r['vehicle_model']); ?></td>
              <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($r['entry_time'])); ?></td>
              <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($r['exit_time'])); ?></td>
              <td class="px-4 py-2 text-right text-primary font-semibold">Ksh<?php echo number_format($r['fee'], 2); ?></td>
              <td class="px-4 py-2"><?php echo ucfirst(htmlspecialchars($r['payment_method'])); ?></td>
              <td class="px-4 py-2">
                <button class="text-primary hover:underline mr-2" onclick="viewReservation(<?php echo $r['id']; ?>)"><i class="ri-eye-line"></i> View</button>
                <button class="text-red-600 hover:underline" onclick="deleteReservation(<?php echo $r['id']; ?>, '<?php echo htmlspecialchars($r['user_name']); ?>', '<?php echo htmlspecialchars($r['license_plate']); ?>', <?php echo $r['fee']; ?>)">
                  <i class="ri-delete-bin-line"></i> Delete
                </button>
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

function viewReservation(id) {
  fetch('reservations_view.php?id=' + id)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok: ' + response.status);
      }
      return response.json();
    })
    .then(data => {
      if (data.success && data.reservation) {
        const r = data.reservation;
        
        const html = `
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Reservation Information</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">Booking Ref:</span> <code class="bg-gray-200 px-1 rounded">${r.booking_ref}</code></div>
                  <div><span class="font-medium">User:</span> ${r.user_name}</div>
                  <div><span class="font-medium">Parking Spot:</span> ${r.spot_name}</div>
                  <div><span class="font-medium">Spot Number:</span> ${r.spot_number || 'N/A'}</div>
                  <div><span class="font-medium">Fee:</span> <span class="text-primary font-semibold">Ksh${parseFloat(r.fee).toFixed(2)}</span></div>
                </div>
              </div>
              
              <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Vehicle Information</h3>
                <div class="space-y-2 text-sm">
                  <div><span class="font-medium">License Plate:</span> <span class="font-mono">${r.license_plate}</span></div>
                  <div><span class="font-medium">Vehicle Type:</span> ${r.vehicle_type.charAt(0).toUpperCase() + r.vehicle_type.slice(1)}</div>
                  <div><span class="font-medium">Vehicle Model:</span> ${r.vehicle_model}</div>
                  <div><span class="font-medium">Payment Method:</span> ${r.payment_method.charAt(0).toUpperCase() + r.payment_method.slice(1)}</div>
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Entry Time</h3>
                <div class="text-sm">
                  <div><span class="font-medium">Date & Time:</span> ${new Date(r.entry_time).toLocaleString()}</div>
                </div>
              </div>
              
              <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-primary mb-2">Exit Time</h3>
                <div class="text-sm">
                  <div><span class="font-medium">Date & Time:</span> ${new Date(r.exit_time).toLocaleString()}</div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="font-semibold text-primary mb-2">Timeline</h3>
              <div class="space-y-2 text-sm">
                <div><span class="font-medium">Created:</span> ${new Date(r.created_at).toLocaleString()}</div>
              </div>
            </div>
            
            <div class="flex justify-center space-x-4 pt-4">
              <button onclick="editReservation(${r.id}, '${r.license_plate}', '${r.vehicle_type}', '${r.vehicle_model}', '${r.entry_time}', '${r.exit_time}', ${r.fee}, '${r.payment_method || '0'}', ${r.spot_number || 'null'})" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 transition">
                <i class="ri-edit-2-line mr-2"></i>Edit Reservation
              </button>
            </div>
          </div>
        `;
        
        // Update modal content directly
        document.getElementById('adminModalTitle').textContent = 'Reservation Details';
        document.getElementById('adminModalContent').innerHTML = html;
        document.getElementById('adminModal').classList.remove('hidden');
        
        // Hide the confirm button since this is just for viewing
        document.getElementById('adminModalConfirm').style.display = 'none';
        document.getElementById('adminModalCancel').textContent = 'Close';
        
      } else {
        showToast('Reservation not found: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error fetching reservation details:', error);
      showToast('Error loading reservation details: ' + error.message, 'error');
    });
}

function editReservation(id, licensePlate, vehicleType, vehicleModel, entryTime, exitTime, fee, paymentMethod, spotNumber) {
  console.log('Edit reservation called with payment method:', paymentMethod);
  
  const html = `
    <form id="editReservationForm" class="space-y-4">
      <input type="hidden" name="id" value="${id}">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">License Plate</label>
          <input type="text" name="license_plate" value="${licensePlate}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Spot Number</label>
          <input type="number" name="spot_number" value="${spotNumber || ''}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
          <select name="vehicle_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
            <option value="car" ${vehicleType === 'car' ? 'selected' : ''}>Car</option>
            <option value="suv" ${vehicleType === 'suv' ? 'selected' : ''}>SUV</option>
            <option value="truck" ${vehicleType === 'truck' ? 'selected' : ''}>Truck</option>
            <option value="motorcycle" ${vehicleType === 'motorcycle' ? 'selected' : ''}>Motorcycle</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Model</label>
          <input type="text" name="vehicle_model" value="${vehicleModel}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Entry Time</label>
          <input type="datetime-local" name="entry_time" value="${entryTime.replace(' ', 'T')}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Exit Time</label>
          <input type="datetime-local" name="exit_time" value="${exitTime.replace(' ', 'T')}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Fee (Ksh)</label>
          <input type="number" name="fee" value="${fee}" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
          <select name="payment_method" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" required>
            <option value="mpesa" ${paymentMethod === 'mpesa' ? 'selected' : ''}>M-Pesa</option>
            <option value="equity" ${paymentMethod === 'equity' ? 'selected' : ''}>Equity</option>
            <option value="0" ${paymentMethod === '0' ? 'selected' : ''}>Pending</option>
          </select>
        </div>
      </div>
      
      <div class="text-sm text-gray-600">
        <p><strong>Note:</strong> Changes to entry/exit times may affect the calculated fee.</p>
        <p><strong>Debug:</strong> Payment method value: ${paymentMethod}</p>
      </div>
    </form>
  `;
  
  // Update modal for editing
  document.getElementById('adminModalTitle').textContent = 'Edit Reservation';
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
    const form = document.getElementById('editReservationForm');
    const formData = new FormData(form);
    
    // Debug: Log form data
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
      console.log(key + ': ' + value);
    }
    
    fetch('reservations_edit.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.success) {
        showToast('Reservation updated successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast('Update failed: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error updating reservation:', error);
      showToast('Error updating reservation: ' + error.message, 'error');
    });
  });
  
  newCancelBtn.addEventListener('click', function() {
    closeAdminModal();
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

function deleteReservation(id, userName, licensePlate, fee) {
  console.log('deleteReservation called with:', {id, userName, licensePlate, fee});
  showAdminModal('Confirm Deletion', `
    <div class="text-center py-4">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="ri-delete-bin-line text-2xl text-red-600"></i>
      </div>
      <p class="text-lg font-medium text-gray-800 mb-2">Delete Reservation</p>
      <p class="text-gray-600 mb-4">
        <strong>User:</strong> ${userName}<br>
        <strong>License Plate:</strong> ${licensePlate}<br>
        <strong>Fee:</strong> Ksh${parseFloat(fee).toFixed(2)}
      </p>
      <p class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
        <i class="ri-error-warning-line mr-1"></i>
        This action cannot be undone. The reservation and all associated data will be permanently deleted.
      </p>
    </div>
  `, function() {
    console.log('Delete confirmation clicked for reservation:', id);
    fetch('reservations_delete.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `id=${id}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('Reservation deleted successfully!', 'success');
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast('Delete failed: ' + (data.error || 'Unknown error'), 'error');
      }
    })
    .catch(error => {
      console.error('Error deleting reservation:', error);
      showToast('Error deleting reservation: ' + error.message, 'error');
    });
  });
}
</script>
</body>
</html>
