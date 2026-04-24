<?php

$db = new PDO('mysql:host=localhost;dbname=u82641;charset=utf8', 'u82641', '7937378');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$valid_admin = false;

if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    $stmt = $db->prepare("SELECT password_hash FROM admins WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        $valid_admin = true;
    }
}

if (!$valid_admin) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin area"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

$message = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $db->prepare("UPDATE users SET form_data_id = NULL WHERE form_data_id = ?");
    $stmt->execute([$id]);
    
    $stmt = $db->prepare("DELETE FROM form_data WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Запись удалена';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $fio = trim($_POST['fio']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $prog_lang = isset($_POST['prog_lang']) ? implode(',', $_POST['prog_lang']) : '';
    $bio = trim($_POST['bio']);
    $agreement = isset($_POST['agreement']) ? 1 : 0;
    
    $stmt = $db->prepare("UPDATE form_data SET 
        fio = ?, email = ?, phone = ?, birthday = ?, gender = ?, 
        prog_lang = ?, bio = ?, agreement = ? WHERE id = ?");
    $stmt->execute([$fio, $email, $phone, $birthday, $gender, $prog_lang, $bio, $agreement, $id]);
    $message = 'Запись обновлена';
}

$users = $db->query("SELECT * FROM form_data ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$all_langs = [1=>'Pascal', 2=>'C', 3=>'C++', 4=>'JavaScript', 5=>'PHP', 6=>'Python', 7=>'Java', 8=>'Haskel', 9=>'Clojure', 10=>'Prolog', 11=>'Scala', 12=>'Go'];

$lang_counts = [];
foreach ($all_langs as $id => $name) {
    $lang_counts[$id] = 0;
}

foreach ($users as $user) {
    if (!empty($user['prog_lang'])) {
        $lang_ids = explode(',', $user['prog_lang']);
        foreach ($lang_ids as $id) {
            $id = (int)$id;
            if (isset($lang_counts[$id])) {
                $lang_counts[$id]++;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #2059b4;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #B0C4DE;
            color: white;
        }
        .delete-btn {
            background: #b51d12;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        .edit-btn {
            background: #4682B4;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
        }
        .stats {
            background: #E6E6FA;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .stats ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .stats li {
            background: white;
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #2059b4;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2059b4;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .edit-form {
            background: #f8f9fa;
            padding: 15px;
            margin-top: 10px;
            border-radius: 8px;
        }
        .form-row {
            margin-bottom: 10px;
        }
        .form-row label {
            display: inline-block;
            width: 120px;
            font-weight: 600;
        }
        .form-row input, .form-row select, .form-row textarea {
            padding: 5px;
            width: 300px;
        }
        .save-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .edit-row {
            display: none;
        }
        .edit-row.active {
            display: table-row;
        }
    </style>
    <script>
        function showEditForm(id) {
            document.getElementById('edit-row-' + id).classList.add('active');
        }
        function hideEditForm(id) {
            document.getElementById('edit-row-' + id).classList.remove('active');
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Панель администратора</h1>
    
    <?php if ($message): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="stats">
        <h2>Статистика по языкам программирования</h2>
        <ul>
        <?php foreach ($all_langs as $id => $name): ?>
            <li><strong><?php echo $name; ?>:</strong> <?php echo $lang_counts[$id]; ?> пользователь(ей)</li>
        <?php endforeach; ?>
        </ul>
    </div>
    
    <h2>Все пользователи</h2>
    
    <?php if (empty($users)): ?>
        <p>Нет данных</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Дата рождения</th>
                    <th>Пол</th>
                    <th>Языки</th>
                    <th>Биография</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr id="row-<?php echo $user['id']; ?>">
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['fio']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo $user['birthday']; ?></td>
                    <td><?php echo $user['gender'] == 'male' ? 'Мужской' : 'Женский'; ?></td>
                    <td>
                        <?php
                        $lang_ids = explode(',', $user['prog_lang']);
                        $lang_names = [];
                        foreach ($lang_ids as $id) {
                            if (isset($all_langs[(int)$id])) {
                                $lang_names[] = $all_langs[(int)$id];
                            }
                        }
                        echo implode(', ', $lang_names);
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars(substr($user['bio'], 0, 100)); ?></td>
                    <td>
                        <button class="edit-btn" onclick="showEditForm(<?php echo $user['id']; ?>)">Редактировать</button>
                        <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Удалить запись?')">
                            <button class="delete-btn">Удалить</button>
                        </a>
                    </td>
                </tr>
                <tr id="edit-row-<?php echo $user['id']; ?>" class="edit-row">
                    <td colspan="9">
                        <form method="post">
                            <input type="hidden" name="edit_id" value="<?php echo $user['id']; ?>">
                            <div class="form-row">
                                <label>ФИО:</label>
                                <input type="text" name="fio" value="<?php echo htmlspecialchars($user['fio']); ?>" required>
                            </div>
                            <div class="form-row">
                                <label>Email:</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-row">
                                <label>Телефон:</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="form-row">
                                <label>Дата рождения:</label>
                                <input type="date" name="birthday" value="<?php echo $user['birthday']; ?>" required>
                            </div>
                            <div class="form-row">
                                <label>Пол:</label>
                                <select name="gender">
                                    <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                                    <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                                </select>
                            </div>
                            <div class="form-row">
                                <label>Языки:</label>
                                <select name="prog_lang[]" multiple>
                                    <?php 
                                    $selected_langs = explode(',', $user['prog_lang']);
                                    foreach ($all_langs as $id => $name): 
                                        $sel = in_array((string)$id, $selected_langs) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $id; ?>" <?php echo $sel; ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <label>Биография:</label>
                                <textarea name="bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </div>
                            <div class="form-row">
                                <label>
                                    <input type="checkbox" name="agreement" value="1" <?php echo $user['agreement'] ? 'checked' : ''; ?>>
                                    С согласием ознакомлен
                                </label>
                            </div>
                            <button type="submit" class="save-btn">Сохранить</button>
                            <button type="button" class="cancel-btn" onclick="hideEditForm(<?php echo $user['id']; ?>)">Отмена</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <a href="index.php" class="back-link">На главную</a>
</div>
</body>
</html>
