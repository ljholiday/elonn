# Elonn

`elonn.local` is the public Elonn web site and account entry point. Production is `elonn.com`.

This app owns the customer-facing marketing surface and the browser account experience. It does not own identity data and must not connect directly to `elonn_api`.

## Current Purpose

- Present the Elonn marketing/conversion page.
- Link users into the World experience.
- Provide browser-facing account pages for login, registration, account display, and logout.
- Call `api.elonn.local` or `api.elonn.com` for identity operations.

## Routes

Current browser routes:

```text
GET /
GET /account/login
POST /account/login
GET /account/register
POST /account/register
GET /account
POST /account/logout
```

The landing page also has progressive-enhancement login and registration modals. The full account routes work without JavaScript.

## Structure

```text
config/
  .env
public/
  index.php
  .htaccess
  assets/
src/
  ApiClient.php
  Response.php
  Router.php
  View.php
templates/
  layout.php
  home.php
  account/
```

## Configuration

Create `config/.env` for environment-specific values:

```env
ELONN_API_BASE_URL=
ELONN_COOKIE_DOMAIN=
ELONN_WORLD_URL=
```

Blank values use host-based defaults.

Local defaults:

```text
ELONN_API_BASE_URL=http://api.elonn.local
ELONN_COOKIE_DOMAIN=.elonn.local
ELONN_WORLD_URL=http://world.elonn.local/world
```

Production defaults:

```text
ELONN_API_BASE_URL=https://api.elonn.com
ELONN_COOKIE_DOMAIN=.elonn.com
ELONN_WORLD_URL=https://world.elonn.com/world
```

## Identity Boundary

- `api.elonn.local` owns shared identity.
- `elonn.local` owns the visible account UI.
- Login and registration POSTs call API identity endpoints.
- Successful login and registration set the shared `elonn_api_token` cookie.
- `/account` reads the current identity by calling `GET /identity/me` on the API.
- This app has no database and no identity tables.

Do not add OAuth, MFA, password reset, roles, teams, or third-party app authorization here yet.

## Related Services

- `api.elonn.local`: shared identity authority.
- `time.elonn.local`: calendar/time product service.
- `world.elonn.local`: World demo and product surface.
- `social.elonara.com`: external Social surface displayed by World.

## Deployment Notes

- Apache should serve this project through `public/`, or the project-root `.htaccess` should rewrite requests into `public/`.
- No database migration is required for this app.
- Production can use explicit `.env` values, but the current code infers `.elonn.com` settings from the host when values are blank.

## Verification

Local:

```text
http://elonn.local/
http://elonn.local/account/login
http://elonn.local/account/register
http://elonn.local/account
```

Production:

```text
https://elonn.com/
https://elonn.com/account/login
https://elonn.com/account/register
https://elonn.com/account
```

Run PHP syntax checks:

```bash
find public src templates -name '*.php' -print0 | xargs -0 -n1 php -l
```
