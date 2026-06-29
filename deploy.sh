#!/bin/bash
# deploy.sh - Deploy Symfony project updates with environment selection
#
# Usage:
#   ./deploy.sh           # Runs deployment in production mode (default)
#   ./deploy.sh local     # Runs deployment in local environment mode
#
# Server prerequisites:
#   - php84 alias on PATH (PHP 8.4 with pdo_mysql, zip, intl)
#   - Composer globally available
#   - Node + Yarn
#   - Git checkout with deploy credentials
#   - .env.local with APP_ENV=prod, APP_SECRET, DATABASE_URL (production MySQL host)
#   - Writable var/ directory
#   - Nginx root pointing to public/
#
# Optional after deploy if OPcache serves stale code:
#   sudo systemctl reload php8.4-fpm

set -e

PHP_BIN="php84"
DEPLOY_BRANCH="master"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSER_BIN="$(command -v composer)"

remove_stale_prod_cache() {
    if [ ! -e var/cache/prod ]; then
        return 0
    fi

    # PHP-FPM may still have preload files open under this path; rename aside first.
    local stale="var/cache/prod.stale.$$"
    if mv var/cache/prod "${stale}" 2>/dev/null; then
        /bin/rm -rf "${stale}" &
        return 0
    fi

    /bin/rm -rf var/cache/prod
}

if [ -z "${COMPOSER_BIN}" ]; then
    echo "Error: composer not found on PATH." >&2
    exit 1
fi

if ! command -v "${PHP_BIN}" >/dev/null 2>&1; then
    echo "Error: ${PHP_BIN} not found on PATH." >&2
    exit 1
fi

ENVIRONMENT="production"
if [ "$1" == "local" ]; then
    ENVIRONMENT="local"
fi

declare -a PROD_STEPS=(
    "Pulling latest changes from git repository (${DEPLOY_BRANCH} branch)"
    "Removing stale production cache (avoids cache:clear booting an incompatible container)"
    "Installing Composer dependencies for production"
    "Installing Node dependencies for production"
    "Building frontend assets for production"
    "Running database migrations for production"
    "Warming up Symfony cache for production"
    "Ensuring var/ directory is writable"
)

declare -A PROD_COMMANDS
PROD_COMMANDS[0]="git pull origin ${DEPLOY_BRANCH}"
PROD_COMMANDS[1]="remove_stale_prod_cache"
PROD_COMMANDS[2]="APP_ENV=prod APP_DEBUG=0 ${PHP_BIN} \"${COMPOSER_BIN}\" install --no-interaction --prefer-dist --optimize-autoloader --no-dev"
PROD_COMMANDS[3]="yarn install --frozen-lockfile"
PROD_COMMANDS[4]="NODE_ENV=production yarn encore production --progress"
PROD_COMMANDS[5]="${PHP_BIN} bin/console doctrine:migrations:migrate --no-interaction --env=prod"
PROD_COMMANDS[6]="remove_stale_prod_cache && ${PHP_BIN} bin/console cache:warmup --env=prod"
PROD_COMMANDS[7]="mkdir -p var/log && chmod -R ug+rwx var"

declare -a LOCAL_STEPS=(
    "Pulling latest changes from git repository (current branch)"
    "Installing Composer dependencies for local"
    "Installing Node dependencies for local"
    "Building frontend assets for local"
    "Running database migrations and clearing cache for local"
)

declare -A LOCAL_COMMANDS
LOCAL_COMMANDS[0]="git pull"
LOCAL_COMMANDS[1]="${PHP_BIN} \"${COMPOSER_BIN}\" install --no-interaction --prefer-dist --optimize-autoloader"
LOCAL_COMMANDS[2]="yarn install"
LOCAL_COMMANDS[3]="yarn encore dev"
LOCAL_COMMANDS[4]="${PHP_BIN} bin/console doctrine:migrations:migrate --no-interaction && ${PHP_BIN} bin/console cache:clear"

if [ "$ENVIRONMENT" == "production" ]; then
    STEPS=("${PROD_STEPS[@]}")
    declare -n COMMANDS=PROD_COMMANDS
else
    STEPS=("${LOCAL_STEPS[@]}")
    declare -n COMMANDS=LOCAL_COMMANDS
fi

TOTAL_STEPS=${#STEPS[@]}
CURRENT_STEP=1

cd "${PROJECT_DIR}"

echo "--------------------------------------------"
echo "Starting Symfony deployment for '${ENVIRONMENT}' environment..."
echo "Project directory: ${PROJECT_DIR}"
echo "PHP binary: ${PHP_BIN}"
echo "--------------------------------------------"

for ((i=0; i<${TOTAL_STEPS}; i++)); do
    echo "[${CURRENT_STEP}/${TOTAL_STEPS}] ${STEPS[$i]}"
    eval "${COMMANDS[$i]}"
    CURRENT_STEP=$((CURRENT_STEP + 1))
done

echo "--------------------------------------------"
echo "Symfony deployment for '${ENVIRONMENT}' completed successfully!"
echo "--------------------------------------------"
