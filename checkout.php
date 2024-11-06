<?php

require __DIR__ . "/vendor/autoload.php";
require('admin/inc/db_config.php');

session_start();

if (!isset($_SESSION['room']) || !isset($_SESSION['room']['payment'])) {
    echo 'No booking information available.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = $_POST['name'];
    $phonenum = $_POST['phonenum'];
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];


    if (empty($name) || empty($phonenum) || empty($checkin) || empty($checkout)) {
        echo 'All booking details are required.';
        exit;
    }


    $room_id = $_SESSION['room']['id'];
    $room_name = $_SESSION['room']['name'];
    $total_amount = $_SESSION['room']['payment'];


    $total_amount_in_cents = $total_amount * 100;

    $stripe_secret_key = "sk_test_51Q3iK0ERrZyKQ4nIVftX4hAQxLnZ7790s9yqGB47dXCKR7R6j0gt0zBITSYxkoz6VYZimxSBKUU34UrGnXoFZqtS008SKCBWCn";
    \Stripe\Stripe::setApiKey($stripe_secret_key);

    try {

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'thb',
                        'unit_amount' => $total_amount_in_cents,
                        'product_data' => [
                            'name' => $room_name,
                            'description' => "Booking for room ID: $room_id",
                        ],
                    ],
                ],
            ],
            'success_url' => 'http://localhost/Hotel/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/Hotel/checkout.php',
        ]);


        $query = "INSERT INTO `bookings` (`name`, `phonenum`, `checkin`, `checkout`, `room_id`, `room_name`, `total_amount`, `payment_status`, `datentime`)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssiss", $name, $phonenum, $checkin, $checkout, $room_id, $room_name, $total_amount);

        if ($stmt->execute()) {

            http_response_code(303);
            header("Location: " . $session->url);
            exit;
        } else {
            echo 'Failed to save booking information to the database.';
            exit;
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo 'Error creating Stripe Checkout Session: ' . $e->getMessage();
        exit;
    }
} else {
    echo 'Invalid request method.';
    exit;
}
