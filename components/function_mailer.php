<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

// Load configuration
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    die('Configuration file not found. Please copy config.example.php to config.php and update with your credentials.');
}

function send_registration_email($to, $to_name) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        //Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $to_name);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to SmartPark!';
        $mail->Body    = '
        <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(32,33,91,0.08);overflow:hidden;font-family:sans-serif;">
            <div style="background:#20215B;padding:32px 0;text-align:center;">
                <h1 style="color:#fff;font-size:2rem;margin:0;font-family:Pacifico, cursive;letter-spacing:1px;">SmartPark</h1>
            </div>
            <div style="padding:32px 28px 24px 28px;">
                <h2 style="color:#20215B;font-size:1.5rem;margin-top:0;margin-bottom:12px;">Welcome, ' . htmlspecialchars($to_name) . '!</h2>
                <p style="color:#444;font-size:1rem;line-height:1.6;margin-bottom:18px;">Thank you for registering at <b>SmartPark</b>. We are thrilled to have you join our community of smart drivers!</p>
                <div style="background:#f4f6fa;border-radius:8px;padding:18px 16px;margin-bottom:18px;">
                    <h3 style="color:#20215B;margin-top:0;margin-bottom:8px;font-size:1.1rem;">What you can do with SmartPark:</h3>
                    <ul style="color:#333;padding-left:18px;margin:0;font-size:0.98rem;">
                        <li> Reserve parking spots in advance</li>
                        <li> Pay securely online (Mpesa, Equity, and more)</li>
                        <li> Manage your bookings and profile easily</li>
                        <li> Get instant notifications and reminders</li>
                        <li> Enjoy secure, reliable, and convenient parking</li>
                    </ul>
                </div>
                <div style="text-align:center;margin-bottom:18px;">
                    <a href="http://localhost/smartpark/reglogin.php" style="display:inline-block;background:#20215B;color:#fff;text-decoration:none;padding:12px 32px;border-radius:6px;font-weight:600;font-size:1rem;box-shadow:0 2px 8px rgba(32,33,91,0.10);transition:background 0.2s;">Login to Your Account</a>
                </div>
                <p style="color:#666;font-size:0.97rem;margin-bottom:0;">If you have any questions or need help, just reply to this email or contact our support team at <a href="mailto:support@smartpark.com" style="color:#20215B;text-decoration:underline;">support@smartpark.com</a>.</p>
                <p style="color:#aaa;font-size:0.85rem;margin-top:24px;">&copy; ' . date('Y') . ' SmartPark. All rights reserved.</p>
            </div>
        </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Optionally log $mail->ErrorInfo
        return false;
    }
}

function send_booking_confirmation_email($user_email, $user_name, $booking_data) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        //Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($user_email, $user_name);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation - SmartPark';
        
        // Format dates for display
        $entry_date = date('F j, Y', strtotime($booking_data['entry_time']));
        $entry_time = date('g:i A', strtotime($booking_data['entry_time']));
        $exit_date = date('F j, Y', strtotime($booking_data['exit_time']));
        $exit_time = date('g:i A', strtotime($booking_data['exit_time']));
        
        $mail->Body = '
        <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(32,33,91,0.08);overflow:hidden;font-family:sans-serif;">
            <div style="background:#20215B;padding:32px 0;text-align:center;">
                <h1 style="color:#fff;font-size:2rem;margin:0;font-family:Pacifico, cursive;letter-spacing:1px;">SmartPark</h1>
                <p style="color:#fff;margin:8px 0 0 0;font-size:1.1rem;">Booking Confirmation</p>
            </div>
            
            <div style="padding:32px 28px 24px 28px;">
                <h2 style="color:#20215B;font-size:1.5rem;margin-top:0;margin-bottom:16px;">Hello, ' . htmlspecialchars($user_name) . '!</h2>
                <p style="color:#444;font-size:1rem;line-height:1.6;margin-bottom:24px;">Your parking reservation has been successfully confirmed. Here are your booking details:</p>
                
                <div style="background:#f8f9fa;border-radius:8px;padding:20px;margin-bottom:24px;border-left:4px solid #20215B;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Booking Reference:</span>
                        <span style="font-weight:600;color:#333;">' . htmlspecialchars($booking_data['booking_ref']) . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Parking Location:</span>
                        <span style="color:#333;">' . htmlspecialchars($booking_data['spot_name']) . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Spot Number:</span>
                        <span style="color:#333;">' . htmlspecialchars($booking_data['spot_number']) . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Vehicle:</span>
                        <span style="color:#333;">' . htmlspecialchars($booking_data['vehicle_type'] . ' - ' . $booking_data['vehicle_model']) . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">License Plate:</span>
                        <span style="color:#333;">' . htmlspecialchars($booking_data['license_plate']) . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Entry Time:</span>
                        <span style="color:#333;">' . $entry_date . ' at ' . $entry_time . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Exit Time:</span>
                        <span style="color:#333;">' . $exit_date . ' at ' . $exit_time . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-weight:600;color:#20215B;">Payment Method:</span>
                        <span style="color:#333;">' . ucfirst(htmlspecialchars($booking_data['payment_method'])) . '</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:0;">
                        <span style="font-weight:600;color:#20215B;">Total Amount:</span>
                        <span style="font-weight:600;color:#333;font-size:1.1rem;">Ksh ' . number_format($booking_data['fee'], 2) . '</span>
                    </div>
                </div>
                
                <div style="background:#e8f5e8;border-radius:8px;padding:16px;margin-bottom:24px;border-left:4px solid #28a745;">
                    <h3 style="color:#155724;margin-top:0;margin-bottom:8px;font-size:1.1rem;">Important Information:</h3>
                    <ul style="color:#155724;padding-left:18px;margin:0;font-size:0.95rem;">
                        <li>Please arrive on time for your scheduled entry</li>
                        <li>Have your booking reference ready for verification</li>
                        <li>Keep your vehicle within the allocated time slot</li>
                        <li>Contact us immediately if you need to modify your booking</li>
                    </ul>
                </div>
                
                <div style="text-align:center;margin-bottom:24px;">
                    <a href="https://your-smartpark-url.com/user/receipt.php?booking_ref=' . urlencode($booking_data['booking_ref']) . '" style="display:inline-block;background:#20215B;color:#fff;text-decoration:none;padding:12px 32px;border-radius:6px;font-weight:600;font-size:1rem;box-shadow:0 2px 8px rgba(32,33,91,0.10);transition:background 0.2s;">View Receipt</a>
                </div>
                
                <p style="color:#666;font-size:0.97rem;margin-bottom:0;">If you have any questions or need to modify your booking, please contact our support team at <a href="mailto:support@smartpark.com" style="color:#20215B;text-decoration:underline;">support@smartpark.com</a> or call us at <strong>+254 XXX XXX XXX</strong>.</p>
                
                <p style="color:#aaa;font-size:0.85rem;margin-top:24px;">&copy; ' . date('Y') . ' SmartPark. All rights reserved.</p>
            </div>
        </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Optionally log $mail->ErrorInfo
        return false;
    }
}

function send_password_reset_email($to, $to_name, $reset_link) {
    $mail = new PHPMailer(true);
    try {
        // Set timezone to UTC for universal compatibility
        date_default_timezone_set('UTC');
        
        //Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        //Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to, $to_name);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - SmartPark';
        
        // Calculate expiration time in user-friendly format
        $expires_at = gmdate('F j, Y \a\t g:i A T', strtotime('+1 hour'));
        
        $mail->Body = '
        <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(32,33,91,0.08);overflow:hidden;font-family:sans-serif;">
            <div style="background:#20215B;padding:32px 0;text-align:center;">
                <h1 style="color:#fff;font-size:2rem;margin:0;font-family:Pacifico, cursive;letter-spacing:1px;">SmartPark</h1>
                <p style="color:#fff;margin:8px 0 0 0;font-size:1.1rem;">Password Reset</p>
            </div>
            <div style="padding:32px 28px 24px 28px;">
                <h2 style="color:#20215B;font-size:1.5rem;margin-top:0;margin-bottom:12px;">Hello, ' . htmlspecialchars($to_name) . '!</h2>
                <p style="color:#444;font-size:1rem;line-height:1.6;margin-bottom:18px;">We received a request to reset your password for your SmartPark account.</p>
                
                <div style="background:#f4f6fa;border-radius:8px;padding:18px 16px;margin-bottom:18px;">
                    <h3 style="color:#20215B;margin-top:0;margin-bottom:8px;font-size:1.1rem;">Important Security Notice:</h3>
                    <ul style="color:#333;padding-left:18px;margin:0;font-size:0.98rem;">
                        <li>This link will expire on <strong>' . $expires_at . '</strong></li>
                        <li>If you didn\'t request this, please ignore this email</li>
                        <li>Never share this link with anyone</li>
                        <li>Your account security is our priority</li>
                    </ul>
                </div>
                
                <div style="text-align:center;margin-bottom:18px;">
                    <a href="' . $reset_link . '" style="display:inline-block;background:#20215B;color:#fff;text-decoration:none;padding:12px 32px;border-radius:6px;font-weight:600;font-size:1rem;box-shadow:0 2px 8px rgba(32,33,91,0.10);transition:background 0.2s;">Reset My Password</a>
                </div>
                
                <div style="background:#fff3cd;border-radius:8px;padding:16px;margin-bottom:18px;border-left:4px solid #ffc107;">
                    <h3 style="color:#856404;margin-top:0;margin-bottom:8px;font-size:1.1rem;">Can\'t click the button?</h3>
                    <p style="color:#856404;font-size:0.95rem;margin:0;">Copy and paste this link into your browser:</p>
                    <p style="color:#856404;font-size:0.9rem;margin:8px 0 0 0;word-break:break-all;">' . $reset_link . '</p>
                </div>
                
                <p style="color:#666;font-size:0.97rem;margin-bottom:0;">If you have any questions or need help, please contact our support team at <a href="mailto:support@smartpark.com" style="color:#20215B;text-decoration:underline;">support@smartpark.com</a>.</p>
                
                <p style="color:#aaa;font-size:0.85rem;margin-top:24px;">&copy; ' . gmdate('Y') . ' SmartPark. All rights reserved.</p>
            </div>
        </div>';

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Optionally log $mail->ErrorInfo
        return false;
    }
}
