# Salla Custom Box – Laravel Starter Kit

This repository contains the initial implementation of the Custom Box Builder for Salla, built using Laravel.

##  Project Overview

This is an evolving project focusing on:

* **Laravel Application Architecture**: Robust backend setup using the Salla Starter Kit.
* **Database Integration**: MySQL-driven product and user management.
* **Product Synchronization**: Automated fetching of store items via the Salla API.
* **Box Builder Interface**: A custom tool for creating product packages.
* **Webhook Integration**: Real-time product updates via Salla webhooks.

---

##  Project Requirements

Ensure your local environment meets these specifications:

* **PHP**: 8.1 or higher
* **Package Managers**: Composer & npm
* **Database**: MySQL
* **Tools**: Git

---

## Local Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/Esraaaak/Salla-Custom-Box.git
cd Salla-Custom-Box
```

### 2. Install Dependencies

```bash
composer install
npm install && npm run build
```

### 3. Configure Environment

Create your local environment file:

```bash
cp .env.example .env
php artisan key:generate
```

**Edit `.env` and configure your database and Salla settings:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=salla_app
DB_USERNAME=root
DB_PASSWORD=your_password

SALLA_WEBHOOK_SECRET=your_webhook_secret
SALLA_API_KEY=your_salla_api_key
```

### 4. Database Setup

Initialize the database schema:

```bash
php artisan migrate
```

### 5. Synchronizing Products from Salla

Pull your store's products into the local database:

```bash
php artisan app:sync-products
```

### 6. Running the Application

```bash
php artisan serve
```

Access the application at: `http://127.0.0.1:8000`

---

## Webhook: product.updated

### What It Does
When a product is updated in the Salla store, Salla automatically sends a `product.updated` event to this application. The app receives it and updates the product in the local database in real time — keeping the product list always up to date without manual syncing.

### How It Works
```
Salla store: product gets updated
    → Salla sends POST to /api/webhook
        → WebhookController receives it
            → App\Actions\Product\Updated handles it
                → Product is updated in local database 
```

### Webhook Route
```
POST /api/webhook
```

### Request Format
```json
{
  "event": "product.updated",
  "merchant": "1029864349",
  "created_at": "2026-02-19T12:00:00Z",
  "data": {
    "id": 123456,
    "sku": "PRDO-123456",
    "name": "Updated Product Name",
    "description": "New description from Postman test",
    "price": {
      "amount": 199.99
    },
    "quantity": 50,
    "main_image": "https://example.com/image.jpg"
  }
}
```
### How to Test the Webhook Locally

1. Open Postman
2. Method: `POST`
3. URL: `http://127.0.0.1:8000/api/webhook`
4. Headers tab:
   - `Content-Type: application/json`
   - `Authorization: your_webhook_secret_from_env`
5. Body tab → raw → JSON:
```json
{
    "event": "product.updated",
    "merchant": "1029864349",
    "data": {
        "id": 123456,
        "name": "Updated Product Name",
        "description": "New description",
        "price": { "amount": 199.99 },
        "quantity": 50,
        "main_image": "https://example.com/image.jpg"
    }
}
```
6. Click **Send**
7. Expected response: `{"success":true}`

**Verify in Database:**
```sql
SELECT * FROM Products WHERE id = 123456;
```
You should see the product saved with the new values.
### Related Files
```
app/Actions/Product/Updated.php     ← webhook handler logic
app/Http/Controllers/WebhookController.php  ← routes webhook to correct action
app/Http/Requests/WebhookRequest.php        ← validates & authenticates webhook
app/Models/Product.php              ← product model
```

---

## Troubleshooting & Usage Notes

### Accessing the Box Builder

1. Visit `http://127.0.0.1:8000` and click **"LogIn"** to authenticate.
2. The application is configured to redirect you to the builder automatically.
3. Direct Link: `http://127.0.0.1:8000/box_builder.html`.

### Common Issues

* **Redirect/Login Issues**: Open Browser Inspector -> Storage -> Cookies. Delete `salla_demo_app_session` for `127.0.0.1` and refresh the page.
* **Empty Product Lists**: Verify that `php artisan app:sync-products` completed successfully.
* **Database Errors**: Ensure your MySQL server is running and the `DB_DATABASE` name in `.env` matches your local database.
* **Webhook returns 403**: Check that `SALLA_WEBHOOK_SECRET` in `.env` matches the `Authorization` header value.
* **Webhook returns 422**: Make sure the request body includes `event`, `merchant`, and `data` fields.

