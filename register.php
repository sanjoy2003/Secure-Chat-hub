<?php
include('database_connection.php');
session_start();

$message = '';

if (isset($_SESSION['user_id'])) {
    header('location:index.php');
}

if (isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $check_query = "
    SELECT * FROM login 
    WHERE username = :username
    ";
    $statement = $connect->prepare($check_query);
    $check_data = array(
        ':username'     => $username
    );
    if ($statement->execute($check_data)) {
        if ($statement->rowCount() > 0) {
            $message .= '<p><label>Username already taken</label></p>';
        } else {
            if (empty($username)) {
                $message .= '<p><label>Username is required</label></p>';
            }
            if (empty($password)) {
                $message .= '<p><label>Password is required</label></p>';
            } else {
                if ($password != $_POST['confirm_password']) {
                    $message .= '<p><label>Password not match</label></p>';
                }
            }
            if ($message == '') {
                $data = array(
                    ':username'     => $username,
                    ':password'     => password_hash($password, PASSWORD_DEFAULT)
                );

                $query = "
                INSERT INTO login 
                (username, password) 
                VALUES (:username, :password)
                ";
                $statement = $connect->prepare($query);
                if ($statement->execute($data)) {
                    $message = "<label>Registration Completed</label>";
                }
            }
        }
    }
}
?>

<html>  
<head>  
    <title>Chat Application using PHP Ajax Jquery</title>  
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        body {
            background-color: #f3e5ab; /* Changed background color */
            font-family: Arial, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dark-mode {
            background-color: #333;
            color: #f4f4f4;
        }

        .container {
            margin-top: 50px;
        }

        .panel {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .panel:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .panel-heading {
            font-size: 24px;
            font-weight: bold;
            color: #ff6347; /* Changed to red */
            text-shadow: 2px 2px #333; /* Added shadow for 3D effect */
        }

        .form-group label {
            font-weight: bold;
            color: #ff6347; /* Changed to red */
        }

        .form-control {
            border-radius: 5px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
            color: white;
            border-radius: 5px;
            transition: all 0.3s ease;
            box-shadow: 0 5px #138496;
            font-size: 20px; /* Increased button size */
        }

        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
            box-shadow: 0 5px #0c5460;
        }

        .btn-info:active {
            background-color: #117a8b;
            border-color: #0c5460;
            box-shadow: 0 2px #0c5460;
            transform: translateY(2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            border-radius: 5px;
            transition: all 0.3s ease;
            box-shadow: 0 5px #5a6268;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            box-shadow: 0 5px #4e555b;
        }

        .btn-secondary:active {
            background-color: #545b62;
            border-color: #4e555b;
            box-shadow: 0 2px #4e555b;
            transform: translateY(2px);
        }

        .text-center {
            text-align: center;
        }

        .dark-mode .btn-info, .dark-mode .btn-secondary {
            box-shadow: none;
        }

        .dark-mode .form-group label,
        .dark-mode .form-control {
            color: #f4f4f4;
        }

        #darkModeToggle {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background-color: #333;
            color: #f4f4f4;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: 5px;
        }

        .dark-mode #darkModeToggle {
            background-color: #f4f4f4;
            color: #333;
        }

        .dark-mode #alreadyRegisteredPanel {
            display: none; /* Hide the panel in dark mode */
        }

        h3, .panel-heading {
            animation: fadeIn 2s ease-in-out;
            text-align: center;
            font-size: 36px; /* Increased font size */
            color: #ff6347; /* Changed text color to red */
            text-shadow: 2px 2px #333; /* Added shadow for 3D effect */
        }

        .dark-mode h3, .dark-mode .panel-heading {
            color: #f4f4f4;
            text-shadow: 2px 2px #000; /* Adjusted shadow for dark mode */
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Italics style for success message */
        .registration-message {
            font-style: italic;
            color: green; /* Adjust color as needed */
            text-align: center;
            margin-top: 10px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#darkModeToggle').click(function() {
                $('body').toggleClass('dark-mode');
                const buttonText = $('body').hasClass('dark-mode') ? 'Switch to Light Mode' : 'Switch to Dark Mode';
                $(this).text(buttonText);
            });
        });
    </script>
</head>  
<body>  
    <button id="darkModeToggle" class="btn btn-secondary">Switch to Dark Mode</button>
    <div class="container">
        <br />
        <h3 align="center">Join Your Secure Chat Hub</h3><br />
        <br />
        <div class="panel panel-default">
            <div class="panel-heading">Register Your Account</div>
            <div class="panel-body">
                <span class="text-danger"><?php echo $message; ?></span>
                <form method="post">
                    <div class="form-group">
                        <label>Enter Username</label>
                        <input type="text" name="username" placeholder="Enter your username" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Enter Password</label>
                        <input type="password" name="password" placeholder="Enter your password" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Re-enter Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter your password" class="form-control" />
                    </div>
                    <div class="form-group text-center">
                        <input type="submit" name="register" class="btn btn-info" value="Register" />
                    </div>
                </form>
            </div>
        </div>
        <!-- Display success message after registration -->
        <?php if (!empty($message) && strpos($message, 'Registration Completed') !== false): ?>
            <div class="registration-message"><?php echo $message; ?></div>
        <?php endif; ?>
        <br>
        <div class="panel panel-default" id="alreadyRegisteredPanel">
            <div class="panel-heading">Already Registered?</div>
            <div class="panel-body text-center">
                <a href="login.php" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </div>
</body>  
</html>
