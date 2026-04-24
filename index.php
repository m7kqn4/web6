<?php
header('Content-Type: text/html; charset=UTF-8');

$db = new PDO('mysql:host=localhost;dbname=u82641;charset=utf8', 'u82641', '7937378');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    $errors = array();
    $values = array();
    $showCredentials = false;
    $generatedLogin = '';
    $generatedPassword = '';

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = 'Спасибо, результаты сохранены.';
        
        if (!empty($_COOKIE['login_display']) && !empty($_COOKIE['pass_display'])) {
            $generatedLogin = $_COOKIE['login_display'];
            $generatedPassword = $_COOKIE['pass_display'];
            $showCredentials = true;
            setcookie('login_display', '', 100000);
            setcookie('pass_display', '', 100000);
        }
    }

    $error_fields = ['fio', 'phone', 'email', 'birthday', 'gender', 'prog_lang', 'agreement'];
    foreach ($error_fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        if ($errors[$field]) {
            setcookie($field . '_error', '', 100000);
        }
    }

    $value_fields = ['fio', 'phone', 'email', 'birthday', 'gender', 'bio', 'agreement', 'prog_lang'];
    foreach ($value_fields as $field) {
        $values[$field] = empty($_COOKIE[$field . '_value']) ? '' : $_COOKIE[$field . '_value'];
    }

    if (!empty($_COOKIE[session_name()])) {
        session_start();
        if (!empty($_SESSION['login']) && !empty($_SESSION['user_id'])) {
            $stmt = $db->prepare("SELECT fd.* FROM form_data fd 
                                   JOIN users u ON u.form_data_id = fd.id 
                                   WHERE u.id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData) {
                $values['fio'] = $userData['fio'];
                $values['phone'] = $userData['phone'];
                $values['email'] = $userData['email'];
                $values['birthday'] = $userData['birthday'];
                $values['gender'] = $userData['gender'];
                $values['prog_lang'] = $userData['prog_lang'];
                $values['bio'] = $userData['bio'];
                $values['agreement'] = $userData['agreement'];
                $messages[] = 'Вы авторизованы ' . htmlspecialchars($_SESSION['login']) . '. Можете редактировать данные.';
            }
        }
    }

    include('form.php');
} 
else {
    $errors = false;
    
    $error_fields = ['fio', 'phone', 'email', 'birthday', 'gender', 'prog_lang', 'agreement'];
    
    $fio = trim($_POST['fio'] ?? '');
    if (empty($fio)) {
        setcookie('fio_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        setcookie('fio_value', $fio, time() + 30*24*60*60);
    }
    
    $phone = trim($_POST['phone'] ?? '');
    $phone_clean = preg_replace('/\D/', '', $phone);
    if (empty($phone_clean) || strlen($phone_clean) < 10 || strlen($phone_clean) > 11) {
        setcookie('phone_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        setcookie('phone_value', $phone, time() + 30*24*60*60);
    }
    
    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        setcookie('email_value', $email, time() + 30*24*60*60);
    }
    
    $birthday = $_POST['birthday'] ?? '';
    if (empty($birthday)) {
        setcookie('birthday_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        $age = date_diff(date_create($birthday), date_create('today'))->y;
        if ($age < 18) {
            setcookie('birthday_error', '1', time() + 24*60*60);
            $errors = true;
        } else {
            setcookie('birthday_value', $birthday, time() + 30*24*60*60);
        }
    }
    
    $gender = $_POST['gender'] ?? '';
    if (!in_array($gender, ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        setcookie('gender_value', $gender, time() + 30*24*60*60);
    }
    
    $prog_lang = $_POST['prog_lang'] ?? [];
    if (empty($prog_lang)) {
        setcookie('prog_lang_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        $prog_lang_str = implode(',', $prog_lang);
        setcookie('prog_lang_value', $prog_lang_str, time() + 30*24*60*60);
    }
    
    $agreement = $_POST['agreement'] ?? '';
    if ($agreement != '1') {
        setcookie('agreement_error', '1', time() + 24*60*60);
        $errors = true;
    } else {
        setcookie('agreement_value', '1', time() + 30*24*60*60);
    }
    
    $bio = trim($_POST['bio'] ?? '');
    setcookie('bio_value', $bio, time() + 30*24*60*60);
    
    if ($errors) {
        header('Location: index.php');
        exit();
    }
    
    foreach ($error_fields as $field) {
        setcookie($field . '_error', '', 100000);
    }
    
    $isAuthorized = false;
    $userId = null;
    
    session_start();
    if (!empty($_SESSION['login']) && !empty($_SESSION['user_id'])) {
        $isAuthorized = true;
        $userId = $_SESSION['user_id'];
    }
    
    if ($isAuthorized) {
        $stmt = $db->prepare("SELECT form_data_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['form_data_id']) {
            $stmt = $db->prepare("UPDATE form_data SET 
                fio = ?, email = ?, phone = ?, birthday = ?, gender = ?, 
                prog_lang = ?, bio = ?, agreement = ? WHERE id = ?");
            $stmt->execute([$fio, $email, $phone, $birthday, $gender, $prog_lang_str, $bio, $agreement, $user['form_data_id']]);
        }
    } else {
        $stmt = $db->prepare("INSERT INTO form_data (fio, email, phone, birthday, gender, prog_lang, bio, agreement) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $email, $phone, $birthday, $gender, $prog_lang_str, $bio, $agreement]);
        $formDataId = $db->lastInsertId();
        
        $login = 'user_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $rawPassword = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (login, password_hash, form_data_id) VALUES (?, ?, ?)");
        $stmt->execute([$login, $passwordHash, $formDataId]);
        
        setcookie('login_display', $login, time() + 24*60*60, '/');
        setcookie('pass_display', $rawPassword, time() + 24*60*60, '/');
    }
    
    session_write_close();
    
    setcookie('save', '1', time() + 24*60*60, '/');
    
    header('Location: index.php');
    exit();
}
?>
