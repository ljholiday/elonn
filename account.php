<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/account', PHP_URL_PATH) ?: '/account';
$apiBaseUrl = apiBaseUrl();
$siteBaseUrl = siteBaseUrl();
$token = cookieToken();
$identity = $token === null ? null : identityForToken($apiBaseUrl, $token);

if ($path === '/account' && $identity === null) {
    redirect('/account/login');
}

$error = errorMessage($_GET['error'] ?? null);

if ($path === '/account/register') {
    renderPage('Create account', 'register', $apiBaseUrl, $siteBaseUrl, $identity, $error);
    exit;
}

if ($path === '/account/login') {
    renderPage('Log in', 'login', $apiBaseUrl, $siteBaseUrl, $identity, $error);
    exit;
}

renderPage('Account', 'account', $apiBaseUrl, $siteBaseUrl, $identity, $error);

function apiBaseUrl(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return str_contains((string) $host, 'elonn.local')
        ? 'http://api.elonn.local'
        : 'https://api.elonn.com';
}

function siteBaseUrl(): string
{
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'elonn.local';
    return $scheme . '://' . $host;
}

function cookieToken(): ?string
{
    $token = $_COOKIE['elonn_api_token'] ?? null;
    if (!is_string($token)) {
        return null;
    }

    $token = trim($token);
    return $token === '' ? null : $token;
}

/**
 * @return array{id: string, email: string, display_name: string|null}|null
 */
function identityForToken(string $apiBaseUrl, string $token): ?array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nAuthorization: Bearer {$token}",
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);

    $raw = @file_get_contents($apiBaseUrl . '/identity/me', false, $context);
    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($decoded) || !isset($decoded['id'], $decoded['email'])) {
        return null;
    }

    return [
        'id' => (string) $decoded['id'],
        'email' => (string) $decoded['email'],
        'display_name' => is_string($decoded['display_name'] ?? null) ? $decoded['display_name'] : null,
    ];
}

function errorMessage(mixed $error): ?string
{
    return match ($error) {
        'missing_fields' => 'Email and password are required.',
        'invalid_login' => 'Invalid email or password.',
        'user_exists' => 'An account already exists for that email.',
        'register_failed' => 'Unable to create account.',
        'login_failed' => 'Unable to log in.',
        default => null,
    };
}

function redirect(string $path): void
{
    http_response_code(303);
    header('Location: ' . $path);
    exit;
}

/**
 * @param array{id: string, email: string, display_name: string|null}|null $identity
 */
function renderPage(
    string $title,
    string $view,
    string $apiBaseUrl,
    string $siteBaseUrl,
    ?array $identity,
    ?string $error
): void {
    $returnTo = $siteBaseUrl . '/account';
    $loginErrorTo = $siteBaseUrl . '/account/login';
    $registerErrorTo = $siteBaseUrl . '/account/register';
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> - Elonn</title>
    <meta name="description" content="Elonn account access.">
    <link rel="stylesheet" href="/assets/site.css">
</head>
<body>
    <header class="site-header" aria-label="Primary">
        <a class="brand" href="/">Elonn</a>
        <nav class="nav" aria-label="Primary navigation">
            <a href="/account/login">Log in</a>
            <a href="/account/register">Register</a>
            <a class="nav__cta" data-env-link="world" href="https://world.elonn.com/world">Enter World</a>
        </nav>
    </header>

    <main class="account-page">
        <section class="account-panel" aria-labelledby="account-title">
            <p class="eyebrow">Elonn Account</p>
            <?php if ($view === 'register'): ?>
                <h1 id="account-title">Create account</h1>
                <p class="account-copy">Create one Elonn identity for the services coming online.</p>
                <?php if ($error !== null): ?><p class="account-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <form class="account-form" method="post" action="<?= htmlspecialchars($apiBaseUrl . '/identity/register', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="error_to" value="<?= htmlspecialchars($registerErrorTo, ENT_QUOTES, 'UTF-8') ?>">
                    <label>Display name <input name="display_name" autocomplete="name"></label>
                    <label>Email <input name="email" type="email" autocomplete="email" required></label>
                    <label>Password <input name="password" type="password" autocomplete="new-password" required></label>
                    <button class="button button--primary" type="submit">Create account</button>
                </form>
                <p class="account-copy">Already have an account? <a href="/account/login">Log in</a>.</p>
            <?php elseif ($view === 'login'): ?>
                <h1 id="account-title">Log in</h1>
                <p class="account-copy">Use your Elonn identity to enter connected services.</p>
                <?php if ($error !== null): ?><p class="account-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <form class="account-form" method="post" action="<?= htmlspecialchars($apiBaseUrl . '/identity/login', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="error_to" value="<?= htmlspecialchars($loginErrorTo, ENT_QUOTES, 'UTF-8') ?>">
                    <label>Email <input name="email" type="email" autocomplete="email" required></label>
                    <label>Password <input name="password" type="password" autocomplete="current-password" required></label>
                    <button class="button button--primary" type="submit">Log in</button>
                </form>
                <p class="account-copy">Need an account? <a href="/account/register">Create one</a>.</p>
            <?php else: ?>
                <h1 id="account-title">Account</h1>
                <div class="account-details">
                    <div><span>ID</span><strong><?= htmlspecialchars($identity['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div><span>Email</span><strong><?= htmlspecialchars($identity['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
                    <div><span>Display name</span><strong><?= htmlspecialchars($identity['display_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></div>
                </div>
                <form class="account-actions" method="post" action="<?= htmlspecialchars($apiBaseUrl . '/identity/logout', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($siteBaseUrl . '/account/login', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="error_to" value="<?= htmlspecialchars($siteBaseUrl . '/account', ENT_QUOTES, 'UTF-8') ?>">
                    <button class="button" type="submit">Log out</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
    <?php
}
