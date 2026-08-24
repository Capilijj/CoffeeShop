# Coffee Shop Management System

A PHP and MySQL-based coffee shop ordering system for customers and administrators. Users can register and log in, browse the menu, customize orders, upload GCash proof of payment, and track order status. An admin area is also included for managing the menu, add-ons, orders, and customer feedback.

## Features

- User registration, login, logout, and password reset with email OTP
- Coffee menu with images, prices, and add-on options
- Online ordering and GCash proof-of-payment upload
- Order history, cancellation, and status tracking
- User profile management and profile image upload
- Admin dashboard for orders and status updates
- Admin menu and add-on management
- Customer feedback management

## Requirements

- PHP 8.0 or newer
- MySQL/MariaDB
- Composer
- PHP extensions: `mysqli`, `mbstring`, and `openssl`

## Local Setup

1. Clone or download the project, then open the project folder in a terminal.

2. Install the PHP dependencies:

	```powershell
	composer install
	```

3. Create a MySQL database named `sample_users`.

4. Import [sample_users (3).sql](sample_users%20(3).sql) using phpMyAdmin or a MySQL client.

5. Check the database settings in [LoginPage/database_connection.php](LoginPage/database_connection.php). The default local setup is:

	- Host: `localhost`
	- Port: `3306`
	- Database: `sample_users`
	- Username: `root`
	- Password: blank if the local MySQL server has no password

6. To enable email OTP, create a `.env` file in the project root. Do not commit this file.

	```env
	MAIL_HOST=smtp.gmail.com
	MAIL_USERNAME=your-email@gmail.com
	MAIL_PASSWORD=your-app-password
	MAIL_ENCRYPTION=tls
	MAIL_PORT=587
	```

7. Start the built-in PHP server:

	```powershell
	php -S localhost:8000
	```

8. Open `http://localhost:8000` in your browser.

## Project Structure

- `index.php` - main login and registration page
- `Dashboard/` - customer dashboard
- `sections/` - home page sections and menu
- `Order/` - order creation and saving
- `Orders/` - order history and cancellation
- `Profile/` - customer profile
- `Admin/` - admin dashboard and management pages
- `LoginPage/` - authentication, database connection, and email OTP
- `Image/` and `ImageMenu/` - menu and site images
- `uploads/` - uploaded profile images and payment proofs; local-only files

## Notes

- Make sure MySQL/MariaDB is running before opening the site.
- If the database connection is `actively refused`, check that the port in `database_connection.php` is correct and that the MySQL service is running.
- The admin pages are accessible only to accounts with the `admin` role in the database.