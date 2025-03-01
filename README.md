# Products API

Welcome to the Products API! This is a RESTful API designed to manage and interact with products in an e-commerce system. It allows users to view, create, update, delete, and manage products, as well as place orders for those products. This API is built with Laravel and provides robust features for managing product-related data. The application enforces strict authorization, ensuring that users can only interact with their own orders and reviews, and includes error handling for database and other operational issues.

## Prerequisites

Before deploying the project, ensure you have the following installed:

- **Docker**: Docker and Docker Compose are required to containerize the application and PostgreSQL database. You can download Docker from [here](https://www.docker.com/get-started).
- **PHP**: PHP is used for managing Laravel dependencies; however, it will be handled by Docker containers.
- **Git**: Git is required to clone the repository.
- **Composer**: Composer is a PHP dependency manager, and it will be used to install the Laravel project dependencies (although this will be managed via Docker, having it installed locally can help with troubleshooting).

## Step-by-Step Deployment Instructions

Follow these steps to deploy the application:

### 1. Clone the Repository

Clone the repository to your local machine using Git:

```bash
git clone https://github.com/dmitrycrockodile/products_api.git your-repository-name
cd your-repository-name
```

### 2. Create the ```.env``` File

Copy the example environment file to create your own `.env `file:

```bash
cp .env.example .env
```

This file contains the environment variables required for the Laravel application to function, including database connection settings.

### 3. Docker setup

The project uses Docker Compose to set up the environment. In the project root directory, run the following command to start the Docker containers:

```bash
docker-compose up -d
```

This will build and start the following containers:

app: The Laravel application container.  
db: The MariaDB database container.  
Make sure that the `.env` file is properly configured to connect to the MariaDB container:

```bash
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

The `DB_HOST` should be set to db, which is the name of the MariaDB container defined in the `docker-compose.yml` file.

### 4. Install Composer Dependencies

Once the containers are running, install the Laravel dependencies using Composer. Enter the `app` container and run:

```bash
docker exec -it <container_name> bash
composer install
```

### 5. Set Up the Database

After Composer installs the dependencies, run the following command to migrate the database:

```bash
php artisan migrate
```

This will create the necessary database tables (like `products` and `users`) according to the migrations defined in the Laravel application.

### 6. Create and Seed the Database to get pre-created users

To populate the database with test users run:

```bash
php artisan db:seed UserSeeder
```

### 7. Run the tests (optional)

You can run the tests using PHPUnit to ensure everything is working correctly:

```bash
docker exec -it <container_name> bash
php artisan test --filter LoginControllerTest
php artisan test --filter LogoutControllerTest
php artisan test --filter RegisterControllerTest
php artisan test --filter OrderControllerTest
php artisan test --filter ProductControllerTest
php artisan test --filter ReviewControllerTest
```

Or:

```bash
docker exec -it <container_name> bash
php artisan test
```

## API Base URL
All API requests should be made to:
`http://localhost:8876/api`

## Testing the API Endpoints

#### Get all products
**Description:** Retrieves a list of products for the unauthenticated user. User can use filters like `categories`, `prices`, `sortby`, `title` and `highRated`.
**Response:** A confirmation message and a list of the products in JSON format.

```bash
  GET /products
```

#### Create product
**Description:** Creates a new product. Requires a JSON payload with `title`, `description`, `preview_image`, `price`, `count` and `category_id`.  
**Response:** A confirmation message and the created product in JSON format.

```bash
  POST /products
```

#### Update product
**Description:** Updates an existing product. Requires the product `id` and updated data.  
**Response:** A confirmation message and the updated product in JSON format.

```bash
  PUT /products/{product}
```

#### Delete product
**Description:** Deletes the specified product.  
**Response:** A confirmation message indicating that the product is deleted.

```bash
  DELETE /products/{product}
```

#### Register user
**Description:** Registers a user. Requires a JSON payload with `name`, `email` `password` and `password_confirmation`.  
**Response:** A confirmation message and the registered user email in JSON format.

```bash
  POST /register
```

#### Login the user
**Description:** Logins the user. Requires a JSON payload with `email` and `password`.  
**Response:** A confirmation message with the user id and email in JSON format.

```bash
  POST /login
```

#### Logout the user
**Description:** Logouts a user. Requires an `id`.  
**Response:** A confirmation message.

```bash
  POST /logout
```

#### Get all orders
**Description:** Retrieves a list of orders for the authenticated user.
**Response:** A confirmation message and a list of the user orders in JSON format.

```bash
  GET /orders
```

#### Create an order
**Description:** Creates a new order. Requires a JSON payload with the `items` containing the `product_id` and the `quantity`.  
**Response:** A confirmation message and the created order in JSON format.

```bash
  POST /orders
```

#### Get all reviews
**Description:** Retrieves a list of all reviews.
**Response:** A confirmation message and a list of the reviews in JSON format.

```bash
  GET /reviews
```

#### Create a review
**Description:** Creates a new review. Requires a JSON payload with the `rating`, `title`, `product_id` and the `body` (not required option).  
**Response:** A confirmation message and the created order in JSON format.

```bash
  POST /review
```

#### Delete review
**Description:** Deletes the specified review. User can delete only his review. 
**Response:** A confirmation message indicating that the review is deleted.

```bash
  DELETE /reviews/{review}
```

## Pre-created users

For testing purposes, the following users have been pre-created in the database. You can use these credentials to test the API:

### 1. Joe
**Name:** `Joe`  
**Email:** `joe.test@example.com`  
**Password:** `joes_password`

### 2. John
**Name:** `John`  
**Email:** `john.test@example.com`  
**Password:** `johns_password`

### 3. Joel
**Name:** `Joel`  
**Email:** `joel.test@example.com`  
**Password:** `joels_password`