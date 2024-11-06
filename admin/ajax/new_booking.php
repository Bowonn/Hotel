<?php
require('../inc/db_config.php');
require('../inc/essentials.php');
adminLogin();

if (isset($_POST['get_booking'])) {

    $frm_data = filteration($_POST);

    $query = "SELECT * FROM `bookings`";
    $res = mysqli_query($con, $query);
    $i = 1;
    $table_data = "";

    while ($data = mysqli_fetch_assoc($res)) {

        $datentime = date("d-m-Y", strtotime($data['datentime']));
        $checkin = date("d-m-Y", strtotime($data['checkin']));
        $checkout = date("d-m-Y", strtotime($data['checkout']));

        $table_data .= "
        <tr>
            <td>$i</td>
            <td>
                <span class='badge bg-primary'>
                    Order ID: $data[id]
                </span>
                <br>
                <b>Name :</b> $data[name]
                <br>
                <b>Phone No: :</b> $data[phonenum]
                <br>
            </td>
            <td>
                <b>Room :</b> $data[room_name]
                <br>
                <b>Price :</b> $data[total_amount] ฿
                <br>
            </td>
            <td>
                <b>Check in:</b> $checkin
                <br>
                <b>Check out:</b> $checkout
                <br>
            </td>
            <td>
                <button type='button' onclick='delete_booking($data[id])' class='m-2 btn btn-outline-danger btn-sm fe-bold shadow-none'>
                    <i class='bi bi-trash'></i> Dalete Booking
                </button>
            </td>
        </tr>
    ";
        $i++;
    }
    echo $table_data;
}

if (isset($_POST['delete_booking'])) {
    $frm_data = filteration($_POST);

    $res = delete("DELETE FROM `bookings` WHERE `id`=?", [$frm_data['id']], 'i');

    if ($res) {
        echo 1;
    } else {
        echo 0;
    }
}
