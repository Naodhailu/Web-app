<?php
session_start();

// Redirect to welcome if already logged in
if (isset($_SESSION['username'])) {
    header('Location: welcome.php');
    exit;
}

$error = '';
$success = '';
$show_signup = false;
$action = '';
$username = '';

// Simple JSON file for data storage (so you don't have to setup MySQL yet)
$users_file = 'users.json';
if (!file_exists($users_file)) {
    file_put_contents($users_file, json_encode([]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $users = json_decode(file_get_contents($users_file), true);
    
    if ($action === 'login') {
        if (isset($users[$username])) {
            if ($users[$username] === $password) {
                // Correct password
                $_SESSION['username'] = $username;
                header('Location: welcome.php');
                exit;
            } else {
                // Wrong password
                $error = 'The password was wrong. Please try again.';
            }
        } else {
            // User not found
            $error = 'User not found. Please sign up to create an account.';
        }
    } elseif ($action === 'signup') {
        $show_signup = true;
        if (isset($users[$username])) {
            $error = 'Username already exists. Please login.';
        } else {
            $users[$username] = $password;
            if(file_put_contents($users_file, json_encode($users))) {
                $success = 'Account created successfully! You can now login.';
                $show_signup = false; // Switch to login form so they can log in
            } else {
                $error = 'Failed to create account. Server error.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachemo SE Section A - Login</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <div class="container">
        <div class="form-container <?php echo $show_signup ? 'show-signup' : ''; ?>" id="form-container">
            
            <!-- Login Form -->
            <form class="form login-form" id="login-form" method="POST" action="index.php">
                <input type="hidden" name="action" value="login">
                <div class="header">
                    <h1>Wachemo SE Section A</h1>
                    <p>Welcome back! Please login.</p>
                </div>
                
                <?php if ($error && !$show_signup): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success && !$show_signup): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="input-group">
                    <input type="text" id="login-username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($action === 'login' ? $username : ''); ?>">
                    <label for="login-username">Username</label>
                </div>
                <div class="input-group">
                    <input type="password" id="login-password" name="password" required autocomplete="current-password">
                    <label for="login-password">Password</label>
                </div>
                <button type="submit" class="btn">Login</button>
                <div class="toggle-text">
                    Don't have an account? <span id="to-signup">Sign up</span>
                </div>
            </form>

            <!-- Sign Up Form -->
            <form class="form signup-form" id="signup-form" method="POST" action="index.php">
                <input type="hidden" name="action" value="signup">
                <div class="header">
                    <h1>Create Account</h1>
                    <p>Join Wachemo SE Section A portal.</p>
                </div>
                
                <?php if ($error && $show_signup): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="input-group">
                    <input type="text" id="signup-username" name="username" required autocomplete="username" value="<?php echo htmlspecialchars($action === 'signup' ? $username : ''); ?>">
                    <label for="signup-username">Username</label>
                </div>
                <div class="input-group">
                    <input type="password" id="signup-password" name="password" required autocomplete="new-password">
                    <label for="signup-password">Password</label>
                </div>
                <button type="submit" class="btn">Sign Up</button>
                <div class="toggle-text">
                    Already have an account? <span id="to-login">Login</span>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
