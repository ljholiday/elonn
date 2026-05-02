<?php

declare(strict_types=1);

use Elonn\Site\ApiClient;
use Elonn\Site\Response;
use Elonn\Site\Router;
use Elonn\Site\View;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $prefix = 'Elonn\\Site\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = BASE_PATH . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$env = loadEnv(BASE_PATH . '/config/.env');
$apiBaseUrl = envValue($env, 'ELONN_API_BASE_URL') ?? defaultApiBaseUrl();
$cookieDomain = envValue($env, 'ELONN_COOKIE_DOMAIN') ?? defaultCookieDomain();
$worldUrl = envValue($env, 'ELONN_WORLD_URL') ?? defaultWorldUrl();
$api = new ApiClient($apiBaseUrl);
$router = new Router();

$router->get('/', static function () use ($api, $worldUrl): void {
    renderPage('Elonn', 'home.php', [
        'identity' => currentIdentity($api),
        'worldUrl' => $worldUrl,
        'error' => errorMessage($_GET['error'] ?? null),
    ]);
});

$router->get('/account/login', static function () use ($api, $worldUrl): void {
    renderPage('Log in', 'account/login.php', [
        'identity' => currentIdentity($api),
        'worldUrl' => $worldUrl,
        'error' => errorMessage($_GET['error'] ?? null),
        'old' => [],
    ]);
});

$router->post('/account/login', static function () use ($api, $cookieDomain): void {
    $email = normalizeEmail($_POST['email'] ?? null);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($email === null || $password === '') {
        renderPage('Log in', 'account/login.php', [
            'identity' => null,
            'worldUrl' => defaultWorldUrl(),
            'error' => 'Email and password are required.',
            'old' => formOld(['email']),
        ], 400);
        return;
    }

    $result = $api->login($email, $password);
    if (!$result['ok'] || !isset($result['token'], $result['expires_at'])) {
        renderPage('Log in', 'account/login.php', [
            'identity' => null,
            'worldUrl' => defaultWorldUrl(),
            'error' => 'Invalid email or password.',
            'old' => formOld(['email']),
        ], 401);
        return;
    }

    setAuthCookie($result['token'], $result['expires_at'], $cookieDomain);
    Response::redirect('/account');
});

$router->get('/account/register', static function () use ($api, $worldUrl): void {
    renderPage('Create account', 'account/register.php', [
        'identity' => currentIdentity($api),
        'worldUrl' => $worldUrl,
        'error' => errorMessage($_GET['error'] ?? null),
        'old' => [],
    ]);
});

$router->post('/account/register', static function () use ($api, $cookieDomain): void {
    $email = normalizeEmail($_POST['email'] ?? null);
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $displayName = cleanOptionalString($_POST['display_name'] ?? null);

    if ($email === null || $password === '') {
        renderPage('Create account', 'account/register.php', [
            'identity' => null,
            'worldUrl' => defaultWorldUrl(),
            'error' => 'Email and password are required.',
            'old' => formOld(['display_name', 'email']),
        ], 400);
        return;
    }

    $register = $api->register($email, $password, $displayName);
    if (!$register['ok']) {
        renderPage('Create account', 'account/register.php', [
            'identity' => null,
            'worldUrl' => defaultWorldUrl(),
            'error' => $register['status'] === 409 ? 'An account already exists for that email.' : 'Unable to create account.',
            'old' => formOld(['display_name', 'email']),
        ], $register['status'] === 409 ? 409 : 500);
        return;
    }

    $login = $api->login($email, $password);
    if (!$login['ok'] || !isset($login['token'], $login['expires_at'])) {
        Response::redirect('/account/login');
        return;
    }

    setAuthCookie($login['token'], $login['expires_at'], $cookieDomain);
    Response::redirect('/account');
});

$router->get('/account', static function () use ($api, $worldUrl): void {
    $identity = currentIdentity($api);
    if ($identity === null) {
        Response::redirect('/account/login');
        return;
    }

    renderPage('Account', 'account/account.php', [
        'identity' => $identity,
        'worldUrl' => $worldUrl,
        'error' => null,
    ]);
});

$router->post('/account/logout', static function () use ($api, $cookieDomain): void {
    $token = authToken();
    if ($token !== null) {
        $api->logout($token);
    }

    clearAuthCookie($cookieDomain);
    Response::redirect('/account/login');
});

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'
);

/**
 * @param array<string, mixed> $data
 */
function renderPage(string $title, string $template, array $data, int $status = 200): void
{
    View::render('layout.php', [
        'title' => $title,
        'template' => $template,
        'data' => $data,
        'identity' => $data['identity'] ?? null,
        'worldUrl' => $data['worldUrl'] ?? defaultWorldUrl(),
    ], $status);
}

/**
 * @return array<string, string>
 */
function loadEnv(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $values = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $values[trim($key)] = trim($value);
    }

    return $values;
}

function defaultApiBaseUrl(): string
{
    return str_contains((string) ($_SERVER['HTTP_HOST'] ?? ''), 'elonn.local')
        ? 'http://api.elonn.local'
        : 'https://api.elonn.com';
}

/**
 * @param array<string, string> $env
 */
function envValue(array $env, string $key): ?string
{
    $value = trim($env[$key] ?? '');
    return $value === '' ? null : $value;
}

function defaultCookieDomain(): string
{
    return str_contains((string) ($_SERVER['HTTP_HOST'] ?? ''), 'elonn.local')
        ? '.elonn.local'
        : '.elonn.com';
}

function defaultWorldUrl(): string
{
    return str_contains((string) ($_SERVER['HTTP_HOST'] ?? ''), 'elonn.local')
        ? 'http://world.elonn.local/world'
        : 'https://world.elonn.com/world';
}

function authToken(): ?string
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
function currentIdentity(ApiClient $api): ?array
{
    $token = authToken();
    return $token === null ? null : $api->me($token);
}

function normalizeEmail(mixed $email): ?string
{
    if (!is_string($email)) {
        return null;
    }

    $email = strtolower(trim($email));
    return filter_var($email, FILTER_VALIDATE_EMAIL) === false ? null : $email;
}

function cleanOptionalString(mixed $value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);
    return $value === '' ? null : $value;
}

/**
 * @param array<int, string> $keys
 * @return array<string, string>
 */
function formOld(array $keys): array
{
    $old = [];
    foreach ($keys as $key) {
        $value = $_POST[$key] ?? '';
        $old[$key] = is_string($value) ? $value : '';
    }

    return $old;
}

function setAuthCookie(string $token, string $expiresAt, string $domain): void
{
    setcookie('elonn_api_token', $token, [
        'expires' => strtotime($expiresAt) ?: 0,
        'path' => '/',
        'domain' => $domain,
        'secure' => isHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clearAuthCookie(string $domain): void
{
    setcookie('elonn_api_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $domain,
        'secure' => isHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function isHttps(): bool
{
    return ($_SERVER['HTTPS'] ?? '') === 'on'
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
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
