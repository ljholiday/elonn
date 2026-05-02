<?php
/** @var string $title */
/** @var string $template */
/** @var array<string, mixed> $data */
/** @var array{id: string, email: string, display_name: string|null}|null $identity */
/** @var string $worldUrl */
$currentTemplate = $template;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Elonn is the new front page for a spatial internet: a world layer for places, tools, and persistent digital presence.">
    <link rel="stylesheet" href="/assets/elonn.css">
</head>
<body>
    <header class="site-header" aria-label="Primary">
        <a class="brand" href="/">Elonn</a>
        <nav class="nav" aria-label="Primary navigation">
            <?php if ($identity === null): ?>
                <a href="/account/login" data-auth-open="login">Log in</a>
                <a href="/account/register" data-auth-open="register">Create account</a>
            <?php else: ?>
                <a href="/account">Account</a>
            <?php endif; ?>
            <a class="nav__cta" href="<?= htmlspecialchars($worldUrl, ENT_QUOTES, 'UTF-8') ?>">Enter World</a>
        </nav>
    </header>

    <?php require __DIR__ . '/' . $template; ?>

    <script src="/assets/elonn.js" defer></script>
</body>
</html>
