<?php

header('Content-Type: text/html; charset=UTF-8');

$db = new PDO('mysql:host=localhost;dbname=u82641;charset=utf8', 'u82641', '7937378');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #f5f5f5;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
            }
            .login-card {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 350px;
            }
            h2 {
                margin: 0 0 20px 0;
                text-align: center;
                color: #1b4ea0;
            }
            input {
                width: 100%;
                padding: 12px;
                margin: 10px 0;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-sizing: border-box;
            }
            button {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #2059b4, #066dd4);
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 16px;
                margin-top: 10px;
            }
            .error {
                color: red;
                text-align: center;
                margin-bottom: 15px;
            }
            .link {
                text-align: center;
                margin-top: 15px;
            }
            .link a {
                color: #3b82f6;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>Вход в систему</h2>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <input type="text" name="login" placeholder="Логин" required>
                <input type="password" name="pass" placeholder="Пароль" required>
                <button type="submit">Войти</button>
            </form>
            <div class="link">
                <a href="index.php">На главную</a>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['pass'] ?? '';
    
    if (empty($login) || empty($pass)) {
        $error = 'Заполните логин и пароль';
        include(__FILE__);
        exit();
    }
    
    $stmt = $db->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($pass, $user['password_hash'])) {
        session_start();
        $_SESSION['login'] = $user['login'];
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit();
    } else {
        $error = 'Неверный логин или пароль';
        include(__FILE__);
        exit();
    }
}
?>
