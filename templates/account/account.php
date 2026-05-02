<?php
/** @var array<string, mixed> $data */
$identity = $data['identity'] ?? null;
?>
<main class="account-page">
    <section class="account-panel" aria-labelledby="account-title">
        <p class="eyebrow">Elonn Account</p>
        <h1 id="account-title">Account</h1>
        <div class="account-details">
            <div><span>ID</span><strong><?= htmlspecialchars((string) ($identity['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Email</span><strong><?= htmlspecialchars((string) ($identity['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
            <div><span>Display name</span><strong><?= htmlspecialchars((string) ($identity['display_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong></div>
        </div>
        <form class="account-actions" method="post" action="/account/logout">
            <button class="button" type="submit">Log out</button>
        </form>
    </section>
</main>
