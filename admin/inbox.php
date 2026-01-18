<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../components/function.php';
$conn = db_connect();

// Get contact messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM contact_messages";
$count_result = $conn->query($count_query);
$total_messages = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_messages / $limit);

// Get messages
$messages_query = "
    SELECT cm.*, u.name as user_name, u.email as user_email 
    FROM contact_messages cm 
    LEFT JOIN users u ON cm.user_id = u.id 
    ORDER BY cm.created_at DESC 
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($messages_query);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$messages_result = $stmt->get_result();
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#20215B'
                    }
                }
            }
        }
    </script>
    <style>
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-new { background-color: #dbeafe; color: #1d4ed8; }
        .status-read { background-color: #fef3c7; color: #d97706; }
        .status-replied { background-color: #dcfce7; color: #16a34a; }
        .status-closed { background-color: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <div class="flex h-screen pt-16">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="flex-1 flex flex-col">
            <div class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="ri-inbox-line mr-3 text-primary"></i>
                        Inbox
                    </h1>
                    <p class="text-gray-600 mt-1">Manage contact form submissions and customer inquiries</p>
                </div>
            </div>
            
            <div class="flex-1 p-6">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800">Contact Messages</h2>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Filter:</span>
                                    <select id="statusFilter" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                                        <option value="">All Status</option>
                                        <option value="new">New</option>
                                        <option value="read">Read</option>
                                        <option value="replied">Replied</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <button onclick="refreshMessages()" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary/80 transition">
                                    <i class="ri-refresh-line mr-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <i class="ri-inbox-line text-4xl mb-4 block"></i>
                                        <p>No messages found</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($message['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($message['email']); ?></div>
                                            <?php if ($message['phone']): ?>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($message['phone']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($message['user_id']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="ri-user-line mr-1"></i>Registered User
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($message['subject']); ?></div>
                                        <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?><?php echo strlen($message['message']) > 100 ? '...' : ''; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-<?php echo $message['status']; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="viewMessage(<?php echo $message['id']; ?>)" class="text-primary hover:text-primary/80 transition">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                            <button onclick="updateStatus(<?php echo $message['id']; ?>, 'read')" class="text-blue-600 hover:text-blue-800 transition" title="Mark as Read">
                                                <i class="ri-check-line"></i>
                                            </button>
                                            <button onclick="updateStatus(<?php echo $message['id']; ?>, 'replied')" class="text-green-600 hover:text-green-800 transition" title="Mark as Replied">
                                                <i class="ri-reply-line"></i>
                                            </button>
                                            <button onclick="updateStatus(<?php echo $message['id']; ?>, 'closed')" class="text-gray-600 hover:text-gray-800 transition" title="Close">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_messages); ?> of <?php echo $total_messages; ?> results
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition">Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="px-3 py-2 text-sm border border-gray-300 rounded-md <?php echo $i === $page ? 'bg-primary text-white border-primary' : 'hover:bg-gray-50'; ?> transition">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition">Next</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message View Modal -->
    <div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">Message Details</h3>
                    <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <i class="ri-close-line text-2xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="messageContent">
                    <!-- Message content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewMessage(messageId) {
            fetch(`inbox_view.php?id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const message = data.message;
                        document.getElementById('messageContent').innerHTML = `
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">From:</label>
                                        <p class="text-sm text-gray-900">${message.name}</p>
                                        <p class="text-sm text-gray-500">${message.email}</p>
                                        ${message.phone ? `<p class="text-sm text-gray-500">${message.phone}</p>` : ''}
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date:</label>
                                        <p class="text-sm text-gray-900">${new Date(message.created_at).toLocaleString()}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Subject:</label>
                                    <p class="text-sm text-gray-900">${message.subject}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Message:</label>
                                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                                        <p class="text-sm text-gray-900 whitespace-pre-wrap">${message.message}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="updateStatus(${message.id}, 'read')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Mark as Read</button>
                                        <button onclick="updateStatus(${message.id}, 'replied')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">Mark as Replied</button>
                                        <button onclick="updateStatus(${message.id}, 'closed')" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">Close</button>
                                    </div>
                                    <button onclick="replyToMessage(${message.id}, '${message.email}')" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/80 transition">
                                        <i class="ri-reply-line mr-2"></i>Reply
                                    </button>
                                </div>
                            </div>
                        `;
                        document.getElementById('messageModal').classList.remove('hidden');
                    } else {
                        alert('Error loading message: ' + data.error);
                    }
                })
                .catch(error => {
                    alert('Error loading message: ' + error.message);
                });
        }
        
        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
        }
        
        function updateStatus(messageId, status) {
            fetch('inbox_update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message_id: messageId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error updating status: ' + error.message);
            });
        }
        
        function replyToMessage(messageId, email) {
            // Redirect to messages page with pre-filled recipient
            window.location.href = `messages.php?reply_to=${email}`;
        }
        
        function refreshMessages() {
            location.reload();
        }
        
        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const url = new URL(window.location);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        });
    </script>
</body>
</html> 