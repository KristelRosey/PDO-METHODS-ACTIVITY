<?php
// Database connection
include 'database2.php';

$account_created = false;
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $bdate = $_POST['bdate'];
    $contact_num = $_POST['contact_num'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the email exists
    $check_email_query = "SELECT * FROM CUSTOMER_INFO WHERE EMAIL='$email'";
    $result = mysqli_query($conn, $check_email_query);

    if (mysqli_num_rows($result) > 0) {
        $error_message = "An account with this email already exists.";
    } else {
        // new account
        $sql = "INSERT INTO CUSTOMER_INFO (FNAME, LNAME, BDATE, CONTACT_NUM, EMAIL, USERNAME, PASS) 
                VALUES ('$fname', '$lname', '$bdate', '$contact_num', '$email', '$username', '$password')";

        if (mysqli_query($conn, $sql)) {
            $account_created = true; 
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <style>
        body {
            background-color: #4B0082;
            color: #D3D3D3;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .back-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #6A0DAD;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        .form-container {
            background-color: #333;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            text-align: center;
        }
        input[type="text"], input[type="date"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #D3D3D3;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #6A0DAD;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #5A009D;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            color: #D3D3D3;
            width: 300px;
        }
        .modal-content h3 {
            margin: 0 0 20px 0;
        }
        .modal-button {
            background-color: #6A0DAD;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Back Button -->
<a href="login.php" class="back-button">Back</a>

<div class="form-container">
    <h2>Create Account</h2>
    <?php if (!empty($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>

    <form action="register.php" method="post">
        <label>First Name</label>
        <input type="text" name="fname" required><br>
        
        <label>Last Name</label>
        <input type="text" name="lname" required><br>
        
        <label>Birth Date</label>
        <input type="date" name="bdate" required><br>
        
        <label>Contact Number</label>
        <input type="text" name="contact_num" required><br>
        
        <label>Email</label>
        <input type="text" name="email" required><br>
        
        <label>Username</label>
        <input type="text" name="username" required><br>
        
        <label>Password</label>
        <input type="password" name="password" required><br>
        
        <input type="submit" value="Sign Up">
    </form>
</div>


<?php if ($account_created) : ?>
<div class="modal" id="successModal">
    <div class="modal-content">
        <h3>Account Successfully Created!</h3>
        <button class="modal-button" onclick="redirectToLogin()">Okay</button>
    </div>
</div>
<script>
    document.getElementById("successModal").style.display = "flex";

    function redirectToLogin() {
        window.location.href = "login.php";
    }
</script>
<?php endif; ?>

</body>
</html>
