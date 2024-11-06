<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['booking_analytics'])) {
    $frm_data = filteration($_POST);
    $condition = "";

    if ($frm_data['period'] == 1) {
        $condition = "WHERE datentime BETWEEN NOW() - INTERVAL 30 DAY AND NOW()";
    } else if ($frm_data['period'] == 2) {
        $condition = "WHERE datentime BETWEEN NOW() - INTERVAL 90 DAY AND NOW()";
    } else if ($frm_data['period'] == 3) {
        $condition = "WHERE datentime BETWEEN NOW() - INTERVAL 1 YEAR AND NOW()";
    }

    $query = "SELECT 
        COUNT(id) AS `total_bookings`,
        SUM(total_amount) AS `total_amount`,
        COUNT(CASE WHEN payment_status = 'remove_user' THEN 1 ELSE 0 END) AS `remove_user` 
        FROM `bookings` $condition";

    $result = mysqli_fetch_assoc(mysqli_query($con, $query));

    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'Unable to fetch booking analytics']);
    }

    exit;
}

if (isset($_POST['user_analytics'])) {
    $frm_data = filteration($_POST);
    $condition = "";

    if ($frm_data['period'] == 1) {
        $condition = "WHERE datentime BETWEEN NOW() - INTERVAL 30 DAY AND NOW()";
    } else if ($frm_data['period'] == 2) {
        $condition = "WHERE datentime BETWEEN NOW() - INTERVAL 90 DAY AND NOW()";
    } else if ($frm_data['period'] == 3) {
        $condition = "WHERE datentime BETWEEN NOW() - INTERVAL 1 YEAR AND NOW()";
    }

    $total_queries = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(sr_no) AS `count` FROM `user_queries` $condition"));
    $total_new_reg = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(id) AS `count` FROM `user_cred` $condition"));

    if ($total_queries && $total_new_reg) {
        $output = [
            'total_queries' => $total_queries['count'] ?? 0,
            'total_new_reg' => $total_new_reg['count'] ?? 0,
        ];
        echo json_encode($output);
    } else {
        echo json_encode(['error' => 'Unable to fetch user analytics']);
    }

    exit;
}
