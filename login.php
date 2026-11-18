<?php
require 'config.php';

if (!empty($_SESSION['user_id'])) redirect('catalog.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password_hash'])) {
        $_SESSION['user_id']   = (int)$u['id'];
        $_SESSION['user_name'] = $u['name'];
        $_SESSION['is_admin'] = (int)$u['is_admin'];
        $back = $_GET['back'] ?? 'catalog.php';
        if (!preg_match('#^[a-z0-9_\-\./]+\.php(\?[^\s]*)?$#i', $back)) {
            $back = 'catalog.php';
        }
        redirect($back);
    } else {
        $error = 'Неверный email или пароль.';
    }
}

$pageTitle       = 'Вход — МедФарм';
$pageDescription = 'Вход в личный кабинет МедФарм.';
$pageKeywords    = 'вход, логин, МедФарм';
$activePage      = '';
require 'partials/header.php';
?>

<h1>Вход</h1>

<?php if ($error): ?>
    <p style="color:#c00"><?= esc($error) ?></p>
<?php endif; ?>

<form method="post" class="review-form" style="max-width:400px">
    <p>
        <label>Email<br>
            <input type="email" name="email" required
                   value="<?= esc($_POST['email'] ?? '') ?>"
                   style="width:100%;padding:8px">
        </label>
    </p>
    <p>
        <label>Пароль<br>
            <input type="password" name="password" required
                   style="width:100%;padding:8px">
        </label>
    </p>
    <button class="btn" type="submit">Войти</button>
</form>

<p style="margin-top:15px">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>

<?php require 'partials/footer.php'; ?>