<?php
require 'vendor/autoload.php'; // Adjust the path to PHPMailer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuration
$toEmail = 'your_email@example.com'; // Replace with your email address
$subject = 'Suggestion/Feedback from Website'; // Replace with your desired subject line

// Form HTML
echo '<html><body>';
echo '<h1>Suggestion/Feedback Form</h1>';
echo '<form action="" method="post">';
echo '<label for="name">Name:</label><br>';
echo '<input type="text" id="name" name="name"><br><br>';
echo '<label for="email">Email:</label><br>';
echo '<input type="email" id="email" name="email"><br><br>';
echo '<label for="message">Message:</label><br>';
echo '<textarea id="message" name="message"></textarea><br><br>';
echo '<input type="submit" value="Submit">';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Add hidden fields to the form
echo '<input type="hidden" name="honeypot" value="">';
echo '<input type="hidden" name="csrf_token" value="' . $csrfToken . '">';

echo '</form>';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name'));
    $email = trim(filter_input(INPUT_POST, 'email'));
    $message = trim(filter_input(INPUT_POST, 'message'));
    $csrfTokenPosted = filter_input(INPUT_POST, 'csrf_token');

    // Validate form input
    $errors = array();
    if (empty($name)) {
        $errors[] = 'Please enter your name.';
    }
    if (empty($email)) {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if (empty($message)) {
        $errors[] = 'Please enter your message.';
    }

    // Check for spam/bot submissions
    $honeypot = filter_input(INPUT_POST, 'honeypot');
    if (!empty($honeypot)) {
        $errors[] = 'Sorry, it looks like you\'re a bot!';
    }

    // Check for CSRF token
    if (!isset($csrfTokenPosted) || $csrfTokenPosted !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token.';
    }

    if (count($errors) > 0) {
        echo '<p>The following errors occurred:</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul>';
    } else {
        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Replace with your SMTP server address
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // Replace with your email address
            $mail->Password = 'your_email_password'; // Replace with your email password
            $mail->SMTPSecure = 'tls'; // or 'ssl'
            $mail->Port = 587; // or 465

            // Recipient
            $mail->setFrom($email, $name);
            $mail->addAddress($toEmail, 'Your Name'); // Replace with your name

            // Email body
            $body = "Name: $name\nEmail: $email\nMessage: $message";
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Send email
            if ($mail->send()) {
                echo '<p>Thank you for your suggestion/feedback!</p>';
            } else {
                echo '<p>Error sending email. Please try again later.</p>';
            }
        } catch (Exception $e) {
            echo '<p>Error sending email: ' . $e->getMessage() . '</p>';
        }
    }
}

echo '</body></html>';
