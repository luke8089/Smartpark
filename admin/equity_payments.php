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
$equity_payments = $conn->query('
    SELECT e.*, u.name as user_name, r.booking_ref, r.license_plate, r.vehicle_type, r.vehicle_model 
    FROM equity_payments e 
    JOIN users u ON e.user_id = u.id 
    LEFT JOIN reservations r ON e.reservation_id = r.id 
    ORDER BY e.created_at DESC
')->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equity Payments - SmartPark Admin</title>
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
            <h1 class="text-3xl font-bold mb-8 font-['Pacifico'] text-primary fade-in">Equity Payments</h1>
            <div class="bg-white rounded-xl shadow-lg p-6 fade-in" style="animation-delay:0.1s">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-primary text-white">
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Booking Ref</th>
                                <th class="px-4 py-2 text-left">Account Details</th>
                                <th class="px-4 py-2 text-left">Vehicle</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equity_payments as $payment): ?>
                            <tr class="border-b hover:bg-primary/5 transition">
                                <td class="px-4 py-2 font-semibold text-primary"><?php echo htmlspecialchars($payment['user_name']); ?></td>
                                <td class="px-4 py-2"><code class="bg-gray-100 px-1 rounded"><?php echo htmlspecialchars($payment['booking_ref']); ?></code></td>
                                <td class="px-4 py-2">
                                    <div class="text-xs">
                                        <div><strong>Acc:</strong> <?php echo htmlspecialchars($payment['account_number']); ?></div>
                                        <div><strong>Name:</strong> <?php echo htmlspecialchars($payment['account_name']); ?></div>
                                    </div>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="text-xs">
                                        <div><?php echo htmlspecialchars($payment['license_plate']); ?></div>
                                        <div><?php echo htmlspecialchars($payment['vehicle_type'] . ' - ' . $payment['vehicle_model']); ?></div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right text-primary font-semibold">Ksh<?php echo number_format($payment['amount'], 2); ?></td>
                                <td class="px-4 py-2">
                                    <?php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusClass = $statusColors[$payment['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2"><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                <td class="px-4 py-2">
                                    <button class="text-primary hover:underline mr-2" onclick="viewEquityPayment(<?php echo $payment['id']; ?>)">
                                        <i class="ri-eye-line"></i> View
                                    </button>
                                    <?php if ($payment['status'] === 'pending'): ?>
                                    <button class="text-green-600 hover:underline mr-2" onclick="updateEquityStatus(<?php echo $payment['id']; ?>, 'completed')">
                                        <i class="ri-check-line"></i> Confirm
                                    </button>
                                    <button class="text-red-600 hover:underline mr-2" onclick="updateEquityStatus(<?php echo $payment['id']; ?>, 'failed')">
                                        <i class="ri-close-line"></i> Reject
                                    </button>
                                    <?php endif; ?>
                                    <button class="text-red-600 hover:underline" onclick="deleteEquityPayment(<?php echo $payment['id']; ?>, '<?php echo htmlspecialchars($payment['user_name']); ?>', <?php echo $payment['amount']; ?>)">
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

    function viewEquityPayment(id) {
        fetch('equity_payments_view.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.payment) {
                    const p = data.payment;
                    const statusColors = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'completed': 'bg-green-100 text-green-800',
                        'failed': 'bg-red-100 text-red-800',
                        'cancelled': 'bg-gray-100 text-gray-800'
                    };
                    const statusClass = statusColors[p.status] || 'bg-gray-100 text-gray-800';
                    let statusEditHtml = '';
                    let showUpdateBtn = false;
                    
                    // Allow editing status for all payments
                    statusEditHtml = `
                        <label class="block text-sm font-medium mb-1">Change Status:</label>
                        <select id="editEquityStatus" class="border rounded px-2 py-1 w-full">
                            <option value="pending" ${p.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="completed" ${p.status === 'completed' ? 'selected' : ''}>Confirmed</option>
                            <option value="failed" ${p.status === 'failed' ? 'selected' : ''}>Rejected</option>
                            <option value="cancelled" ${p.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    `;
                    showUpdateBtn = true;
                    const html = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-primary mb-2">Payment Information</h3>
                                    <div class="space-y-2 text-sm">
                                        <div><span class="font-medium">Payment ID:</span> #${p.id}</div>
                                        <div><span class="font-medium">Amount:</span> <span class="text-primary font-semibold">Ksh${parseFloat(p.amount).toFixed(2)}</span></div>
                                        <div><span class="font-medium">Transaction Ref:</span> <code class="bg-gray-200 px-1 rounded">${p.transaction_ref || 'N/A'}</code></div>
                                        <div><span class="font-medium">Status:</span> ${statusEditHtml}</div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="font-semibold text-primary mb-2">Account Information</h3>
                                    <div class="space-y-2 text-sm">
                                        <div><span class="font-medium">Account Number:</span> ${p.account_number}</div>
                                        <div><span class="font-medium">Account Name:</span> ${p.account_name}</div>
                                        <div><span class="font-medium">User:</span> ${p.user_name}</div>
                                        <div><span class="font-medium">Booking Ref:</span> <code class="bg-gray-200 px-1 rounded">${p.booking_ref || 'N/A'}</code></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-primary mb-2">Timeline</h3>
                                <div class="space-y-2 text-sm">
                                    <div><span class="font-medium">Created:</span> ${new Date(p.created_at).toLocaleString()}</div>
                                    ${p.updated_at ? `<div><span class="font-medium">Last Updated:</span> ${new Date(p.updated_at).toLocaleString()}</div>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('adminModalTitle').textContent = 'Equity Payment Details';
                    document.getElementById('adminModalContent').innerHTML = html;
                    document.getElementById('adminModal').classList.remove('hidden');
                    // Show or hide confirm button
                    if (showUpdateBtn) {
                        const confirmBtn = document.getElementById('adminModalConfirm');
                        confirmBtn.style.display = 'inline-block';
                        confirmBtn.textContent = 'Update Status';
                        confirmBtn.onclick = function() {
                            const newStatus = document.getElementById('editEquityStatus').value;
                            fetch('equity_payments_update.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `id=${p.id}&status=${newStatus}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showToast('Status updated successfully!', 'success');
                                    setTimeout(() => location.reload(), 1200);
                                } else {
                                    showToast('Update failed: ' + (data.error || 'Unknown error'), 'error');
                                }
                            })
                            .catch(error => {
                                showToast('Error updating status: ' + error.message, 'error');
                            });
                        };
                    } else {
                        document.getElementById('adminModalConfirm').style.display = 'none';
                    }
                    document.getElementById('adminModalCancel').textContent = 'Close';
                } else {
                    showToast('Payment not found: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching payment details:', error);
                showToast('Error loading payment details: ' + error.message, 'error');
            });
    }

    function updateEquityStatus(id, status) {
        const action = status === 'completed' ? 'confirm' : 'reject';
        const confirmation = confirm(`Are you sure you want to ${action} this payment?`);
        
        if (confirmation) {
            fetch('equity_payments_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Payment ${action}ed successfully!`, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(`${action.charAt(0).toUpperCase() + action.slice(1)} failed: ` + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error updating payment status:', error);
                showToast('Error updating payment status: ' + error.message, 'error');
            });
        }
    }

    function deleteEquityPayment(id, userName, amount) {
        showAdminModal('Confirm Deletion', `
            <div class="text-center py-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-delete-bin-line text-2xl text-red-600"></i>
                </div>
                <p class="text-lg font-medium text-gray-800 mb-2">Delete Equity Payment</p>
                <p class="text-gray-600 mb-4">
                    <strong>User:</strong> ${userName}<br>
                    <strong>Amount:</strong> Ksh${parseFloat(amount).toFixed(2)}
                </p>
                <p class="text-sm text-red-600 bg-red-50 p-3 rounded-lg">
                    <i class="ri-error-warning-line mr-1"></i>
                    This action cannot be undone. The payment will be permanently deleted.
                </p>
            </div>
        `, function() {
            fetch('equity_payments_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Payment deleted successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Delete failed: ' + (data.error || 'Unknown error'), 'error');
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