<?php
/** @var array<string, mixed> $data */
$error = $data['error'] ?? null;
$old = is_array($data['old'] ?? null) ? $data['old'] : [];
$isPartial = isset($currentTemplate) && $currentTemplate === 'home.php';
?>
<?php if ($isPartial): ?><div class="auth-modal" data-auth-modal="register" hidden><div class="auth-modal__backdrop" data-auth-close></div><?php endif; ?>
<main class="<?= $isPartial ? 'auth-modal__panel' : 'account-page' ?>">
    <section class="account-panel" aria-labelledby="register-title">
        <?php if ($isPartial): ?><button class="auth-modal__close" type="button" data-auth-close>Close</button><?php endif; ?>
        <p class="eyebrow">Elonn Account</p>
        <h1 id="register-title">Create account</h1>
        <p class="account-copy">Create one Elonn identity for the services coming online.</p>
        <?php if (is_string($error)): ?><p class="account-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <form class="account-form" method="post" action="/account/register">
            <label>Display name <input name="display_name" autocomplete="name" value="<?= htmlspecialchars((string) ($old['display_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Email <input name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Password <input name="password" type="password" autocomplete="new-password" required></label>
            <button class="button button--primary" type="submit">Create account</button>
        </form>
        <p class="account-copy">Already have an account? <a href="/account/login" data-auth-open="login">Log in</a>.</p>
    </section>
</main>
<?php if ($isPartial): ?></div><?php endif; ?>
