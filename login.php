<?php
// login.php
session_start();

$dbhost = 'localhost'; 
$dbuser = 'root'; 
$dbpass = ''; 
$db = 'it';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $db);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? mysqli_real_escape_string($conn, trim($_POST['username'])) : '';
    $password = isset($_POST['password']) ? mysqli_real_escape_string($conn, trim($_POST['password'])) : '';

    if (!empty($username) && !empty($password)) {
        // Admin table එකෙන් User පරීක්ෂා කිරීම
        $query = "SELECT * FROM Admin WHERE Username = '$username' AND password = '$password' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = !empty($user['Full_name']) ? $user['Full_name'] : $user['Username'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Username හෝ Password වැරදියි!";
        }
    } else {
        $error = "කරුණාකර සියලු විස්තර ඇතුළත් කරන්න!";
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HNDIT Examination System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            background-color: #002060;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
            border-top: 5px solid #FFC000;
        }
        .logo-container img {
            width: 90px;
            height: auto;
            margin-bottom: 15px;
        }
        .login-card h2 {
            color: #002060;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .login-card p.subtitle {
            color: #666;
            font-size: 13px;
            margin-bottom: 25px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #002060;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #dcdcdc;
            border-radius: 6px;
            font-size: 13.5px;
            outline: none;
            transition: all 0.3s;
        }
        .form-group input:focus {
            border-color: #002060;
            box-shadow: 0 0 5px rgba(0,32,96,0.2);
        }

        /* Password Wrapper & Toggle Eye Button */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            padding-right: 42px; /* Eye icon එක මත අකුරු නොවැටීමට */
        }
        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            outline: none;
        }
        .toggle-password-btn svg {
            width: 20px;
            height: 20px;
            fill: #777;
            transition: fill 0.2s;
        }
        .toggle-password-btn:hover svg {
            fill: #002060;
        }

        .btn-login {
            width: 100%;
            background-color: #002060;
            color: white;
            border: none;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background-color: #001540;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="SL.jpg" alt="SLIATE Logo">
        </div>
        <h2>HNDIT EXAMS PORTAL</h2>
        <p class="subtitle">Examination Results Management System</p>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Username / Index No:</label>
                <input type="text" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <div class="password-wrapper">
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                    <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()">
                        <!-- Eye Icon SVG -->
                        <svg id="eyeIcon" viewBox="0 0 24 24">
                            <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">LOGIN</button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            var pwdField = document.getElementById('loginPassword');
            var eyeIcon = document.getElementById('eyeIcon');
            
            if (pwdField.type === 'password') {
                pwdField.type = 'text';
                // Password එක පෙනෙන විට Eye Icon එකෙහි වර්ණය වෙනස් කර පෙන්වීම
                eyeIcon.style.fill = '#002060';
            } else {
                pwdField.type = 'password';
                eyeIcon.style.fill = '#777';
            }
        }
    </script>
</body>
</html>