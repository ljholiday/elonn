<?php
/** @var array<string, mixed> $data */
$identity = $data['identity'] ?? null;
$worldUrl = (string) ($data['worldUrl'] ?? '#');
$error = $data['error'] ?? null;
?>
<main>
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero__media" aria-hidden="true"></div>
        <div class="hero__content">
            <p class="eyebrow">The world layer is coming online</p>
            <h1 id="hero-title">Elonn</h1>
            <p class="hero__summary">
                A new front page for the spatial internet: places, people, tools, and live digital surfaces arranged around you instead of buried in tabs.
            </p>
            <div class="actions" aria-label="Primary actions">
                <a class="button button--primary" href="<?= htmlspecialchars($worldUrl, ENT_QUOTES, 'UTF-8') ?>">Enter World</a>
                <?php if ($identity === null): ?>
                    <a class="button" href="/account/register" data-auth-open="register">Create account</a>
                    <a class="button" href="/account/login" data-auth-open="login">Log in</a>
                <?php else: ?>
                    <a class="button" href="/account">Account</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="story" id="world" aria-labelledby="world-title">
        <p class="eyebrow">A browser becomes a place</p>
        <h2 id="world-title">Step through a page and into a world.</h2>
        <p>
            Elonn turns the web into a spatial starting point. The screen becomes a room, the room becomes a map, and every surface can become a tool you carry with you.
        </p>
    </section>

    <section class="overview" aria-label="World principles">
        <article>
            <span>Room Layer</span>
            <h2>Objects stay in the world.</h2>
            <p>Markers, places, and future map objects belong to the surrounding environment so movement has direction and context.</p>
        </article>
        <article>
            <span>Carry Layer</span>
            <h2>Tools stay with you.</h2>
            <p>Windows, controls, and personal surfaces remain close at hand while the world moves behind them.</p>
        </article>
        <article>
            <span>Persistent Presence</span>
            <h2>The front page remembers.</h2>
            <p>Your world can become the place you return to for work, local discovery, communication, and augmented reality.</p>
        </article>
    </section>

    <section class="app-preview" aria-labelledby="app-title">
        <div>
            <p class="eyebrow">Android AR preview</p>
            <h2 id="app-title">Carry the world off the page.</h2>
        </div>
        <div>
            <p>
                The Android preview is an early AR build of the same idea: world markers stay in the room, while your carry layer stays close to the screen.
            </p>
            <a class="button" href="/assets/downloads/elonn-world-ar-debug.apk" download>Download Android APK</a>
        </div>
    </section>

    <section class="access" id="access" aria-labelledby="access-title">
        <div>
            <p class="eyebrow">Live demo</p>
            <h2 id="access-title">The first doorway is open.</h2>
        </div>
        <div>
            <p>
                The web demo is live now as the first public shape of Elonn World. Create an account to prepare for connected calendar, world, and native AR surfaces as the platform hardens.
            </p>
            <a class="button button--primary" href="<?= htmlspecialchars($worldUrl, ENT_QUOTES, 'UTF-8') ?>">Enter World</a>
        </div>
    </section>
</main>

<?php if ($identity === null): ?>
    <?php require __DIR__ . '/account/login.php'; ?>
    <?php require __DIR__ . '/account/register.php'; ?>
<?php endif; ?>
