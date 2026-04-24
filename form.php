<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 5</title>

    <style>

    .error{
        border: 2px solid rgb(122, 9, 9);
    }

    .success {
    border: 2px solid rgb(103, 231, 103);
    background: #e8f5e9;
    padding: 10px;
    margin: 10px 0;
    }
    .credentials {
    background: #fff3e0;
    padding: 10px;
    margin: 10px 0;
    text-align: center;
    }

    body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background: #ffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    color: #1e293b;
    }

    .form-card {
    background: #ffffff;
    padding: 40px;
    border-radius: 28px;
    width: 100%;
    max-width: 400px;
    position: relative;
    box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.15);
    }

    .form-card::before {
    content: '';
    position: absolute;
    inset: -8px;
    background: radial-gradient(circle at 30% 20%, rgba(60, 130, 222, 0.758));
    border-radius: 32px;
    z-index: -2;
    filter: blur(20px);
    }

    .form-card::after {
    content: '';
    position: absolute;
    inset: -4px;
    background: radial-gradient(circle at 70% 80%, rgba(59, 130, 246, 0.3), rgba(6, 182, 212, 0.15), transparent 65%);
    border-radius: 30px;
    z-index: -1;
    filter: blur(25px);
    }

    h2 {
    margin: 0 0 30px 0;
    font-size: 28px;
    font-weight: 700;
    background: linear-gradient(135deg, rgba(60, 130, 222, 0.758), #1372cbee);
    -webkit-background-clip: text;
    background-clip: text;
    color: #1b4ea0;
    text-align: center;
    letter-spacing: -0.5px;
    }   

    .field { margin-bottom: 24px; }

    label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 14px;
    color: #1b4ea0;
    }

    input, input[type="tel"], input[type="email"], input[type="date"], select, textarea {
            width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0;
            border-radius: 14px; background: #ffffff; font-size: 15px;
            transition: all 0.3s ease; color: #1e293b; box-sizing: border-box;
        }

        .error {
            border-color: #882020 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .error-text {
            color: #a11d1d; font-size: 11px; margin-top: 4px; font-weight: 500;
        }

        .msg-banner {
            padding: 12px; border-radius: 12px; margin-bottom: 20px;
            text-align: center; font-size: 13px; font-weight: 600;
        }
        .msg-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .msg-info { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        input:focus, select:focus, textarea:focus {
            outline: none; border-color: #1b4ea0;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .options-group { display: flex; gap: 20px; margin-top: 5px; }
        .options-group label { font-weight: 500; font-size: 14px; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 6px; }

        button {
            background: linear-gradient(135deg, #2059b4, #066dd4);
            color: white; border: none; padding: 14px;
            width: 100%; font-size: 16px; font-weight: 600;
            border-radius: 40px; cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.25); margin-top: 10px;
        }
        button:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35); }
        
        .auth-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .auth-link a {
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
        <div class="form-card">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="msg-banner <?php echo (strpos($msg, 'Спасибо') !== false) ? 'msg-success' : 'msg-info'; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($showCredentials) && !empty($generatedLogin) && !empty($generatedPassword)): ?>
        <div style="background: #fff3e0; border: 2px solid #ff9800; border-radius: 16px; padding: 15px; margin-bottom: 20px; text-align: center;">
            <strong style="color: #e65100;">Данные были сохранены. Ваши учётные данные (показываются один раз):</strong><br><br>
            <strong>Логин:</strong> <code style="background: white; padding: 4px 8px; border-radius: 6px;"><?php echo htmlspecialchars($generatedLogin); ?></code><br>
            <strong>Пароль:</strong> <code style="background: white; padding: 4px 8px; border-radius: 6px;"><?php echo htmlspecialchars($generatedPassword); ?></code><br><br>
            <small style="color: #bf360c;">Сохраните или запомните эти данные, они понадобятся для входа.</small>
        </div>
        <?php endif; ?>

        <h2>Регистрация</h2>
        
        <form action="index.php" method="POST">
            <div class="field">
                <label>ФИО</label>
                <input type="text" name="fio" 
                    class="<?php echo $errors['fio'] ? 'error' : ''; ?>" 
                    value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>" placeholder="Иван Иванов Иванович">
            </div>

            <div class="field">
                <label>Телефон</label>
                <input type="tel" name="phone" 
                    class="<?php echo $errors['phone'] ? 'error' : ''; ?>" 
                    value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>" placeholder="+7 800 000 00 00">
            </div>

            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email" 
                    class="<?php echo $errors['email'] ? 'error' : ''; ?>" 
                    value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>" placeholder="mail@example.com">
            </div>

            <div class="field">
                <label>Дата рождения</label>
                <input type="date" name="birthday" 
                    class="<?php echo $errors['birthday'] ? 'error' : ''; ?>" 
                    value="<?php echo htmlspecialchars($values['birthday'] ?? ''); ?>">
            </div>

            <div class="field">
                <label>Пол</label>
                <div class="options-group">
                    <label><input type="radio" name="gender" value="male" <?php echo (($values['gender'] ?? '') == 'male') ? 'checked' : ''; ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo (($values['gender'] ?? '') == 'female') ? 'checked' : ''; ?>> Женский</label>
                </div>
            </div>

            <div class="field">
                <label>Любимый язык</label>
                <select name="prog_lang[]" multiple class="<?php echo $errors['prog_lang'] ? 'error' : ''; ?>">
                    <?php
                    $langs = [1=>'Pascal', 2=>'C', 3=>'C++', 4=>'JavaScript', 5=>'PHP', 6=>'Python', 7=>'Java', 8=>'Haskel', 9=>'Clojure', 10=>'Prolog', 11=>'Scala', 12=>'Go'];
                    $selected_langs = !empty($values['prog_lang']) ? explode(',', $values['prog_lang']) : [];
                    foreach ($langs as $id => $name) {
                        $sel = in_array($id, $selected_langs) ? 'selected' : '';
                        echo "<option value='$id' $sel>$name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="field">
                <label>Биография</label>
                <textarea name="bio"><?php echo htmlspecialchars($values['bio'] ?? ''); ?></textarea>
            </div>

            <div class="field">
                <label style="display:flex; align-items:center; gap:8px; border-radius:8px; padding:4px;">
                    <input type="checkbox" name="agreement" value="1" <?php echo (($values['agreement'] ?? '') == '1') ? 'checked' : ''; ?>>
                    <span>С согласием ознакомлен(а)</span>
                </label>
            </div>

            <button type="submit">Сохранить</button>
        </form>

        <div class="auth-link">
            <?php if (!empty($_SESSION['login'])): ?>
                <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: 600;">Выйти (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
            <?php else: ?>
                <a href="login.php" style="color: #3b82f6; text-decoration: none; font-weight: 600;">Войти для изменения</a>
            <?php endif; ?>
            <br><br>
            <a href="admin.php" style="color: #dc3545; text-decoration: none; font-weight: 600;">Администратор</a>
        </div>

    </div>

</body>
</html>
