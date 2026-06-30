# Agent guide — live-grid

Symfony 7.4 POC: Sylius Grid + UX Live Components with **custom URL query-param sync** (not Symfony `LiveParam url`).

## Stack

- PHP 8.4+, Symfony 7.4, Doctrine, Sylius Grid/Resource, UX Live Components 3.x
- MariaDB 10.2, Nginx, Docker Compose
- PHPUnit 12 + Playwright E2E (`playwright-php/playwright-symfony`)

## Run the project (Docker)

All PHP/Composer/test commands go through the **app** container:

```bash
make build          # first time
make up
make install        # composer install in container
make bash           # shell inside app container
```

Inside container (first setup):

```bash
yarn install
yarn dev
php bin/console d:m:m
php bin/console doctrine:fixtures:load
```

App URLs:

- Login: http://localhost/en/login
- Admin users list: http://localhost/admin/users/
- Admin user: `admin@live-grid.com` / `111`

DB (host `db` from container, `localhost:3306` from host): `live-grid` / `live-grid_user` / `live-grid_pass`

E2E DB: `live-grid_e2e` on `db_e2e` (host port `3307`). See [docs/playwright.md](docs/playwright.md).

## Deployment (bare-metal server)

Day-to-day development uses Docker (above). For a server checkout with `php84`, Composer, and Yarn on the host:

```bash
./deploy.sh          # production: git pull, composer --no-dev, encore production, migrate, cache warmup
./deploy.sh local    # local bare-metal: git pull, composer, encore dev, migrate, cache:clear
make deploy          # same as ./deploy.sh
make deploy-local    # same as ./deploy.sh local
```

Requires `.env.local` on the server (`APP_ENV=prod`, `APP_SECRET`, `DATABASE_URL`). No background workers or Mercure — web-only app.

## Run tests

### PHPUnit (unit / integration / functional)

Tests use `APP_ENV=test` (see `.env.test`). Prefer Docker:

```bash
make up
make install
make migrate-test
docker-compose exec -u 1000 app php bin/console doctrine:fixtures:load --env=test --no-interaction
make test
```

DAMA Doctrine Test Bundle rolls back each test on `db` / `live-grid`.

### Playwright E2E

```bash
make playwright-install   # one-time
make migrate-e2e          # one-time (or after migrations change)
docker-compose exec app yarn encore production
make e2e
```

E2E uses `phpunit.e2e.xml` → `db_e2e`, **no DAMA**. Layered fixtures: core admin (process) + 25 users (user-list suite only).

See [docs/playwright.md](docs/playwright.md) for `make e2e-suite`, `make e2e-file`, and `make up-e2e`.

Single suite / filter:

```bash
docker-compose exec -u 1000 app vendor/bin/phpunit --filter AdminUserListFunctionalTest
```

Fixtures: 1 admin + 25 users (26 total → 3 pages at 10/page).

## Query-param system (read this before changing list/URL behavior)

| Piece | Path |
|-------|------|
| URL build/apply logic | `src/Component/LiveComponent/Service/QueryableParamsBuilder.php` |
| Live component hook | `src/Component/LiveComponent/Trait/QueryableComponentTrait.php` |
| List component | `src/Component/User/LiveComponent/Admin/UserListComponent.php` |
| Browser URL sync | `assets/controllers/queryable_controller.js` |
| Demo page (2 lists) | `templates/Admin/Crud/index.html.twig` |

Rules: omit default/empty `#[QueryableProp]` values from the query string; each list instance has its own namespace (`live-*` id when two on one page).

## Test layout

| Layer | Location |
|-------|----------|
| Unit | `tests/Component/QueryableParamsBuilderTest.php` |
| Trait | `tests/Component/QueryableComponentTraitTest.php` |
| Integration | `tests/User/UserListComponentIntegrationTest.php` |
| HTTP / Live actions | `tests/User/AdminUserListFunctionalTest.php` |
| Playwright E2E | `tests/E2e/` |
| Helpers | `tests/AbstractWebTestCase.php`, `tests/Component/LiveComponentTestHelper.php` |

Functional tests auth via `$client->loginUser($admin)` — **do not** POST the login form.

PHPUnit config: `phpunit.xml.dist` (excludes `tests/E2e`). E2E: `phpunit.e2e.xml`.

## Agent tips

- Run commands in Docker (`make test`, `make e2e`, `make install`); do not assume host PHP/composer.
- After changing query-param or list behavior, run the full PHPUnit suite (`make test`).
- Browser `pushState` / `popstate` is covered by Playwright E2E, not PHPUnit.
- In-process E2E uses temporary converter workarounds until [playwright-symfony#4](https://github.com/playwright-php/playwright-symfony/pull/4) and [#6](https://github.com/playwright-php/playwright-symfony/pull/6) merge — see [docs/playwright.md](docs/playwright.md#in-process-intercept-workarounds-temporary).
- Do not commit `.idea/` or secrets from `.env.local`.
- `make static` runs PHPStan (level max) via `phpstan.dist.neon`; warms the dev cache first for the Symfony container XML.
