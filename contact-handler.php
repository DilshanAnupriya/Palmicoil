<?php
require_once 'config/config.php';
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Validate and sanitize input
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');

    // Validation
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters long';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    if (!empty($phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number';
    }

    if (empty($subject)) {
        $errors[] = 'Subject is required';
    } elseif (strlen($subject) < 5) {
        $errors[] = 'Subject must be at least 5 characters long';
    }

    if (empty($message)) {
        $errors[] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Message must be at least 10 characters long';
    }

    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fix the following errors:',
            'errors' => $errors
        ]);
        exit;
    }

    // Save to database
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, 'unread', NOW(), NOW())
    ");
    
    $result = $stmt->execute([$name, $email, $phone, $subject, $message]);

    if ($result) {
        // Optional: Send email notification to admin
        $admin_email = 'admin@palmoilco.com'; // Change this to your admin email
        $email_subject = 'New Contact Form Submission - ' . $subject;
        $email_body = "
New contact form submission received:

Name: $name
Email: $email
Phone: $phone
Subject: $subject

Message:
$message

---
This message was sent from the contact form on your website.
        ";

        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Uncomment the line below to enable email notifications
        // mail($admin_email, $email_subject, $email_body, $headers);

        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.'
        ]);
    } else {
        throw new Exception('Failed to save message to database');
    }

} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again later.'
    ]);
}
?>