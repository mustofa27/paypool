# PayPool - Xendit Payment Bridge API

## Overview
PayPool is a centralized payment gateway that bridges multiple applications to Xendit. It provides a secure API for creating and managing payments with token-based authentication.

## Installation & Setup

### 1. Install Dependencies
```bash
composer require xendit/xendit-php laravel/sanctum
composer install
npm install
```

### 2. Configure Environment
Update your `.env` file with Xendit credentials:
```env
XENDIT_API_KEY=your_xendit_secret_key
XENDIT_PUBLIC_KEY=your_xendit_public_key
XENDIT_WEBHOOK_TOKEN=your_webhook_verification_token
XENDIT_SUCCESS_REDIRECT_URL=http://localhost/payment/success
XENDIT_FAILURE_REDIRECT_URL=http://localhost/payment/failed
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Setup Admin User
Create an admin user to manage apps:
```bash
php artisan tinker
>>> $user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);
```

## API Documentation

### Authentication

#### Admin Authentication
Use Laravel Sanctum for admin panel authentication.

#### App Authentication
Apps authenticate using Bearer tokens in the Authorization header:
```
Authorization: Bearer {your_app_access_token}
```

---

## Admin API Endpoints

### App Management

#### Create App
```http
POST /api/admin/apps
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "My E-commerce App",
  "description": "Main e-commerce platform",
  "webhook_url": "https://myapp.com/webhooks/payment"
}
```

**Response:**
```json
{
  "success": true,
  "message": "App created successfully",
  "data": {
    "id": 1,
    "name": "My E-commerce App",
    "description": "Main e-commerce platform",
    "webhook_url": "https://myapp.com/webhooks/payment",
    "access_token": "abc123...xyz789",
    "is_active": true,
    "created_at": "2024-02-05T10:00:00.000000Z"
  }
}
```

#### List Apps
```http
GET /api/admin/apps?is_active=1&search=ecommerce&per_page=15
Authorization: Bearer {admin_token}
```

#### Get App Details
```http
GET /api/admin/apps/{id}
Authorization: Bearer {admin_token}
```

#### Update App
```http
PUT /api/admin/apps/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Updated App Name",
  "is_active": false
}
```

#### Regenerate Access Token
```http
POST /api/admin/apps/{id}/regenerate-token
Authorization: Bearer {admin_token}
```

#### Deactivate App
```http
DELETE /api/admin/apps/{id}
Authorization: Bearer {admin_token}
```

### Payment Management

#### List All Payments
```http
GET /api/admin/payments?app_id=1&status=paid&search=ORDER123&per_page=15
Authorization: Bearer {admin_token}
```

#### Get Payment Details
```http
GET /api/admin/payments/{id}
Authorization: Bearer {admin_token}
```

### Dashboard

#### Get Statistics
```http
GET /api/admin/dashboard/stats
Authorization: Bearer {admin_token}
```

---

## App API Endpoints

### Payment Operations

#### Create Payment
```http
POST /api/v1/payments/create
Authorization: Bearer {app_access_token}
Content-Type: application/json

{
  "external_id": "ORDER-2024-001",
  "amount": 100000,
  "currency": "IDR",
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+628123456789",
  "description": "Order #2024-001",
  "metadata": {
    "order_id": "ORDER-2024-001",
    "items": ["Product A", "Product B"]
  },
  "success_redirect_url": "https://myapp.com/payment/success",
  "failure_redirect_url": "https://myapp.com/payment/failed"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment created successfully",
  "data": {
    "payment_id": 1,
    "external_id": "ORDER-2024-001",
    "amount": 100000,
    "currency": "IDR",
    "status": "pending",
    "invoice_url": "https://checkout.xendit.co/web/...",
    "expired_at": "2024-02-06T10:00:00.000000Z"
  }
}
```

#### Get Payment Status
```http
GET /api/v1/payments/{external_id}
Authorization: Bearer {app_access_token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "payment_id": 1,
    "external_id": "ORDER-2024-001",
    "amount": 100000,
    "currency": "IDR",
    "status": "paid",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "payment_method": "VIRTUAL_ACCOUNT",
    "paid_at": "2024-02-05T11:30:00.000000Z",
    "metadata": {
      "order_id": "ORDER-2024-001"
    }
  }
}
```

#### List Payments
```http
GET /api/v1/payments?status=paid&start_date=2024-02-01&per_page=15
Authorization: Bearer {app_access_token}
```

#### Cancel Payment
```http
POST /api/v1/payments/{external_id}/cancel
Authorization: Bearer {app_access_token}
```

---

## Webhooks

### Xendit Webhook
PayPool automatically receives webhooks from Xendit when payment status changes.

**Endpoint:** `POST /api/webhooks/xendit`

**Headers:**
- `x-callback-token`: Xendit webhook verification token

### App Webhook
When a payment status changes, PayPool forwards the webhook to your app's configured webhook URL.

**Your App Webhook Endpoint Example:**
```http
POST https://your-app.com/webhooks/payment
Content-Type: application/json

{
  "event": "payment.updated",
  "payment": {
    "external_id": "ORDER-2024-001",
    "amount": 100000,
    "currency": "IDR",
    "status": "paid",
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "payment_method": "VIRTUAL_ACCOUNT",
    "paid_at": "2024-02-05T11:30:00.000000Z",
    "metadata": {
      "order_id": "ORDER-2024-001"
    }
  },
  "xendit_data": { ... }
}
```

**Your app should return:**
```json
{
  "success": true
}
```

---

## Payment Flow

1. **Create Payment**: Your app sends a payment request to PayPool
2. **Generate Invoice**: PayPool creates an invoice in Xendit
3. **Return Payment URL**: PayPool returns the payment URL to your app
4. **Customer Pays**: Customer completes payment via Xendit
5. **Xendit Webhook**: Xendit sends webhook to PayPool
6. **Update Status**: PayPool updates payment status
7. **Forward Webhook**: PayPool forwards webhook to your app
8. **Process Update**: Your app processes the payment update

---

## Payment Statuses

- `pending`: Payment created, waiting for customer
- `paid`: Payment successfully completed
- `expired`: Payment expired (not paid within time limit)
- `failed`: Payment failed

---

## Error Handling

All API responses follow this format:

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

**HTTP Status Codes:**
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `404`: Not Found
- `422`: Validation Error
- `500`: Server Error

---

## Integration Example

### PHP Example
```php
<?php

$accessToken = 'your_app_access_token';
$apiUrl = 'http://localhost/api/v1/payments/create';

$data = [
    'external_id' => 'ORDER-' . time(),
    'amount' => 100000,
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'description' => 'Payment for Order #123',
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken,
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['success']) {
    // Redirect customer to payment page
    header('Location: ' . $result['data']['invoice_url']);
} else {
    echo 'Error: ' . $result['message'];
}
```

### JavaScript Example
```javascript
const accessToken = 'your_app_access_token';
const apiUrl = 'http://localhost/api/v1/payments/create';

const data = {
  external_id: 'ORDER-' + Date.now(),
  amount: 100000,
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  description: 'Payment for Order #123',
};

fetch(apiUrl, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${accessToken}`,
  },
  body: JSON.stringify(data),
})
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      // Redirect to payment page
      window.location.href = result.data.invoice_url;
    } else {
      console.error('Error:', result.message);
    }
  });
```

---

## Security Best Practices

1. **Store Access Tokens Securely**: Never expose tokens in client-side code
2. **Use HTTPS**: Always use HTTPS in production
3. **Validate Webhooks**: Verify webhook signatures before processing
4. **Rate Limiting**: Implement rate limiting on your endpoints
5. **Monitor Logs**: Regularly check webhook and payment logs
6. **Rotate Tokens**: Periodically regenerate access tokens

---

## Troubleshooting

### Payment Creation Failed
- Check Xendit API credentials in `.env`
- Verify minimum amount (10,000 IDR)
- Check Xendit account balance/limits

### Webhook Not Received
- Verify webhook URL is publicly accessible
- Check webhook logs in database
- Ensure webhook token is correct

### Invalid Access Token
- Verify token is active in admin panel
- Check Authorization header format
- Ensure app is not deactivated

---

## Support

For issues and questions:
- Check payment logs: `payment_logs` table
- Check webhook logs: `webhook_logs` table
- Review Laravel logs: `storage/logs/laravel.log`
