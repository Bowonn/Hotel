<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();

function getMonthlyRentalReport($startDate, $endDate)
{
    $sql = "SELECT room_name, COUNT(*) AS booking_count, SUM(total_amount) AS total_sales 
            FROM bookings 
            WHERE checkin BETWEEN ? AND ? 
            GROUP BY room_name 
            ORDER BY booking_count DESC";

    return select($sql, [$startDate, $endDate], 'ss');
}

function getHighBookingRooms($limit = 5)
{
    $sql = "SELECT room_name, COUNT(*) AS booking_count 
            FROM bookings 
            GROUP BY room_name 
            ORDER BY booking_count DESC 
            LIMIT ?";

    return select($sql, [$limit], 'i');
}

function getLowBookingRooms($limit = 5)
{
    $sql = "SELECT room_name, COUNT(*) AS booking_count 
            FROM bookings 
            GROUP BY room_name 
            ORDER BY booking_count ASC 
            LIMIT ?";

    return select($sql, [$limit], 'i');
}
$monthlyReport = getMonthlyRentalReport('2024-08-01', '2024-10-31');
$highBookingRooms = getHighBookingRooms();
$lowBookingRooms = getLowBookingRooms();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashbosrd</title>

    <?php
    require('inc/links.php');


    $is_shutdown = mysqli_fetch_assoc(mysqli_query($con, "SELECT `shutdown` FROM `settings`"));

    $current_bookings = mysqli_fetch_assoc(mysqli_query($con, "SELECT 
    COUNT(*) AS get_booking, 
    SUM(CASE WHEN payment_status = 'remove_user' THEN 1 ELSE 0 END) AS remove_user 
    FROM `bookings`"));


    $unread_queries = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(sr_no) AS `count`  
    FROM `user_queries` WHERE `seen`=0"));

    $current_users = mysqli_fetch_assoc(mysqli_query($con, "SELECT
    COUNT(id) AS `total`,  
    COUNT(CASE WHEN `status`=1 THEN 1 END) AS `active`,
    COUNT(CASE WHEN `status`=0 THEN 1 END) AS `inactive`,
    COUNT(CASE WHEN `is_verified`=0 THEN 1 END) AS `unverified`   
    FROM `user_cred`"));

    ?>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }

        .custom-button {
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            margin: 5px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .custom-button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }
    </style>


</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <div class="container-fluid" id="main-content">
        <div class="row">
            <div class="col-lg-10 ms-auto p-4 over-flow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h3>DASHBOARD</h3>
                    <?php
                    if ($is_shutdown['shutdown']) {
                        echo <<<data
                                <h6 class="badge bg-danger py-2 px-3 rounded">Shutdown Mode is Active!</h6>
                            data;
                    }
                    ?>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <a href="new_booking.php" class="text-decoration-none">
                            <div class="card text-center text-success p-3">
                                <h6>New Bookings</h6>
                                <h1 class="mt-2 mb-0"><?php echo $current_bookings['get_booking'] ?></h1>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="user_queries.php" class="text-decoration-none">
                            <div class="card text-center text-info p-3">
                                <h6>User Queries</h6>
                                <h1 class="mt-2 mb-0"><?php echo $unread_queries['count'] ?></h1>
                            </div>
                        </a>
                    </div>

                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5>Booking Analytics</h5>
                    <select class="form-select shadow-none bg-light w-auto" onchange="booking_analytics(this.value)">
                        <option value="1">Past 30 Days</option>
                        <option value="2">Past 90 Days</option>
                        <option value="3">Past 1 Year</option>
                        <option value="4">All time</option>
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 mb-4">
                        <div class="card text-center text-primary p-3">
                            <h6>Total Bookings</h6>
                            <h1 class="mt-2 mb-0" id="room_id"></h1>
                            <h4 class="mt-2 mb-0" id="total_amount"></h4>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5>User, Queries</h5>
                    <select class="form-select shadow-none bg-light w-auto" onchange="user_analytics(this.value)">
                        <option value="1">Past 30 Days</option>
                        <option value="2">Past 90 Days</option>
                        <option value="3">Past 1 Year</option>
                        <option value="4">All time</option>
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 mb-4">
                        <a href="users.php" class="text-decoration-none">
                            <div class="card text-center text-success p-3">
                                <h6>New Registration</h6>
                                <h1 class="mt-2 mb-0" id="total_new_reg">0</h1>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="user_queries.php" class="text-decoration-none">
                            <div class="card text-center text-primary p-3">
                                <h6>Queries</h6>
                                <h1 class="mt-2 mb-0" id="total_queries">0</h1>
                            </div>
                        </a>
                    </div>

                </div>

                <h5>Users</h5>
                <div class="row mb-3">
                    <div class="col-md-3 mb-4">
                        <div class="card text-center text-info p-3">
                            <h6>Total</h6>
                            <h1 class="mt-2 mb-0"><?php echo $current_users['total'] ?></h1>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center text-success p-3">
                            <h6>Active</h6>
                            <h1 class="mt-2 mb-0"><?php echo $current_users['active'] ?></h1>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center text-warning p-3">
                            <h6>Inactive</h6>
                            <h1 class="mt-2 mb-0"><?php echo $current_users['inactive'] ?></h1>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center text-danger p-3">
                            <h6>Unverified</h6>
                            <h1 class="mt-2 mb-0"><?php echo $current_users['unverified'] ?></h1>
                        </div>
                    </div>
                </div>

                <h5>Booking Analytics</h5>
                <div class="col-12 mb-3">
                    <button class="btn btn-primary custom-button" onclick="showSection('monthlyReport')">Monthly Rental Report</button>
                    <button class="btn btn-primary custom-button" onclick="showSection('highBookingRooms')">High Booking Rooms</button>
                    <button class="btn btn-primary custom-button" onclick="showSection('lowBookingRooms')">Low Booking Rooms</button>
                    <button class="btn btn-primary custom-button" onclick="showSection('monthlyChart')">Monthly Rental Chart</button>
                </div>
                <!-- Monthly Rental Report -->
                <div id="monthlyReport" class="col-md-6 mb-4">
                    <div class="card p-3">
                        <h6>Monthly Rental Report</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Room Name</th>
                                    <th>Booking Count</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($monthlyReport)): ?>
                                    <tr>
                                        <td><?php echo $row['room_name']; ?></td>
                                        <td><?php echo $row['booking_count']; ?></td>
                                        <td><?php echo number_format($row['total_sales'], 2); ?> ฿</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- High Booking Rooms -->
                <div id="highBookingRooms" class="col-md-6 mb-4" style="display: none;">
                    <div class="card p-3">
                        <h6>High Booking Rooms</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Room Name</th>
                                    <th>Booking Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($highBookingRooms)): ?>
                                    <tr>
                                        <td><?php echo $row['room_name']; ?></td>
                                        <td><?php echo $row['booking_count']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Low Booking Rooms -->
                <div id="lowBookingRooms" class="col-md-6 mb-4" style="display: none;">
                    <div class="card p-3">
                        <h6>Low Booking Rooms</h6>
                        <table>
                            <thead>
                                <tr>
                                    <th>Room Name</th>
                                    <th>Booking Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($lowBookingRooms)): ?>
                                    <tr>
                                        <td><?php echo $row['room_name']; ?></td>
                                        <td><?php echo $row['booking_count']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Monthly Rental Chart -->
                <div id="monthlyChart" class="col-md-6 mb-4" style="display: none;">
                    <div class="card p-3">
                        <h6>Monthly Rental Report (Chart)</h6>
                        <canvas id="rentalChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>
    <script src="scripts/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Function to show a section based on button click
        function showSection(sectionId) {
            const sections = ['monthlyReport', 'highBookingRooms', 'lowBookingRooms', 'monthlyChart'];
            sections.forEach(section => {
                document.getElementById(section).style.display = 'none';
            });

            document.getElementById(sectionId).style.display = 'block';

            // Initialize the chart if the monthly chart section is selected
            if (sectionId === 'monthlyChart') {
                loadChart();
            }
        }

        // Function to load the rental chart
        function loadChart() {
            const ctx = document.getElementById('rentalChart').getContext('2d');
            const rentalChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($monthlyReport as $row) {
                            echo "'" . $row['room_name'] . "',";
                        } ?>
                    ],
                    datasets: [{
                        label: 'Total Sales (฿)',
                        data: [
                            <?php foreach ($monthlyReport as $row) {
                                echo $row['total_sales'] . ",";
                            } ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>

</body>

</html>