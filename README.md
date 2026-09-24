# Simple Task Management

A small Laravel app for creating projects and ordering their tasks by priority.

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL, such as the MySQL server included with XAMPP

## First-time setup

1. Start MySQL.
2. Create a database named `tasks`.
3. Install the PHP and JavaScript dependencies:

```bash
composer install
npm install
```

4. Create the environment file:

```bash
cp .env.example .env
```

On Windows:

```bash
copy .env.example .env
```

5. Set the database values in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tasks
DB_USERNAME=root
DB_PASSWORD=
```

6. Generate the application key and create the tables:

```bash
php artisan key:generate
php artisan migrate
```

7. Build the frontend:

```bash
npm run build
```

## Run the app

Start the server:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

After changing Vue, CSS, or other frontend files, run `npm run build` again so the page picks up those changes.

To rebuild the frontend automatically while you work, run this in a second terminal and leave it open:

```bash
npm run dev
```
# simple-task-management
