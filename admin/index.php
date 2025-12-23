<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../autoloader.php';

use App\Services\Auth;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\Response;

$auth = Auth::getInstance();
$session = Session::getInstance();
$csrf = new CSRF();

// Check if already logged in
if ($auth->check()) {
    header('Location: dashboard.php');
    exit;
}

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Response::setCorsHeaders();
    
    // Get JSON data or form data
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }
    
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $remember = !empty($data['remember']);
    $csrfToken = $data['csrf_token'] ?? '';
    
    // Validate CSRF
    if (!$csrf->validateToken($csrfToken)) {
        if (strpos($contentType, 'application/json') !== false) {
            Response::error('Token bảo mật không hợp lệ. Vui lòng tải lại trang.', 'CSRF_ERROR', 403);
        } else {
            $error = 'Token bảo mật không hợp lệ. Vui lòng tải lại trang.';
        }
    } else {
        // Attempt login
        $result = $auth->attempt($username, $password, $remember);
        
        if (strpos($contentType, 'application/json') !== false) {
            if ($result['success']) {
                Response::success(['redirect' => 'dashboard.php'], $result['message']);
            } else {
                Response::error($result['message'], 'LOGIN_FAILED', 401);
            }
        } else {
            if ($result['success']) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrfToken = $csrf->getToken();

// Check if session expired or kicked by another login
$sessionExpired = isset($_GET['expired']) && $_GET['expired'] == '1';
$kickedByOther = isset($_GET['kicked']) && $_GET['kicked'] == '1';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Admin - ICOGroup</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../logo.ico">
    <style>
        /* ===== CSS Variables ===== */
        :root {
            --primary: #2563EB;
            --primary-dark: #1E3A5F;
            --primary-light: #3B82F6;
            --accent: #F59E0B;
            --accent-hover: #D97706;
            --success: #10B981;
            --success-light: #34D399;
            --danger: #EF4444;
            --danger-light: #FCA5A5;
            --bg-gradient-start: #0F172A;
            --bg-gradient-end: #1E293B;
            --surface: #FFFFFF;
            --surface-hover: #F8FAFC;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-muted: #94A3B8;
            --text-white: #FFFFFF;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-glow: 0 0 40px rgba(37, 99, 235, 0.15);
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(245, 158, 11, 0.06) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-20px, 20px) rotate(5deg); }
        }

        .login-container {
            background: var(--surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl), var(--shadow-glow);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0F172A 100%);
            padding: 48px 40px 40px;
            text-align: center;
            color: var(--text-white);
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .login-header img {
            height: 56px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            filter: brightness(0) invert(1);
            transition: transform var(--transition-normal);
        }

        .login-header img:hover { transform: scale(1.05); }
        .login-header h1 { font-size: 26px; font-weight: 700; margin-bottom: 8px; position: relative; z-index: 1; letter-spacing: -0.02em; }
        .login-header p { opacity: 0.75; font-size: 15px; position: relative; z-index: 1; font-weight: 400; }

        .login-form { padding: 40px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-weight: 600; font-size: 14px; color: var(--text-primary); margin-bottom: 8px; letter-spacing: -0.01em; }
        .input-wrapper { position: relative; }
        .input-wrapper .material-icons-outlined { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 20px; transition: color var(--transition-fast); }

        .form-group input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E2E8F0;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-family: inherit;
            transition: all var(--transition-fast);
            outline: none;
            background: var(--surface);
        }

        .form-group input:hover { border-color: #CBD5E1; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .form-group input:focus + .material-icons-outlined, .input-wrapper:focus-within .material-icons-outlined { color: var(--primary); }
        .form-group input::placeholder { color: var(--text-muted); }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color var(--transition-fast);
        }

        .password-toggle:hover { color: var(--text-secondary); }
        .password-toggle .material-icons-outlined { position: static; transform: none; font-size: 20px; }

        .error-message {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            color: var(--danger);
            padding: 14px 16px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
            display: none;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--danger-light);
            animation: shake 0.5s cubic-bezier(0.36, 0.07, 0.19, 0.97);
        }

        .error-message.show { display: flex; }
        .error-message .material-icons-outlined { font-size: 20px; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }

        .remember-group { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; }
        .remember-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary); border-radius: 4px; }
        .remember-group label { font-size: 14px; color: var(--text-secondary); cursor: pointer; user-select: none; transition: color var(--transition-fast); }
        .remember-group label:hover { color: var(--text-primary); }

        .login-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--success) 0%, var(--success-light) 100%);
            color: var(--text-white);
            border: none;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); }
        .login-btn:hover::before { left: 100%; }
        .login-btn:active { transform: translateY(0); }
        .login-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .login-btn .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        .login-btn.loading .spinner { display: block; }
        .login-btn.loading .btn-text { display: none; }

        @keyframes spin { to { transform: rotate(360deg); } }

        .login-footer { text-align: center; padding: 0 40px 32px; }
        .login-footer a { color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; transition: all var(--transition-fast); }
        .login-footer a:hover { color: var(--primary-dark); }
        .login-footer a .material-icons-outlined { font-size: 18px; transition: transform var(--transition-fast); }
        .login-footer a:hover .material-icons-outlined { transform: translateX(-3px); }

        @media (max-width: 480px) {
            .login-container { max-width: 100%; }
            .login-header { padding: 36px 24px 32px; }
            .login-header h1 { font-size: 22px; }
            .login-form { padding: 32px 24px; }
            .login-footer { padding: 0 24px 24px; }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <a href="../fonend/index.php" title="Về trang chủ">
                <img src="../hi.jpg" alt="Logo" style="filter: none; height: 80px; border-radius: 8px;">
            </a>
            <h1>Admin Panel</h1>
            <p>Đăng nhập để quản lý hệ thống</p>
        </div>

        <form class="login-form" id="loginForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            
            <?php if ($sessionExpired): ?>
            <div class="error-message show" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); color: #92400E; border-color: #FCD34D;">
                <span class="material-icons-outlined">schedule</span>
                <span>Phiên làm việc đã hết hạn (1 giờ). Vui lòng đăng nhập lại.</span>
            </div>
            <?php endif; ?>
            
            <?php if ($kickedByOther): ?>
            <div class="error-message show" style="background: linear-gradient(135deg, #EDE9FE 0%, #DDD6FE 100%); color: #5B21B6; border-color: #A78BFA;">
                <span class="material-icons-outlined">devices</span>
                <span>Tài khoản đã đăng nhập từ thiết bị khác. Vui lòng đăng nhập lại!</span>
            </div>
            <?php endif; ?>
            
            <div class="error-message <?php echo isset($error) ? 'show' : ''; ?>" id="errorMessage">
                <span class="material-icons-outlined">error_outline</span>
                <span id="errorText"><?php echo htmlspecialchars($error ?? 'Tên đăng nhập hoặc mật khẩu không đúng!'); ?></span>
            </div>

            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required autocomplete="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    <span class="material-icons-outlined">person_outline</span>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                    <span class="material-icons-outlined">lock_outline</span>
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Hiện/ẩn mật khẩu">
                        <span class="material-icons-outlined" id="passwordIcon">visibility_off</span>
                    </button>
                </div>
            </div>

            <div class="remember-group">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Ghi nhớ đăng nhập</label>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <span class="btn-text">Đăng Nhập</span>
                <span class="spinner"></span>
            </button>
        </form>

        <div class="login-footer">
            <a href="../fonend/index.php">
                <span class="material-icons-outlined">arrow_back</span>
                Quay lại trang chủ
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                passwordIcon.textContent = 'visibility_off';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
        });

        document.getElementById('username').addEventListener('input', hideError);
        document.getElementById('password').addEventListener('input', hideError);

        function hideError() {
            document.getElementById('errorMessage').classList.remove('show');
        }
    </script>
</body>

</html>
