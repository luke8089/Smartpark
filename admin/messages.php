<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../components/function.php';
$conn = db_connect();

// Get all users for admin to message
$users_query = "SELECT id, name, email, role, profile_image FROM users WHERE id != ? ORDER BY name";
$users_stmt = $conn->prepare($users_query);
$users_stmt->bind_param('i', $_SESSION['user_id']);
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);

// Get recent conversations
$conversations_query = "
    SELECT DISTINCT 
        u.id, u.name, u.email, u.role, u.profile_image,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count,
        (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM users u
    WHERE u.id != ? AND u.id IN (
        SELECT DISTINCT sender_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT DISTINCT receiver_id FROM messages WHERE sender_id = ?
    )
    ORDER BY last_message_time DESC
";
$conversations_stmt = $conn->prepare($conversations_query);
$conversations_stmt->bind_param(
    'iiiiiiii',
    $_SESSION['user_id'], // unread_count
    $_SESSION['user_id'], // last_message (receiver_id)
    $_SESSION['user_id'], // last_message (sender_id)
    $_SESSION['user_id'], // last_message_time (receiver_id)
    $_SESSION['user_id'], // last_message_time (sender_id)
    $_SESSION['user_id'], // WHERE u.id != ?
    $_SESSION['user_id'], // WHERE u.id IN (sender_id)
    $_SESSION['user_id']  // WHERE u.id IN (receiver_id)
);
$conversations_stmt->execute();
$conversations_result = $conversations_stmt->get_result();
$conversations = $conversations_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in; }
        .slide-in { animation: slideIn 0.3s ease-out; }
        .bounce-in { animation: bounceIn 0.6s ease-out; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateX(-20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes bounceIn { 
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .message-bubble {
            max-width: 70%;
            word-wrap: break-word;
        }
        
        .message-bubble.sent {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        
        .message-bubble.received {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 18px 18px 18px 4px;
        }
        
        .typing-indicator {
            display: none;
        }
        
        .typing-indicator.show {
            display: flex;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #9ca3af;
            animation: typing 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
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
                        <i class="ri-message-3-line mr-3 text-primary"></i>
                        Messages
                    </h1>
                    <p class="text-gray-600 mt-1">Communicate with users and manage support requests</p>
                </div>
            </div>
            
            <div class="flex-1 flex overflow-hidden">
                <!-- Sidebar - User List -->
                <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-800">Conversations</h2>
                            <button onclick="showNewMessageModal()" class="bg-primary text-white p-2 rounded-full hover:bg-primary/80 transition">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" id="searchUsers" placeholder="Search users..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <i class="ri-search-line absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto">
                        <div id="conversationsList">
                            <?php foreach ($conversations as $conversation): ?>
                            <div class="conversation-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition" 
                                 onclick="loadConversation(<?php echo $conversation['id']; ?>, '<?php echo htmlspecialchars($conversation['name']); ?>')">
                                <div class="flex items-center space-x-3">
                                    <div class="relative">
                                        <img src="<?php echo !empty($conversation['profile_image']) ? '/smartpark/user/' . $conversation['profile_image'] : '/smartpark/assets/images/default-avatar.png'; ?>" 
                                             alt="<?php echo htmlspecialchars($conversation['name']); ?>" 
                                             class="w-12 h-12 rounded-full object-cover">
                                        <?php if ($conversation['unread_count'] > 0): ?>
                                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                            <?php echo $conversation['unread_count']; ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h3 class="font-medium text-gray-800 truncate"><?php echo htmlspecialchars($conversation['name']); ?></h3>
                                            <span class="text-xs text-gray-500">
                                                <?php echo date('M j', strtotime($conversation['last_message_time'])); ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($conversation['last_message']); ?></p>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo $conversation['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo ucfirst($conversation['role']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div id="usersList" class="hidden">
                            <?php foreach ($users as $user): ?>
                            <div class="user-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition" 
                                 onclick="startNewConversation(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                <div class="flex items-center space-x-3">
                                    <img src="<?php echo !empty($user['profile_image']) ? '/smartpark/user/' . $user['profile_image'] : '/smartpark/assets/images/default-avatar.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                         class="w-12 h-12 rounded-full object-cover">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Main Chat Area -->
                <div class="flex-1 flex flex-col bg-gray-50">
                    <div id="chatHeader" class="bg-white border-b border-gray-200 p-4 hidden">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <img id="chatUserImage" src="" alt="" class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <h3 id="chatUserName" class="font-semibold text-gray-800"></h3>
                                    <p id="chatUserRole" class="text-sm text-gray-600"></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="toggleUserList()" class="p-2 text-gray-600 hover:text-primary transition">
                                    <i class="ri-user-line"></i>
                                </button>
                                <button onclick="clearChat()" class="p-2 text-gray-600 hover:text-red-500 transition">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
                        <div class="text-center text-gray-500 py-8">
                            <i class="ri-message-3-line text-4xl mb-4"></i>
                            <p>Select a conversation to start messaging</p>
                        </div>
                    </div>
                    
                    <div id="messageInput" class="bg-white border-t border-gray-200 p-4 hidden">
                        <div class="flex items-end space-x-3">
                            <div class="flex-1">
                                <input type="text" id="messageSubject" placeholder="Subject (optional)" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-2 focus:ring-primary focus:border-transparent">
                                <textarea id="messageText" placeholder="Type your message..." rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>
                            <button onclick="sendMessage()" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/80 transition flex items-center">
                                <i class="ri-send-plane-fill mr-2"></i>
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Message Modal -->
    <div id="newMessageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 slide-in">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">New Message</h3>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                    <select id="newMessageUser" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Choose a user...</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?> (<?php echo ucfirst($user['role']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input type="text" id="newMessageSubject" placeholder="Message subject" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea id="newMessageText" placeholder="Type your message..." rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeNewMessageModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">Cancel</button>
                    <button onclick="sendNewMessage()" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/80 transition">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentChatUser = null;
        let currentChatUserId = null;
        
        // Search functionality
        document.getElementById('searchUsers').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const conversationItems = document.querySelectorAll('.conversation-item');
            const userItems = document.querySelectorAll('.user-item');
            
            if (searchTerm) {
                document.getElementById('conversationsList').classList.add('hidden');
                document.getElementById('usersList').classList.remove('hidden');
                
                userItems.forEach(item => {
                    const userName = item.querySelector('h3').textContent.toLowerCase();
                    const userEmail = item.querySelector('p').textContent.toLowerCase();
                    if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                document.getElementById('conversationsList').classList.remove('hidden');
                document.getElementById('usersList').classList.add('hidden');
            }
        });
        
        function loadConversation(userId, userName) {
            currentChatUserId = userId;
            currentChatUser = userName;
            
            // Update chat header
            document.getElementById('chatHeader').classList.remove('hidden');
            document.getElementById('chatUserName').textContent = userName;
            document.getElementById('messageInput').classList.remove('hidden');
            
            // Load messages
            fetch(`messages_get.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMessages(data.messages);
                    } else {
                        showToast('Error loading messages: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    showToast('Error loading messages: ' + error.message, 'error');
                });
        }
        
        function displayMessages(messages) {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.innerHTML = '';
            
            messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.className = `flex ${message.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'justify-end' : 'justify-start'}`;
                
                const bubbleClass = message.sender_id == <?php echo $_SESSION['user_id']; ?> ? 'sent' : 'received';
                
                messageDiv.innerHTML = `
                    <div class="message-bubble ${bubbleClass} p-3 shadow-sm">
                        <div class="text-sm font-medium mb-1">${message.subject || 'No Subject'}</div>
                        <div class="text-sm">${message.message}</div>
                        <div class="text-xs opacity-75 mt-2">${new Date(message.created_at).toLocaleString()}</div>
                    </div>
                `;
                
                chatMessages.appendChild(messageDiv);
            });
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function sendMessage() {
            const subject = document.getElementById('messageSubject').value.trim();
            const message = document.getElementById('messageText').value.trim();
            
            if (!message) {
                showToast('Please enter a message', 'error');
                return;
            }
            
            if (!currentChatUserId) {
                showToast('No user selected', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('receiver_id', currentChatUserId);
            formData.append('subject', subject);
            formData.append('message', message);
            
            fetch('messages_send.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageText').value = '';
                    document.getElementById('messageSubject').value = '';
                    loadConversation(currentChatUserId, currentChatUser);
                    showToast('Message sent successfully!', 'success');
                } else {
                    showToast('Error sending message: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showToast('Error sending message: ' + error.message, 'error');
            });
        }
        
        function startNewConversation(userId, userName) {
            loadConversation(userId, userName);
            document.getElementById('usersList').classList.add('hidden');
            document.getElementById('conversationsList').classList.remove('hidden');
            document.getElementById('searchUsers').value = '';
        }
        
        function showNewMessageModal() {
            document.getElementById('newMessageModal').classList.remove('hidden');
        }
        
        function closeNewMessageModal() {
            document.getElementById('newMessageModal').classList.add('hidden');
        }
        
        function sendNewMessage() {
            const userId = document.getElementById('newMessageUser').value;
            const subject = document.getElementById('newMessageSubject').value.trim();
            const message = document.getElementById('newMessageText').value.trim();
            
            if (!userId) {
                showToast('Please select a user', 'error');
                return;
            }
            
            if (!message) {
                showToast('Please enter a message', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('receiver_id', userId);
            formData.append('subject', subject);
            formData.append('message', message);
            
            fetch('messages_send.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeNewMessageModal();
                    document.getElementById('newMessageUser').value = '';
                    document.getElementById('newMessageSubject').value = '';
                    document.getElementById('newMessageText').value = '';
                    showToast('Message sent successfully!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error sending message: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showToast('Error sending message: ' + error.message, 'error');
            });
        }
        
        function clearChat() {
            if (!currentChatUserId) {
                showToast('No conversation selected', 'error');
                return;
            }
            if (!confirm('Are you sure you want to delete all messages in this conversation? This action cannot be undone.')) {
                return;
            }
            const formData = new FormData();
            formData.append('user_id', currentChatUserId);

            fetch('messages_delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('chatHeader').classList.add('hidden');
                    document.getElementById('messageInput').classList.add('hidden');
                    document.getElementById('chatMessages').innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <i class="ri-message-3-line text-4xl mb-4"></i>
                            <p>Select a conversation to start messaging</p>
                        </div>
                    `;
                    currentChatUser = null;
                    currentChatUserId = null;
                    showToast('Conversation deleted successfully!', 'success');
                } else {
                    showToast('Error deleting conversation: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showToast('Error deleting conversation: ' + error.message, 'error');
            });
        }
        
        function toggleUserList() {
            const usersList = document.getElementById('usersList');
            const conversationsList = document.getElementById('conversationsList');
            
            if (usersList.classList.contains('hidden')) {
                usersList.classList.remove('hidden');
                conversationsList.classList.add('hidden');
            } else {
                usersList.classList.add('hidden');
                conversationsList.classList.remove('hidden');
            }
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
        
        // Auto-refresh conversations every 30 seconds
        setInterval(() => {
            if (!currentChatUserId) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html> 