<?php
session_start(); // Start the session 

// Database connection
include 'database2.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($conn) { 
        
        $stmt = $conn->prepare("SELECT CUSTOMER_ID, FNAME, LNAME, USERNAME FROM CUSTOMER_INFO WHERE USERNAME = ? AND PASS = ?");
        
        if ($stmt) {
           
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if the account exists 
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $_SESSION['customer_id'] = $row['CUSTOMER_ID']; 
                $_SESSION['username'] = $row['USERNAME']; 
                $_SESSION['fname'] = $row['FNAME']; 
                $_SESSION['lname'] = $row['LNAME']; 
                
                header("Location: main.php"); // Redirect to main page 
                exit();
            } else {
                $error_message = "Account not found or incorrect credentials!";
            }

            $stmt->close();
        } else {
            $error_message = "Failed to prepare the SQL statement.";
        }
    } else {
        $error_message = "Failed to connect to the database.";
    }
    
    if ($conn) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        /* General Styling */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #4B0082;
            font-family: Arial, sans-serif;
            color: #D3D3D3;
        }
        
        /* Centered Form Container */
        .form-container {
            background-color: #333;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            width: 300px;
            text-align: center;
        }

        .form-container h2 {
            color: #FFFFFF;
            margin-bottom: 20px;
        }

        /* Input Styling */
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #D3D3D3;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* Button Styling */
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

        /* Link Styling */
        a {
            color: #00CED1;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }

        /* Error Message Styling */
        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Login</h2>
    <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>
    
    <form action="login.php" method="post">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        
        <input type="submit" value="Log In">
        
        <p>Don't have an account? <a href="register.php">Create Account</a></p>
    </form>
</div>

</body>
</html>
