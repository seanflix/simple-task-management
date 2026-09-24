# Simple Task Management

A small Laravel app for creating projects and ordering their tasks by priority.

## Tech Stack Used

- PHP 8.3+ and Laravel 13 for the backend
- MySQL for the database
- Inertia.js with Vue 3 for the frontend
- Tailwind CSS 4 for styling
- Vite to build frontend
- Node.js and npm to install and build frontend packages

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL, such as the MySQL server included with XAMPP

## First time setup

1. Start MySQL server.
2. Create a database named `tasks`.
3. Install PHP and JavaScript dependencies:

```
composer install
npm install
```

4. Create the environment file by copying `.env.example` to the same directory and renaming it to `.env`
5. Set the database values in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tasks
DB_USERNAME=root
DB_PASSWORD=
```

6. Generate the application key and run the migration:

```
php artisan key:generate
php artisan migrate
```

7. Build the frontend:

```
npm run build
```

## Run the app

Start the server:

```
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).
