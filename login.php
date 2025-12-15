<?php
session_start();
$koneksi = new mysqli("localhost", "root", "", "laundry_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek user di database
    $query = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $result = $koneksi->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cuci.in Laundry Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
            position: relative;
        }

        /* Animated Bubbles Background */
        .bubbles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            opacity: 0.6;
            animation: rise 15s infinite ease-in;
        }

        .bubble:nth-child(1) {
            width: 80px;
            height: 80px;
            left: 10%;
            animation-duration: 12s;
            animation-delay: 0s;
        }

        .bubble:nth-child(2) {
            width: 60px;
            height: 60px;
            left: 20%;
            animation-duration: 10s;
            animation-delay: 2s;
        }

        .bubble:nth-child(3) {
            width: 100px;
            height: 100px;
            left: 35%;
            animation-duration: 14s;
            animation-delay: 4s;
        }

        .bubble:nth-child(4) {
            width: 70px;
            height: 70px;
            left: 50%;
            animation-duration: 11s;
            animation-delay: 0s;
        }

        .bubble:nth-child(5) {
            width: 90px;
            height: 90px;
            left: 65%;
            animation-duration: 13s;
            animation-delay: 3s;
        }

        .bubble:nth-child(6) {
            width: 50px;
            height: 50px;
            left: 80%;
            animation-duration: 9s;
            animation-delay: 1s;
        }

        .bubble:nth-child(7) {
            width: 120px;
            height: 120px;
            left: 90%;
            animation-duration: 15s;
            animation-delay: 5s;
        }

        .bubble:nth-child(8) {
            width: 40px;
            height: 40px;
            left: 15%;
            animation-duration: 8s;
            animation-delay: 2s;
        }

        .bubble:nth-child(9) {
            width: 75px;
            height: 75px;
            left: 45%;
            animation-duration: 12s;
            animation-delay: 1s;
        }

        .bubble:nth-child(10) {
            width: 85px;
            height: 85px;
            left: 75%;
            animation-duration: 11s;
            animation-delay: 4s;
        }

        @keyframes rise {
            0% {
                bottom: -100px;
                transform: translateX(0) rotate(0deg);
                opacity: 0.6;
            }
            50% {
                transform: translateX(100px) rotate(180deg);
                opacity: 0.4;
            }
            100% {
                bottom: 120%;
                transform: translateX(-200px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Washing Machine Animation */
        .washing-machine {
            position: fixed;
            top: 10%;
            right: 10%;
            width: 150px;
            height: 150px;
            z-index: 1;
            opacity: 0.2;
        }

        .washing-circle {
            width: 100%;
            height: 100%;
            border: 15px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            position: relative;
            animation: spin 3s linear infinite;
        }

        .washing-circle::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            height: 70%;
            border: 10px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
            width: 450px;
            max-width: 90%;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        .logo-icon i {
            font-size: 3rem;
            color: white;
        }

        .logo-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .logo-section p {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Error Alert */
        .error-alert {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1));
            border: 2px solid #ef4444;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-alert i {
            color: #ef4444;
            font-size: 1.5rem;
        }

        .error-alert span {
            color: #dc2626;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            color: #475569;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: #667eea;
            font-size: 1.1rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control:focus + .input-icon {
            color: #667eea;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            padding: 0.5rem;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        /* Remember Me */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .remember-me label {
            color: #64748b;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #764ba2;
        }

        /* Submit Button */
        .btn-login {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e2e8f0;
        }

        .login-footer p {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }

        .feature-item {
            text-align: center;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 0.5rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 1.5rem;
        }

        .feature-item p {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 2rem 1.5rem;
                width: 95%;
            }

            .logo-section h1 {
                font-size: 2rem;
            }

            .washing-machine {
                width: 100px;
                height: 100px;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
        }

        .loading.active {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spinner 0.8s linear infinite;
        }

        @keyframes spinner {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Animated Bubbles -->
    <div class="bubbles-container">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <!-- Washing Machine Animation -->
    <div class="washing-machine">
        <div class="washing-circle"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-soap"></i>
            </div>
            <h1>Cuci.in</h1>
            <p>Sistem Manajemen Laundry Modern</p>
        </div>

        <!-- Error Alert -->
        <?php if (isset($error_message)): ?>
        <div class="error-alert">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error_message; ?></span>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label>
                    <i class="fas fa-user"></i>
                    Username
                </label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        placeholder="Masukkan username Anda" 
                        required
                        autocomplete="username"
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="form-control" 
                        placeholder="Masukkan password Anda" 
                        required
                        autocomplete="current-password"
                    >
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-forgot">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat Saya</label>
                </div>
                <a href="#" class="forgot-password">Lupa Password?</a>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span id="btnText">Masuk ke Dashboard</span>
                <div class="loading" id="loading"></div>
            </button>
        </form>

        <!-- Features -->
        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <p>Keamanan Terjamin</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <p>Akses Cepat</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <p>Laporan Real-time</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>&copy; 2025 Cuci.in. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form Submit Animation
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const loading = document.getElementById('loading');
            
            btnText.style.display = 'none';
            loading.classList.add('active');
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.8';
        });

        // Add ripple effect on button click
        document.querySelector('.btn-login').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.5)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s ease-out';
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Auto-focus on username input
        window.addEventListener('load', function() {
            document.querySelector('input[name="username"]').focus();
        });

        // Add enter key support
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('loginForm').submit();
                }
            });
        });
    </script>
</body>
</html>