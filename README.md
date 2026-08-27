# Live Grid Project

Proof of concept for a live grid using Symfony UX Live Components, with optional Sylius Grid/Resource integration via an adapter.

## Architecture

- **Queryable** (`src/Queryable/`) — URL query-param sync for any Live Component (omit defaults, multi-instance namespaces, form fields, `pushState`/`popstate`). Sylius-free.
- **LiveGrid** (`src/LiveGrid/`) — Generic `live_grid` Live Component, ports, Twig hooks, events. Configure lists in YAML; render with any Twig templates.
- **Sylius adapter** (`src/Adapter/Sylius/`) — Native bridge to Sylius Grid + Resource (definitions, actions, `allowed_paginate`, Doctrine QB + Spiriit filters).

### Template usage

```twig
{{ component('live_grid', { name: 'admin_user' }) }}
```

Config in `config/packages/live_grid.yaml`:

```yaml
live_grid:
  grids:
    admin_user:
      adapter: sylius
      filter_form: App\Component\User\Form\FilterType\UserListFilterType
      options:
        grid: app_admin_user
```

### Key Features

1. **URL State Persistence** - Component state is automatically saved to and restored from URL parameters
2. **Browser History Support** - Back/forward buttons work correctly with component state
3. **Clean URLs** - Default values are not included in URLs to keep them clean
4. **Multiple Components** - Multiple instances can coexist on the same page (as shown in the template)
5. **Form Integration** - Search filters are integrated with the component's state management
6. **OCP / adapters** - Add grids without new PHP list classes; swap Sylius for `in_memory` or a custom adapter

### Custom Query String Management

The project implements a custom solution for query string management because the built-in Live Components QueryParams didn't meet the requirements. The custom implementation:
- Only includes non-default values in URLs
- Supports multiple component instances on the same page
- Handles browser navigation events properly
- Maintains component isolation while sharing URL state

See [docs/live-component-url-sync.md](docs/live-component-url-sync.md).

# Installation

## Prerequisites

- Docker and Docker Compose installed on your system
- Make utility (usually pre-installed on Linux/macOS)

### 1. Build the Docker containers

```bash
make build
```

This command builds the Docker containers defined in the `docker-compose.yml` file.

### 2. Start the application

```bash
make up
```

This command starts all the services (PHP, Nginx, MySQL) in detached mode.

### 3. Access the application container

```bash
make bash
```

This command opens a bash shell inside the PHP application container.

### 4. Install dependencies and setup the database

Once inside the container, run the following commands:

#### a) Install Composer dependencies

```bash
composer install
```

#### b) Install Node.js assets and build them

```bash
yarn install && yarn dev
```

#### c) Run database migrations

```bash
php bin/console d:m:m
```

#### d) Load database fixtures

```bash
php bin/console doctrine:fixtures:load
```

## Accessing the Application

After completing the installation steps:

- **Web Application**: http://localhost/
- **Admin Panel**: http://localhost/admin (login with admin@live-grid.com | 111)
- **Database**: localhost:3306
  - Database: `live-grid`
  - Username: `live-grid_user`
  - Password: `live-grid_pass`

## Available Make Commands

- `make build` - Build Docker containers
- `make up` - Start all services
- `make up_visible` - Start all services with visible output
- `make down` - Stop all services
- `make bash` - Access the PHP container shell
- `make static` - Run PHPStan static analysis (not implemented yet)

