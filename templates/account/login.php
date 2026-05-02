<?php
/** @var array<string, mixed> $data */
$error = $data['error'] ?? null;
$old = is_array($data['old'] ?? null) ? $data['old'] : [];
$isPartial = isset($currentTemplate) && $currentTemplate === 'home.php';
?>
<?php if ($isPartial): ?><div class="auth-modal" data-auth-modal="login" hidden><div class="auth-modal__backdrop" data-auth-close></div><?php endif; ?>
<main class="<?= $isPartial ? 'auth-modal__panel' : 'account-page' ?>">
    <section class="account-panel" aria-labelledby="login-title">
        <?php if ($isPartial): ?><button class="auth-modal__close" type="button" data-auth-close>Close</button><?php endif; ?>
        <p class="eyebrow">Elonn Account</p>
        <h1 id="login-title">Log in</h1>
        <p class="account-copy">Use your Elonn identity to enter connected services.</p>
        <?php if (is_string($error)): ?><p class="account-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <form class="account-form" method="post" action="/account/login">
            <label>Email <input name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
            <button class="button button--primary" type="submit">Log in</button>
        </form>
        <p class="account-copy">Need an account? <a href="/account/register" data-auth-open="register">Create one</a>.</p>
    </section>
</main>
<?php if ($isPartial): ?></div><?php endif; ?>
