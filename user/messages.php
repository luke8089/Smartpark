<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit();
}
require_once '../components/function.php';
$conn = db_connect();
// Get admin user(s)
$admins_query = "SELECT id, name, email, profile_image FROM users WHERE role = 'admin' ORDER BY name";
$admins_result = $conn->query($admins_query);
$admins = $admins_result->fetch_all(MYSQLI_ASSOC);
// Get recent conversations (only with admins)
$conversations_query = "
    SELECT DISTINCT 
        u.id, u.name, u.email, u.profile_image,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ?) as unread_count,
        (SELECT message FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM users u
    WHERE u.role = 'admin' AND u.id IN (
        SELECT DISTINCT sender_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT DISTINCT receiver_id FROM messages WHERE sender_id = ?
    )
    ORDER BY last_message_time DESC
";
$conversations_stmt = $conn->prepare($conversations_query);
$conversations_stmt->bind_param(
    'iiiiiii',
    $_SESSION['user_id'], // unread_count
    $_SESSION['user_id'], // last_message (receiver_id)
    $_SESSION['user_id'], // last_message (sender_id)
    $_SESSION['user_id'], // last_message_time (receiver_id)
    $_SESSION['user_id'], // last_message_time (sender_id)
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
    <title>Messages - User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#20215B',
                        secondary: '#60A5FA'
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
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in; }
        .slide-in { animation: slideIn 0.3s ease-out; }
        .bounce-in { animation: bounceIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateX(-20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes bounceIn { 0% { transform: scale(0.3); opacity: 0; } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { transform: scale(1); opacity: 1; } }
        .message-bubble { max-width: 70%; word-wrap: break-word; }
        .message-bubble.sent { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); color: white; border-radius: 18px 18px 4px 18px; }
        .message-bubble.received { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 18px 18px 18px 4px; }
        .toast { position: fixed; top: 20px; right: 20px; padding: 12px 24px; border-radius: 8px; color: white; font-weight: 500; z-index: 1000; transform: translateX(100%); transition: transform 0.3s ease; }
        .toast.show { transform: translateX(0); }
        .toast.success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<?php include 'includes/header.php'; ?>
<div class="flex flex-1 flex-row gap-x-8 min-w-0 w-full">
  <main class="flex-1 min-w-0 w-full flex flex-col">
    <div class="bg-white shadow-sm border-b">
      <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
          <i class="ri-message-3-line mr-3 text-primary"></i>
          Messages
        </h1>
        <p class="text-gray-600 mt-1">Chat with the admin for support or inquiries</p>
      </div>
    </div>
    <div class="flex-1 flex overflow-hidden">
      <!-- Sidebar - Admin List -->
      <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
        <div class="p-4 border-b border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Admins</h2>
          </div>
          <div class="relative">
            <input type="text" id="searchAdmins" placeholder="Search admin..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            <i class="ri-search-line absolute right-3 top-3 text-gray-400"></i>
          </div>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div id="conversationsList">
            <?php foreach ($conversations as $conversation): ?>
            <div class="conversation-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition" onclick="loadConversation(<?php echo $conversation['id']; ?>, '<?php echo htmlspecialchars($conversation['name']); ?>')">
              <div class="flex items-center space-x-3">
                <div class="relative">
                  <img src="<?php 
                    if (!empty($conversation['profile_image'])) {
                        if (preg_match('/^https?:\/\//', $conversation['profile_image'])) {
                            echo $conversation['profile_image'];
                        } else if (strpos($conversation['profile_image'], 'user/uploads/') === 0) {
                            echo '../' . $conversation['profile_image'];
                        } else if (strpos($conversation['profile_image'], 'uploads/') === 0) {
                            echo '../' . $conversation['profile_image'];
                        } else {
                            echo '../uploads/' . $conversation['profile_image'];
                        }
                    } else {
                        echo '../assets/images/default-avatar.png';
                    }
                  ?>" alt="<?php echo htmlspecialchars($conversation['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                  <?php if ($conversation['unread_count'] > 0): ?>
                  <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                    <?php echo $conversation['unread_count']; ?>
                  </span>
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-800 truncate"><?php echo htmlspecialchars($conversation['name']); ?></h3>
                    <span class="text-xs text-gray-500"><?php echo date('M j', strtotime($conversation['last_message_time'])); ?></span>
                  </div>
                   <p class="text-sm text-gray-600 truncate"><?php echo htmlspecialchars($conversation['last_message']); ?></p>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div id="adminsList" class="hidden">
            <?php foreach ($admins as $admin): ?>
            <div class="admin-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition" onclick="startNewConversation(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['name']); ?>')">
              <div class="flex items-center space-x-3">
                <img src="<?php 
                    if (!empty($admin['profile_image'])) {
                        if (preg_match('/^https?:\/\//', $admin['profile_image'])) {
                            echo $admin['profile_image'];
                        } else if (strpos($admin['profile_image'], 'user/uploads/') === 0) {
                            echo '../' . $admin['profile_image'];
                        } else if (strpos($admin['profile_image'], 'uploads/') === 0) {
                            echo '../' . $admin['profile_image'];
                        } else {
                            echo '../uploads/' . $admin['profile_image'];
                        }
                    } else {
                        echo '../assets/images/default-avatar.png';
                    }
                ?>" alt="<?php echo htmlspecialchars($admin['name']); ?>" class="w-12 h-12 rounded-full object-cover">
                <div class="flex-1">
                  <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($admin['name']); ?></h3>
                  <p class="text-sm text-gray-600"><?php echo htmlspecialchars($admin['email']); ?></p>
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
          <div class="flex items-center space-x-3">
            <img id="chatUserImage" src="" alt="" class="w-10 h-10 rounded-full object-cover">
            <div>
              <h3 id="chatUserName" class="font-semibold text-gray-800"></h3>
              <p class="text-sm text-gray-600">Admin</p>
            </div>
          </div>
        </div>
        <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
          <div class="text-center text-gray-500 py-8">
            <i class="ri-message-3-line text-4xl mb-4"></i>
            <p>Select an admin to start messaging</p>
          </div>
        </div>
        <div id="messageInput" class="bg-white border-t border-gray-200 p-4 hidden">
          <div class="flex items-end space-x-3">
            <div class="flex-1">
              <input type="text" id="messageSubject" placeholder="Subject (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-2 focus:ring-primary focus:border-transparent">
              <textarea id="messageText" placeholder="Type your message..." rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
            </div>
            <button onclick="sendMessage()" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/80 transition flex items-center">
              <i class="ri-send-plane-fill mr-2"></i>Send
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include 'includes/footer.php'; ?>
<script>
        let currentChatUser = null;
        let currentChatUserId = null;
        document.getElementById('searchAdmins').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const conversationItems = document.querySelectorAll('.conversation-item');
            const adminItems = document.querySelectorAll('.admin-item');
            if (searchTerm) {
                document.getElementById('conversationsList').classList.add('hidden');
                document.getElementById('adminsList').classList.remove('hidden');
                adminItems.forEach(item => {
                    const adminName = item.querySelector('h3').textContent.toLowerCase();
                    const adminEmail = item.querySelector('p').textContent.toLowerCase();
                    if (adminName.includes(searchTerm) || adminEmail.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                document.getElementById('conversationsList').classList.remove('hidden');
                document.getElementById('adminsList').classList.add('hidden');
            }
        });
        function loadConversation(userId, userName) {
            currentChatUserId = userId;
            currentChatUser = userName;
            document.getElementById('chatHeader').classList.remove('hidden');
            document.getElementById('chatUserName').textContent = userName;
            document.getElementById('messageInput').classList.remove('hidden');
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
                showToast('No admin selected', 'error');
                return;
            }
            const formData = new FormData();
            formData.append('receiver_id', currentChatUserId);
            formData.append('subject', subject);
            formData.append('message', message);
            fetch('../admin/messages_send.php', {
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
            document.getElementById('adminsList').classList.add('hidden');
            document.getElementById('conversationsList').classList.remove('hidden');
            document.getElementById('searchAdmins').value = '';
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