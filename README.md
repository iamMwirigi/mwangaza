# Symfony API Project

This is a clean Symfony 7 API project integrated with Swagger for documentation and MySQL for the database.

## Project Setup

1. **Database Configuration**:
   Update the `DATABASE_URL` in your `.env` file with your MySQL credentials:
   ```dotenv
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0.32&charset=utf8mb4"
   ```

2. **Database Migrations**:
   Once you've configured your database, run the following commands to create the database and run migrations:
   ```bash
   php bin/console doctrine:database:create
   php bin/console make:migration
   php bin/console doctrine:migrations:migrate
   ```

3. **Running the Server**:
   You can use the Symfony local server (if installed):
   ```bash
   symfony serve
   ```
   Or the built-in PHP server:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

## API Documentation

The project uses `NelmioApiDocBundle` to generate Swagger documentation.

- **Swagger UI**: Access it at `http://localhost:8000/api/doc`
- **OpenAPI JSON**: Available at `http://localhost:8000/api/doc.json`

## Project Structure

- `src/Entity/User.php`: The User entity with validation and serialization groups.
- `src/Controller/UserController.php`: RESTful controller for User resources with Swagger annotations.
- `config/packages/nelmio_api_doc.yaml`: Configuration for Swagger documentation.

## Security & Deployment

- All sensitive configuration (like database credentials) should stay in `.env.local` (not committed).
- The `.env` file contains placeholders for your environment variables.
- The project follows standard RESTful routing conventions (`/api/user` and `/api/user/{id}`).
