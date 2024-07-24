<?php
include('database_connection.php');

session_start();

$message = '';

if (isset($_SESSION['user_id'])) {
    header('location:index.php');
}

if (isset($_POST['login'])) {
    $query = "
        SELECT * FROM login 
        WHERE username = :username
    ";
    $statement = $connect->prepare($query);
    $statement->execute(
        array(
            ':username' => $_POST["username"]
        )
    );
    $count = $statement->rowCount();
    if ($count > 0) {
        $result = $statement->fetchAll();
        foreach ($result as $row) {
            if (password_verify($_POST["password"], $row["password"])) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $sub_query = "
                INSERT INTO login_details 
                (user_id) 
                VALUES ('" . $row['user_id'] . "')
                ";
                $statement = $connect->prepare($sub_query);
                $statement->execute();
                $_SESSION['login_details_id'] = $connect->lastInsertId();
                header('location:index.php');
            } else {
                $message = '<label>Wrong Password</label>';
            }
        }
    } else {
        $message = '<label>Wrong Username</label>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Secure Chat Hub</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body.light-mode {
            background: linear-gradient(to right, #f3ca20, #bdc3c7);
            color: #333;
            font-size: 18px; /* Increased font size */
        }
        body.dark-mode {
            background: linear-gradient(to right, #232526, #414345);
            color: #fff;
            font-size: 18px; /* Increased font size */
        }
        .container {
            margin-top: 100px;
        }
        .panel {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            transform: translateZ(0);
            transition: all 0.3s ease;
        }
        .panel:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }
        .panel-heading {
            background: #007bff;
            color: #fff;
            font-size: 36px; /* Larger heading font size */
            font-weight: bold;
            text-align: center;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 20px 15px; /* Increased padding */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4); /* Text shadow for emphasis */
            position: relative; /* Position relative for absolute positioning */
        }
        .panel-heading::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.1); /* Semi-transparent overlay */
            top: 0;
            left: 0;
            animation: blink 2s infinite; /* Blink animation */
        }
        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0;
            }
        }
        .form-group label {
            color: #000;
        }
        .btn {
            width: auto;
            padding: 8px 15px; /* Slightly larger button */
            font-size: 18px; /* Increased button font size */
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
            border-radius: 5px;
        }
        .btn-info {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-info:hover {
            background-color: #0056b3;
            border-color: #004085;
            transform: translateY(-2px);
        }
        .btn-register {
            background-color: #28a745;
            border-color: #28a745;
            color: #fff;
            border-radius: 5px;
        }
        .btn-register:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }
        .text-danger {
            color: red;
        }
        a {
            color: #007bff;
            transition: color 0.3s;
        }
        a:hover {
            color: #0056b3;
        }
        .title {
            font-size: 2.5em; /* Larger title font size */
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 1px 1px 2px #000;
        }
        .mode-switch {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .mode-switch button {
            font-size: 12px;
            padding: 5px 10px;
        }
        .register-link {
            text-align: center;
            margin-top: 15px;
        }
        .typing-effect {
            font-size: 1.4em; /* Larger typing effect font size */
            text-align: center;
            color: #000;
            margin-top: 20px;
        }
        .typing-effect::after {
            content: '|';
            animation: blink 0.7s infinite;
        }
        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0;
            }
        }
    </style>
</head>
<body class="light-mode">
    <div class="mode-switch">
        <button onclick="toggleMode()" class="btn btn-info">Switch to Dark Mode</button>
    </div>
    <div class="container">
        <h1 class="title">Secure Chat Hub</h1>
        <div class="panel panel-default">
            <div class="panel-heading">Login to  Chat Hub</div>
            <div class="panel-body">
                <p class="text-danger"><?php echo $message; ?></p>
                <form method="post">
                    <div class="form-group">
                        <label>Enter Username</label>
                        <input type="text" name="username" placeholde= "enter Username"class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Enter Password</label>
                        <input type="password" name="password" placeholder="enter your password" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <input type="submit" name="login" class="btn btn-info" value="Login" />
                    </div>
                </form>
                <div class="register-link">
                    <a href="register.php" class="btn btn-register">Register</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleMode() {
            const body = document.body;
            const button = document.querySelector('.mode-switch button');
            if (body.classList.contains('light-mode')) {
                body.classList.replace('light-mode', 'dark-mode');
                button.textContent = 'Switch to Light Mode';
            } else {
                body.classList.replace('dark-mode', 'light-mode');
                button.textContent = 'Switch to Dark Mode';
            }
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            if (window.location.href.includes('index.php')) {
                const typingEffect = document.createElement('div');
                typingEffect.classList.add('typing-effect');
                typingEffect.innerHTML = 'Welcome to the Professional Chat Application!';
                document.querySelector('.container').appendChild(typingEffect);
                typeWriter();
            }
        });

        function typeWriter() {
            const text = document.querySelector('.typing-effect').innerHTML;
            const textArray = text.split('');
            let index = 0;
            document.querySelector('.typing-effect').innerHTML = '';
            function typing() {
                if (index < textArray.length) {
                    document.querySelector('.typing-effect').innerHTML += textArray[index];
                    index++;
                    setTimeout(typing, 100);
                }
            }
            typing();
        }
    </script>
</body>
</html>
