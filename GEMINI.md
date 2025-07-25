# Project: Crime Map Visualization

## Project Overview

A web application to visualize crime data on an interactive map. The application allows users to view crime statistics, filter data by location and type, and see trends over time. The goal is to provide a clear and accessible platform for understanding crime patterns in a given area.

## Technology Stack

- **Backend:** Laravel 12
- **Frontend:** Inertia.js with Vue 3
- **Database:** SQLite (for local development), PostgreSQL (for production)
- **Styling:** Tailwind CSS
- **Build Tool:** Vite

## Project Structure

The project follows the standard Laravel application structure. Key directories include:

- `app/Http/Controllers`: Contains controllers that handle HTTP requests.
- `app/Models`: Contains the Eloquent models for database interaction (e.g., `CrimeData`, `User`).
- `app/Providers`: Contains service providers for bootstrapping the application.
- `config`: Contains application configuration files.
- `database/migrations`: Contains database schema definitions.
- `database/seeders`: Contains database seeders for populating the database with initial data.
- `public`: The web server's document root.
- `resources/js`: Contains all frontend assets, including Vue components, JavaScript, and CSS.
- `resources/js/Pages`: Contains the Inertia.js page components.
- `routes`: Contains route definitions for the application.
- `tests`: Contains all application tests.

## Local Development Setup

To set up the project for local development, follow these steps:

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/crime-map.git
    cd crime-map
    ```

2.  **Install dependencies:**

    ```bash
    composer install
    npm install
    ```

3.  **Set up environment variables:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

    _Ensure your `.env` file is configured correctly, especially the `DB_CONNECTION` and other database settings._

4.  **Set up the database:**

    ```bash
    touch database/database.sqlite
    php artisan migrate --seed
    ```

5.  **Run the development servers:**
    - In one terminal, run the Laravel development server:
        ```bash
        php artisan serve
        ```
    - In another terminal, run the Vite development server:
        ```bash
        npm run dev
        ```

## Testing

The project uses PHPUnit for backend testing. To run the test suite, use the following command:

```bash
php artisan test
```

## Coding Standards & Conventions

- **PHP:** We follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard.
- **JavaScript/Vue:** We use [Prettier](https://prettier.io/) for code formatting and [ESLint](https://eslint.org/) for linting.
- **Commits:** We follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification.

To format your code before committing, run:

```bash
npx prettier --write .
```

To check for linting errors, run:

```bash
npm run lint
```
