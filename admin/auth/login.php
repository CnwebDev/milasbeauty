<?php
require_once '../../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$_POST['email']]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($_POST['password'], $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];

        header("Location: ../index.php");
        exit;
    }

    $error = 'Onjuiste inloggegevens';
}
?>
<?php
$pageTitle = 'Login';
$showAdminNav = false;
$bodyClass = 'admin-body min-h-screen';
$mainClass = 'mx-auto max-w-7xl px-6 py-10 flex items-center justify-center';

include __DIR__ . '/../includes/header.php';
?>

<div class="w-full max-w-md rounded-[32px] p-8 luxe-ring bg-black/35 shadow-luxe">

    <!-- Logo / title -->
    <div class="text-center">
        <div class="font-display tracking-[.34em] text-sm gold-text">ASA</div>
        <div class="text-xs tracking-[.32em] text-white/60 -mt-1">ADMIN PANEL</div>
        <h1 class="mt-6 font-display text-3xl">Inloggen</h1>
        <p class="mt-2 text-sm text-white/60">Beveiligde beheeromgeving</p>
    </div>

    <?php if ($error): ?>
        <div class="mt-6 rounded-2xl p-4 luxe-ring bg-red-500/10 text-sm text-red-200">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post" class="mt-8 space-y-4">

        <label class="block text-sm text-white/80">
            E-mail
            <input
                    type="email"
                    name="email"
                    required
                    class="mt-2 input-field"
                    placeholder="admin@asaparfums.nl"
            />
        </label>

        <label class="block text-sm text-white/80">
            Wachtwoord
            <input
                    type="password"
                    name="password"
                    required
                    class="mt-2 input-field"
                    placeholder="••••••••"
            />
        </label>

        <button
                type="submit"
                class="btn btn-primary btn-md btn-block shadow-glow mt-4"
        >
            Inloggen
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-white/40">
        © <?= date('Y') ?> ASA Parfums
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
