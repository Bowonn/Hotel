<?php

require __DIR__ . "/vendor/autoload.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


$stripe_secret_key = "sk_test_51Q3iK0ERrZyKQ4nIVftX4hAQxLnZ7790s9yqGB47dXCKR7R6j0gt0zBITSYxkoz6VYZimxSBKUU34UrGnXoFZqtS008SKCBWCn";
\Stripe\Stripe::setApiKey($stripe_secret_key);


if (!isset($_GET['session_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing session_id parameter']);
    exit;
}


$hname = 'localhost';
$uname = 'root';
$pass = '';
$db = 'hdwensite';

$con = mysqli_connect($hname, $uname, $pass, $db);

if (!$con) {
    die("Cannot connect to the database: " . mysqli_connect_error());
}

try {
    $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);

    if ($session->payment_status == 'paid') {

        $amount = $session->amount_total / 100;
        $currency = strtoupper($session->currency);
        $product_name = isset($_SESSION['room']['name']) ? $_SESSION['room']['name'] : 'ไม่ระบุ';
        $payment_date = date('Y-m-d H:i:s', $session->created);


        $sql = "INSERT INTO payments (session_id, product_name, amount, currency, payment_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdss', $_GET['session_id'], $product_name, $amount, $currency, $payment_date);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = 'Thank you for your payment!';
        } else {
            echo "Error: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);

?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo 'Receipt - ' . $product_name; ?></title>
            <?php require('inc/links.php'); ?>
            <style>
                .pop:hover {
                    border-top-color: var(--teal) !important;
                    transform: scale(1.03);
                    transition: all 0.3s;
                }

                .alert {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 1050;
                    width: auto;
                    transition: opacity 0.3s ease;
                }
            </style>
        </head>

        <body class="bg-light">
            <?php require('inc/header.php'); ?>

            <div class="container">
                <div class="row my-5">
                    <div class="col-lg-7 col-md-12 px-4">
                        <div class="card p-3 shadow-sm rounded">
                            <h1>Receipt</h1>
                            <p><strong>Product name:</strong> <?php echo $product_name; ?></p>
                            <p><strong>Amount:</strong> <?php echo $amount . ' ' . $currency; ?></p>
                            <p><strong>Date and time:</strong> <?php echo $payment_date; ?></p>

                            <?php
                            if (isset($_SESSION['alert'])) {
                                echo "<div class='alert alert-success' role='alert' id='paymentAlert'>{$_SESSION['alert']}</div>";
                                unset($_SESSION['alert']);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php require('inc/footer.php'); ?>

            <script>
                window.onload = function() {
                    var alert = document.getElementById('paymentAlert');
                    if (alert) {
                        setTimeout(function() {
                            alert.style.opacity = '0';
                            setTimeout(function() {
                                alert.style.display = 'none';
                            }, 300);
                        }, 3000);
                    }
                };
            </script>
        </body>

        </html>
<?php
    } else {
        echo "การชำระเงินล้มเหลว!";
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    mysqli_close($con);
}
?>