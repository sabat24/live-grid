# Playwright E2E tests

Browser end-to-end tests use [`playwright-php/playwright-symfony`](https://github.com/playwright-php/playwright-symfony) with PHPUnit in the Symfony `test` environment (Chromium, headless).

## Testing strategy

Use the **fastest test that can assert the behaviour**.

| Prefer | When |
|--------|------|
| **PHPUnit + `KernelBrowser`** | HTTP status, redirects, Live Component round-trips via `LiveComponentTestHelper`, query-param hydration on GET/reload, filters, pagination, dual-list isolation |
| **PHPUnit + `KernelTestCase`** | Domain services, `QueryableParamsBuilder`, repositories, trait mount/history simulation |
| **Playwright E2E** (`tests/E2e/`, `make e2e`) | Real browser JS (`pushState` / `popstate`), URL sync after `location.reload()`, custom UI controls (e.g. Huro dropdown) |

### Rules of thumb

1. **If `assertSelectorExists` / `assertSelectorTextContains` on a `KernelBrowser` response works, use PHPUnit** — not Playwright.
2. **Reserve Playwright for UX mechanics PHPUnit cannot see** — e.g. `history.pushState`, `popstate`, full page reload with committed URL, custom dropdown open + Live click wiring.
3. **Do not duplicate coverage** — server hydration after reload belongs in PHPUnit; browser history glue belongs in E2E.
4. **Component namespace naming** (`admin:user_list` vs `live-*` vs custom `data-live-id`) is a PHP mount concern only; it does not change `queryable_controller.js` behaviour. Test naming variants in PHPUnit, not with separate E2E routes.

Functional tests in [`tests/User/AdminUserListFunctionalTest.php`](../tests/User/AdminUserListFunctionalTest.php) cover server-side query-param hydration; browser history is covered by [`tests/E2e/Admin/UserListUrlSyncE2eTest.php`](../tests/E2e/Admin/UserListUrlSyncE2eTest.php).

## Databases

| Suite | Database | Host | DAMA |
|-------|----------|------|------|
| `make test` | `live-grid` | `db` | yes (rollback per test) |
| `make e2e` | `live-grid_e2e` | `db_e2e` | **no** (commits survive reloads) |

## Fixture tiers (E2E)

1. **`make migrate-e2e`** — schema once per environment / CI
2. **Process** (`AbstractE2eTestCase`) — purge + admin only (`admin@live-grid.com` / `111`)
3. **Class** (`AbstractUserListE2eTestCase`) — 25 users for pagination tests
4. **Per method** — no automatic reseed

Admin authentication is programmatic via Symfony session cookie (`PlaywrightSessionAuthenticator`), not form login — mirrors `KernelBrowser::loginUser()` and reuses the cached session across tests after each browser context restart.

## One-time setup

```bash
make build && make up && make install
make playwright-install
make migrate-test
make migrate-e2e
docker-compose exec app yarn install && docker-compose exec app yarn encore production
```

## Running

```bash
make e2e
make e2e-suite SUITE="E2E UserList"
make e2e-file FILE=tests/E2e/Admin/UserListUrlSyncE2eTest.php
```

`make e2e` uses normal `make up` (dev PHP-FPM). Playwright intercepts HTTP to the in-process test kernel; the dev database is untouched.

### In-process intercept workarounds (temporary)

`playwright-php/playwright-symfony` v0.9 has gaps when PHPUnit drives Chromium against the kernel directly (not nginx). Until upstream fixes land, live-grid ships **test-only** converters under [`tests/E2e/Client/`](../tests/E2e/Client/), wired in [`AbstractE2eTestCase`](../tests/E2e/AbstractE2eTestCase.php). Normal browser traffic through nginx does **not** use these classes.

| Converter | Problem | Workaround |
|-----------|---------|------------|
| `MultipartAwareRequestConverter` | `postData()` is null for multipart Live actions → empty `args` | Rehydrate from `postDataBuffer()` via `BufferedPostDataRequest` |
| `LiveComponentAwareResponseConverter` | `application/vnd.live-component+html` treated as binary (base64) → morphdom never updates DOM | Treat `+html` vendor MIME as text in `isBinaryContentType()` |

**Upstream (remove workarounds after merge + `composer update`):**

| Bug | Issue | PR |
|-----|-------|-----|
| Multipart `postDataBuffer()` fallback | [playwright-php/playwright-symfony#3](https://github.com/playwright-php/playwright-symfony/issues/3) | [playwright-php/playwright-symfony#4](https://github.com/playwright-php/playwright-symfony/pull/4) |
| `+html` structured MIME suffix | [playwright-php/playwright-symfony#5](https://github.com/playwright-php/playwright-symfony/issues/5) | [playwright-php/playwright-symfony#6](https://github.com/playwright-php/playwright-symfony/pull/6) |

**Cleanup checklist** (once both PRs are released):

1. Bump `playwright-php/playwright-symfony` in `composer.json`
2. Delete `tests/E2e/Client/MultipartAwareRequestConverter.php`, `LiveComponentAwareResponseConverter.php`, `BufferedPostDataRequest.php`
3. Revert `AbstractE2eTestCase` to default `RequestConverter` / `ResponseConverter` (no custom constructor args)
4. Remove this subsection from this doc
5. Run `make e2e` and `make test`

### Manual browser against E2E DB

```bash
make up-e2e   # PHP-FPM serves APP_ENV=test against db_e2e
# browse http://live-grid.lh
make up       # restore dev
```
