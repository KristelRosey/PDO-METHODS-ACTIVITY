<?php
session_start(); // Start session

if (!isset($_SESSION['customer_id'])) {
    // Redirect to login 
    header("Location: login.php");
    exit();
}

// Customer ID 
$customerId = $_SESSION['customer_id'];

// Database connection
include 'database2.php';

// Fetch customer information
$customerInfoQuery = "SELECT * FROM CUSTOMER_INFO WHERE CUSTOMER_ID = $customerId";
$customerInfoResult = mysqli_query($conn, $customerInfoQuery);
$customerInfo = mysqli_fetch_assoc($customerInfoResult);

// Handle customer info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateAccount'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $bdate = $_POST['bdate'];
    $contactNum = $_POST['contact_num'];
    $email = $_POST['email'];

    $updateQuery = "UPDATE CUSTOMER_INFO SET FNAME = '$fname', LNAME = '$lname', BDATE = '$bdate', CONTACT_NUM = '$contactNum', EMAIL = '$email' WHERE CUSTOMER_ID = $customerId";
    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Account information updated successfully');</script>";
        $customerInfoResult = mysqli_query($conn, $customerInfoQuery);
        $customerInfo = mysqli_fetch_assoc($customerInfoResult);
    } else {
        echo "<script>alert('Error updating account information');</script>";
    }
}

// Handle account deletion
if (isset($_POST['deleteAccount'])) {
    $deleteQuery = "DELETE FROM CUSTOMER_INFO WHERE CUSTOMER_ID = $customerId";
    if (mysqli_query($conn, $deleteQuery)) {
        // Destroy the session and redirect to login
        session_destroy();
        header("Location: login.php?message=Account deleted successfully");
        exit();
    } else {
        echo "<script>alert('Error deleting account: " . mysqli_error($conn) . "');</script>";
    }
}

// Fetch all room information
$allRoomsQuery = "SELECT ROOM_ID, ROOM_TYPE, STATUS FROM ROOMS";
$allRoomsResult = mysqli_query($conn, $allRoomsQuery);

// Fetch only vacant rooms 
$vacantRoomsQuery = "SELECT ROOM_ID, ROOM_TYPE FROM ROOMS WHERE STATUS = 'vacant'";
$vacantRoomsResult = mysqli_query($conn, $vacantRoomsQuery);

// Fetch all amenities 
$amenitiesQuery = "SELECT * FROM AMENITIES";
$amenitiesResult = mysqli_query($conn, $amenitiesQuery);
$amenities = mysqli_fetch_all($amenitiesResult, MYSQLI_ASSOC); 

// Handle reservation submission
$reservationMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookRoom'])) {
    $occupants = $_POST['occupants'];
    $pet = $_POST['pet'];
    $reservationDate = $_POST['reservation_date'];
    $timeIn = $_POST['time_in'];
    $timeOut = $_POST['time_out'];
    $roomId = $_POST['room_id'];

    // Check for existing reservations 
    $checkConflictQuery = "SELECT * FROM RESERVATION WHERE ROOM_ID = '$roomId' AND TIME_IN < '$timeOut' AND TIME_OUT > '$timeIn'";
    $conflictResult = mysqli_query($conn, $checkConflictQuery);

    if (mysqli_num_rows($conflictResult) > 0) {
        $reservationMessage = 'Room is already booked for the selected date and time. Please choose another date or room.';
    } else {
        // Insert new reservation
        $insertReservationQuery = "INSERT INTO RESERVATION (CUSTOMER_REF_NUM, CUSTOMER_NAME, NUM_OF_OCCUPANT, ROOM_TYPE, PET, TIME_IN, TIME_OUT, ROOM_ID) 
                                   VALUES ('$customerId', '{$customerInfo['FNAME']} {$customerInfo['LNAME']}', '$occupants', 
                                   (SELECT ROOM_TYPE FROM ROOMS WHERE ROOM_ID = '$roomId'), '$pet', '$timeIn', '$timeOut', '$roomId')";
        if (mysqli_query($conn, $insertReservationQuery)) {
            $reservationMessage = 'Reservation successful!';
        } else {
            $reservationMessage = 'Error in reservation: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Brookhaven Hotel - Main</title>
    <style>
        /* General styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #4B0082;
            color: #D3D3D3;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo {
            font-size: 2em;
            color: #D3D3D3;
            margin-top: 20px;
        }
        .nav-bar {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 20px;
            position: relative;
        }
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        .nav-button {
            background-color: #6A0DAD;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .nav-button:hover {
            background-color: #5A009D;
        }
        .logout-button {
            position: absolute;
            right: 20px;
            background-color: #FF4500;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .section {
            display: none;
            width: 80%;
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }
        .visible {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #D3D3D3;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .reservation-message {
            color: #FFA500;
            font-weight: bold;
            text-align: center;
        }
        .button {
            background-color: #6A0DAD;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #5A009D;
        }
        .delete-button {
            background-color: #FF4500;
        }
        .delete-button:hover {
            background-color: #FF0000;
        }
    </style>
</head>
<body>

<div class="logo">Brookhaven Hotel</div>

<div class="nav-bar">
    <div class="nav-buttons">
        <button class="nav-button" onclick="showSection('myAccount')">My Account</button>
        <button class="nav-button" onclick="showSection('roomsReservation')">Rooms & Reservation</button>
        <button class="nav-button" onclick="showSection('amenities')">Amenities</button>
        <button class="nav-button" onclick="showSection('paymentDetails')">Payment Details</button>
    </div>
    <button class="logout-button" onclick="confirmLogout()">Logout</button>
</div>

<!-- My Account Section -->
<div class="section" id="myAccount">
    <h2>My Account</h2>
    <form action="main.php" method="post">
        <p>First Name: <input type="text" name="fname" value="<?= $customerInfo['FNAME'] ?>" readonly required></p>
        <p>Last Name: <input type="text" name="lname" value="<?= $customerInfo['LNAME'] ?>" readonly required></p>
        <p>Birth Date: <input type="date" name="bdate" value="<?= $customerInfo['BDATE'] ?>" readonly required></p>
        <p>Contact Number: <input type="text" name="contact_num" value="<?= $customerInfo['CONTACT_NUM'] ?>" readonly required></p>
        <p>Email: <input type="email" name="email" value="<?= $customerInfo['EMAIL'] ?>" readonly required></p>
        <button type="button" onclick="enableEdit()" class="button">Edit</button>
        <button type="submit" name="updateAccount" class="button" style="display:none;" id="saveButton">Save</button>
    </form>
    <form action="main.php" method="post">
        <button type="submit" name="deleteAccount" class="button delete-button" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete My Account</button>
    </form>
</div>

<!-- Rooms & Reservation Section -->
<div class="section" id="roomsReservation">
    <h2>Book a Room</h2>
    <form action="main.php" method="post">
        <p>Number of Occupants: <input type="number" name="occupants" required></p>
        <p>Pet: 
            <label><input type="radio" name="pet" value="Yes"> Yes</label>
            <label><input type="radio" name="pet" value="No" checked> No</label>
        </p>
        <p>Reservation Date: <input type="date" name="reservation_date" required></p>
        <p>Time In: <input type="time" name="time_in" required></p>
        <p>Time Out: <input type="time" name="time_out" required></p>
        <p>Select Room: 
            <select name="room_id" required>
                <?php while ($vacantRoom = mysqli_fetch_assoc($vacantRoomsResult)) : ?>
                    <option value="<?= $vacantRoom['ROOM_ID'] ?>"><?= $vacantRoom['ROOM_TYPE'] ?> (<?= $vacantRoom['ROOM_ID'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </p>
        <button type="submit" name="bookRoom" class="button">Book Room</button>
        <div class="reservation-message"><?= $reservationMessage ?></div>
    </form>
</div>

<!-- Amenities Section -->
<div class="section" id="amenities">
    <h2>Amenities</h2>
    <?php if (!empty($amenities)): ?>
        <table>
            <tr>
                <th>Amenity</th>
                <th>Available</th>
            </tr>
            <tr>
                <td>Pool</td>
                <td><?= $amenities[0]['POOL'] ? 'Yes' : 'No' ?></td>
            </tr>
            <tr>
                <td>Pet Friendly</td>
                <td><?= $amenities[0]['PET'] ? 'Yes' : 'No' ?></td>
            </tr>
            <tr>
                <td>Snack Bar</td>
                <td><?= $amenities[0]['SNACK_BAR'] ? 'Yes' : 'No' ?></td>
            </tr>
            <tr>
                <td>Arcade</td>
                <td><?= $amenities[0]['ARCADE'] ? 'Yes' : 'No' ?></td>
            </tr>
            <tr>
                <td>Spa</td>
                <td><?= $amenities[0]['SPA'] ? 'Yes' : 'No' ?></td>
            </tr>
        </table>
    <?php else: ?>
        <p>No amenities found.</p>
    <?php endif; ?>
</div>

<!-- Payment Details Section -->
<div class="section" id="paymentDetails">
    <h2>Payment Details</h2>
    <p>Payment information goes here.</p>
</div>

<script>
    function showSection(sectionId) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => section.classList.remove('visible'));
        // Show the selected section
        document.getElementById(sectionId).classList.add('visible');
    }

    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = 'login.php'; // Add your logout script here
        }
    }

    function enableEdit() {
        const inputs = document.querySelectorAll('#myAccount input');
        inputs.forEach(input => {
            input.readOnly = false; // Enable editing
        });
        document.getElementById('saveButton').style.display = 'block'; // Show save button
    }
</script>
</body>
</html>
