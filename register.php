<?php
require 'config.php';

if (!empty($_SESSION['user_id'])) redirect('catalog.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (mb_strlen($name) < 2) {
        $error = 'Имя — минимум 2 символа.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email.';
    } elseif (mb_strlen($pass) < 6) {
        $error = 'Пароль — минимум 6 символов.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Этот email уже зарегистрирован.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);

            $_SESSION['user_id']   = (int)$pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['is_admin'] = (int)$u['is_admin'];
            redirect('catalog.php');
        }
    }
}

$pageTitle       = 'Регистрация — МедФарм';
$pageDescription = 'Регистрация в онлайн-аптеке МедФарм: бронирование лекарств, отзывы, история заказов.';
$pageKeywords    = 'регистрация, МедФарм, онлайн-аптека';
$activePage      = '';
require 'partials/header.php';
?>

<h1>Регистрация</h1>

<?php if ($error): ?>
    <p style="color:#c00"><?= esc($error) ?></p>
<?php endif; ?>

<form method="post" class="review-form" style="max-width:400px">
    <p>
        <label>Имя<br>
            <input type="text" name="name" required minlength="2"
                   value="<?= esc($_POST['name'] ?? '') ?>"
                   style="width:100%;padding:8px">
        </label>
    </p>
    <p>
        <label>Email<br>
            <input type="email" name="email" required
                   value="<?= esc($_POST['email'] ?? '') ?>"
                   style="width:100%;padding:8px">
        </label>
    </p>
    <p>
        <label>Пароль (минимум 6 символов)<br>
            <input type="password" name="password" required minlength="6"
                   style="width:100%;padding:8px">
        </label>
    </p>
    <button class="btn" type="submit">Зарегистрироваться</button>
</form>

<p style="margin-top:15px">Уже есть аккаунт? <a href="login.php">Войти</a></p>

<?php require 'partials/footer.php'; ?>