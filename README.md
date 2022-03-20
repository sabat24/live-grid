# Live Grid Project

Proof of concept for a live grid using Symfony UX Live Components and Sylius Grid Bundle.

## The Main Concept

I started this project in January 2023, when Live Components were still in the experimental phase. The main idea was to create reusable components that could be placed on a single page, with the ability to save their state in the URL for recall, supporting browser history and navigation via browser buttons. However, I abandoned this concept for various personal and professional reasons, so the project wasn't developed much beyond testing whether the concept would work.

At that time, the QueryParams option didn't exist in Live Components, so I created my own solution. The current QueryParams option doesn't quite meet all my requirements. For example, the component's default values (including empty ones) shouldn't be sent to the URL, which would keep URLs cleaner and more user-friendly.

Because I started with the concept of reusable components on a single page, I didn't focus solely on lists (Grid Bundle), even though that was the core functionality. The goal was to create a more flexible system where different types of components could coexist and share state.

I also wanted the list component to have filters, a list, and pagination built into it as a single cohesive unit. Unlike the basic template approach, where filters and the list are visually separated into two distinct blocks, I envisioned a more integrated solution.

Additionally, I wanted a nice, mobile-responsive table view, so I decided to use a separate template. However, the functionality itself is designed to be independent of the template, allowing for different visual representations of the same data.

Currently, some aspects of the example component are still too strongly tied to a specific entity, as I haven't yet created a universal component configuration system. This is one of the areas that would need further development to make the components truly reusable across different entities and use cases.

### How the Component Works

The live grid component is implemented using a combination of PHP Live Components and JavaScript Stimulus controllers:

#### Backend Implementation
- **`src/Component/User/LiveComponent/Admin/UserListComponent.php`** - The main Live Component class that handles:
    - Pagination state (`page`, `resultsPerPage`)
    - Form handling for filters
    - Grid data management using Sylius Grid Bundle
    - Custom query string management via `QueryableComponentTrait`

#### Frontend Implementation
- **`assets/controllers/queryable_controller.js`** - Stimulus controller that manages:
    - Browser history integration
    - URL state synchronization
    - Component re-rendering on browser navigation
    - Query parameter management (adds/removes/updates URL params)

#### Template Usage
- **`templates/Admin/Crud/index.html.twig`** - Basic template showing how to use the component:
  ```twig
  {{ component('admin:user_list') }}
  ```

### Key Features

1. **URL State Persistence** - Component state is automatically saved to and restored from URL parameters
2. **Browser History Support** - Back/forward buttons work correctly with component state
3. **Clean URLs** - Default values are not included in URLs to keep them clean
4. **Multiple Components** - Multiple instances can coexist on the same page (as shown in the template)
5. **Form Integration** - Search filters are integrated with the component's state management

### Custom Query String Management

The project implements a custom solution for query string management because the built-in Live Components QueryParams didn't meet the requirements. The custom implementation:
- Only includes non-default values in URLs
- Supports multiple component instances on the same page
- Handles browser navigation events properly
- Maintains component isolation while sharing URL state

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

#### b) Install Node.js assets

```bash
yarn install
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

- **Web Application**: http://localhost/en_GB/login
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

