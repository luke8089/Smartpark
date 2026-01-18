<?php session_start();
if (!isset($_SESSION['user'])) {
  header('Location: ../reglogin.php');
  exit();
}
// Prevent page caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
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
    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        .contact-form input:focus, .contact-form textarea:focus {
            outline: none;
            border-color: #20215B;
            box-shadow: 0 0 0 2px rgba(32, 33, 91, 0.1);
        }
        .success-message {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include 'includes/header.php'; ?>
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="text-center mb-16">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Contact Us</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">We're here to help and answer any questions you might have. We look forward to hearing from you.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="bg-white p-8 rounded-lg shadow-sm text-center">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-phone-line text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Phone</h3>
                <p class="text-gray-600">+2547 123 4567</p>
                <p class="text-sm text-gray-500 mt-2">Mon-Fri: 9:00 AM - 6:00 PM EST</p>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-sm text-center">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-mail-line text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Email</h3>
                <p class="text-gray-600">support@smartpark.com</p>
                <p class="text-sm text-gray-500 mt-2">24/7 Email Support</p>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-sm text-center">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="ri-map-pin-line text-primary text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">Office</h3>
                <p class="text-gray-600">Sarit Centre, Westlands</p>
                <p class="text-gray-600">Nairobi, Kenya</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
            <div class="bg-white p-8 rounded-lg shadow-sm">
                <h2 class="text-2xl font-semibold mb-6">Send us a message</h2>
                <form id="contactForm" class="contact-form space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-button text-gray-900 text-sm" placeholder="John Smith">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-button text-gray-900 text-sm" placeholder="john@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-button text-gray-900 text-sm" placeholder="+1 (555) 123-4567">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="subject">Subject *</label>
                        <div class="relative">
                            <select id="subject" name="subject" required class="w-full px-4 py-2 border border-gray-300 rounded-button text-gray-900 text-sm appearance-none pr-8">
                                <option value="">Select a subject</option>
                                <option value="general">General Inquiry</option>
                                <option value="support">Technical Support</option>
                                <option value="billing">Billing Question</option>
                                <option value="partnership">Partnership Opportunity</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <i class="ri-arrow-down-s-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="message">Message *</label>
                        <textarea id="message" name="message" required rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-button text-gray-900 text-sm" placeholder="Your message here..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-3 px-6 rounded-button hover:bg-primary/90 transition-colors cursor-pointer whitespace-nowrap">Send Message</button>
                </form>
            </div>

            <div class="space-y-8">
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <h2 class="text-2xl font-semibold mb-6">Our Location</h2>
                    <div class="w-full h-[400px] rounded-lg overflow-hidden relative">
                        <iframe 
                            src="https://www.openstreetmap.org/export/embed.html?bbox=36.8022,-1.2649,36.8022,-1.2649&layer=mapnik&marker=-1.2649,36.8022" 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            scrolling="no" 
                            marginheight="0" 
                            marginwidth="0" 
                            title="SmartPark Location - Sarit Centre, Westlands, Nairobi">
                        </iframe>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <h2 class="text-2xl font-semibold mb-6">Connect With Us</h2>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                            <i class="ri-twitter-x-line text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                            <i class="ri-facebook-line text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                            <i class="ri-linkedin-line text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                            <i class="ri-instagram-line text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable submit button and show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="ri-loader-4-line animate-spin mr-2"></i>Sending...';
    
    fetch('contact_submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showSuccessMessage(data.message);
            // Reset form
            document.getElementById('contactForm').reset();
        } else {
            // Show error message
            showErrorMessage(data.error);
        }
    })
    .catch(error => {
        showErrorMessage('An error occurred. Please try again.');
        console.error('Error:', error);
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;
    });
});

function showSuccessMessage(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
    successDiv.textContent = message;
    document.body.appendChild(successDiv);
    
    // Show the message
    setTimeout(() => successDiv.style.display = 'block', 100);
    
    // Hide after 5 seconds
    setTimeout(() => {
        successDiv.style.display = 'none';
        document.body.removeChild(successDiv);
    }, 5000);
}

function showErrorMessage(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'success-message bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg';
    errorDiv.textContent = message;
    document.body.appendChild(errorDiv);
    
    // Show the message
    setTimeout(() => errorDiv.style.display = 'block', 100);
    
    // Hide after 5 seconds
    setTimeout(() => {
        errorDiv.style.display = 'none';
        document.body.removeChild(errorDiv);
    }, 5000);
}
</script>
</body>
</html>