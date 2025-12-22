<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #fff;
            text-align: center;
            padding: 20px;
        }
        .container { max-width: 400px; }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: linear-gradient(135deg, #EF4444, #F59E0B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
        }
        h1 { font-size: 24px; margin-bottom: 16px; font-weight: 700; }
        p { color: rgba(255,255,255,0.7); margin-bottom: 32px; line-height: 1.6; }
        a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #10B981, #34D399);
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }
        a:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">403</div>
        <h1>Không có quyền truy cập</h1>
        <p>Bạn không có quyền truy cập vào trang này. Vui lòng liên hệ quản trị viên nếu bạn cho rằng đây là lỗi.</p>
        <a href="dashboard.php">← Quay lại Dashboard</a>
    </div>
</body>
</html>
