# Order Shipping & Tracking System

This is a Laravel-based API project that implements user authentication and role-based access control using Laravel Sanctum and Spatie Permission.

## Requirements

- PHP >= 8.2
- Composer
- PostgreSQL

## Installation

1. Clone the repository:
```bash
git clone https://github.com/Akmalfauzi/interview-test-blueray-cargo
cd interview-test-blueray-cargo
```

2. Install PHP dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env` file:
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Biteship API Configuration
BITESHIP_DEVELOPMENT=true
BITESHIP_API_KEY=your_biteship_api_key
BITESHIP_API_URL=https://api.biteship.com/v1
```

6. Run database migrations:
```bash
php artisan migrate --seed
```

### Default Users
After running the migrations and seeders, the following users will be created:

#### Admin User
- **Email**: admin@example.com
- **Password**: password
- **Role**: Admin
- **Permissions**: All permissions

#### Regular Users
- **Email**: user@example.com
- **Password**: password
- **Role**: User
- **Permissions**: Basic user permissions

## Running the Project

1. Start the development server:
```bash
php artisan serve
```

2. For development with all services (server, queue, and logs):
```bash
composer dev
```

The application will be available at `http://localhost:8000`

## API Documentation

### Authentication Endpoints

#### Register User
- **URL**: `/api/v1/register`
- **Method**: `POST`
- **Description**: Register a new user
- **Request Body**:
  ```json
  {
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Registration successful",
    "data": {
      "message": "Registration successful",
      "user": {
        "name": "User Testing",
        "email": "usertesting@mail.com",
        "updated_at": "2025-05-16T06:40:34.000000Z",
        "created_at": "2025-05-16T06:40:34.000000Z",
        "id": 4,
        "profile_photo_url": null
      },
      "token": "8|ZbzZz5HeiAHUUXhg3bmiZiluVGtrT76hMtO46gdh02164785"
    }
  }
  ```

#### Login
- **URL**: `/api/v1/login`
- **Method**: `POST`
- **Description**: Authenticate user and get access token
- **Request Body**:
  ```json
  {
    "email": "string",
    "password": "string"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "message": "Login successful",
      "user": {
        "id": 1,
        "name": "Admin",
        "email": "admin@example.com",
        "email_verified_at": "2025-05-16T03:06:46.000000Z",
        "created_at": "2025-05-16T03:06:46.000000Z",
        "updated_at": "2025-05-16T03:06:46.000000Z",
        "phone": null,
        "address": null,
        "profile_photo_path": null,
        "profile_photo_url": null
      },
      "token": "7|13GvxjEHWFSaA5n8PElT0C034da0mSZHjjgHZG9D5092ac03"
    }
  }
  ```

#### Logout
- **URL**: `/api/v1/logout`
- **Method**: `POST`
- **Description**: Revoke the user's current token
- **Headers**: 
  - `Authorization: Bearer {token}`
- **Response**:
  ```json
  {
    "success": true,
    "message": "Successfully logged out",
    "data": {
      "message": "Successfully logged out"
    }
  }
  ```

## Testing

Run the test suite:
```bash
composer test
```

## Security

This project uses Laravel Sanctum for API authentication and Spatie Permission for role-based access control. Make sure to:

1. Keep your `.env` file secure and never commit it to version control
2. Use HTTPS in production
3. Regularly update dependencies
4. Follow Laravel security best practices

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
