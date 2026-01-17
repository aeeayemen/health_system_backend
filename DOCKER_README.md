# Docker Setup for Health Nutrition API

This project includes a Docker setup for easy development and deployment.

## Prerequisites

- Docker
- Docker Compose

## Getting Started

1.  **Build and start the containers:**

    ```bash
    docker-compose up -d --build
    ```

2.  **Install dependencies:**

    ```bash
    docker-compose exec app composer install
    ```

3.  **Run migrations:**

    ```bash
    docker-compose exec app php artisan migrate
    ```

4.  **Access the application:**

    The API will be available at `http://localhost:8000`.

## Services

-   **app**: PHP 8.2 FPM container serving the Laravel application.
-   **webserver**: Nginx container serving as the web server.
-   **db**: MySQL 8.0 container for the database.

## Useful Commands

-   **Stop containers:** `docker-compose down`
-   **View logs:** `docker-compose logs -f`
-   **Run Artisan command:** `docker-compose exec app php artisan <command>`
