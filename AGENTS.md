# Agent guide — live-grid

Symfony 6.1 POC: Sylius Grid + UX Live Components with **custom URL query-param sync** (not Symfony `LiveParam url`).

## Stack

- PHP 8.1+, Symfony 6.1, Doctrine, Sylius Grid/Resource, UX Live Components
- MariaDB 10.2, Nginx, Docker Compose
- PHPUnit 9 (no Playwright yet)

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

- Login: http://localhost/en_GB/login
- Admin users list: http://localhost/admin/users/
- Admin user: `admin@live-grid.com` / `111`

DB (host `db` from container, `localhost:3306` from host): `live-grid` / `live-grid_user` / `live-grid_pass`

## Run tests

Tests use `APP_ENV=test` (see `.env.test`). Prefer Docker:

```bash
make up
make install

# one-time test DB setup (uses same DB name; DAMA rolls back per test)
docker-compose exec -u 1000 app php bin/console doctrine:migrations:migrate --env=test --no-interaction
docker-compose exec -u 1000 app php bin/console doctrine:fixtures:load --env=test --no-interaction

make test
```

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
| Helpers | `tests/AbstractWebTestCase.php`, `tests/Component/LiveComponentTestHelper.php` |

Functional tests auth via `$client->loginUser($admin)` — **do not** POST the login form.

PHPUnit config: `phpunit.xml.dist`. DAMA Doctrine Test Bundle wraps each test in a transaction.

## Agent tips

- Run commands in Docker (`make test`, `make install`); do not assume host PHP/composer.
- After changing query-param or list behavior, run the full PHPUnit suite.
- Browser `pushState` / `popstate` is **not** covered by PHPUnit; use Playwright later if needed.
- Do not commit `.idea/` or secrets from `.env.local`.
- `make static` runs PHPStan (level max) via `phpstan.dist.neon`; warms the dev cache first for the Symfony container XML.
