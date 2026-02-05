# PayPool - Setup Instructions

## Quick Start Guide

### 1. Install Dependencies

First, make sure you have MAMP running with PHP 8.2+.

```bash
cd /Applications/MAMP/htdocs/paypool

# Install Composer packages
/Applications/MAMP/bin/php/php8.2.0/bin/php /usr/local/bin/composer require xendit/xendit-php laravel/sanctum
/Applications/MAMP/bin/php/php8.2.0/bin/php /usr/local/bin/composer install

# Install NPM packages
npm install
```

### 2. Configure Database

Update your `.env` file with your database credentials (already configured):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=paypool
DB_USERNAME=root
DB_PASSWORD=root
```

Create the database:
```sql
CREATE DATABASE paypool CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `users` - Admin users
- `apps` - Registered applications
- `payments` - Payment records
- `payment_logs` - Payment event logs
- `webhook_logs` - Webhook delivery logs

### 4. Create Admin User

```bash
php artisan tinker
```

Then in the Tinker console:
```php
$user = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@paypool.test',
    'password' => bcrypt('admin123')
]);

// Create a Sanctum token for API access
$token = $user->createToken('admin-token')->plainTextToken;
echo "Admin Token: " . $token;
```

Save this token - you'll use it for admin API calls.

### 5. Publish Sanctum Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 6. Configure Xendit

Your Xendit credentials are already in `.env`:
```env
XENDIT_API_KEY=xnd_development_...
XENDIT_PUBLIC_KEY=xnd_public_development_...
XENDIT_WEBHOOK_TOKEN=yMH0EXIGCI2MyXHBLvmXYMOQELbaKY8z9rMI5ovNBrIIDueX
```

**Important:** Configure Xendit webhook URL in your Xendit dashboard:
```
Webhook URL: http://your-domain.test/api/webhooks/xendit
```

### 7. Start the Application

```bash
php artisan serve
```

Or if using MAMP, access via:
```
http://localhost:8888/paypool/public
```

---

## Testing the API

### Step 1: Create an App (as Admin)

```bash
curl -X POST http://localhost:8000/api/admin/apps \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test App",
    "description": "My test application",
    "webhook_url": "https://webhook.site/your-unique-url"
  }'
```

Save the `access_token` from the response.

### Step 2: Create a Payment (as App)

```bash
curl -X POST http://localhost:8000/api/v1/payments/create \
  -H "Authorization: Bearer YOUR_APP_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "external_id": "ORDER-001",
    "amount": 50000,
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "description": "Test Payment"
  }'
```

You'll receive a payment URL - open it to complete the payment.

### Step 3: Check Payment Status

```bash
curl -X GET http://localhost:8000/api/v1/payments/ORDER-001 \
  -H "Authorization: Bearer YOUR_APP_ACCESS_TOKEN"
```

---

## Database Structure

### Apps Table
Stores registered applications with their access tokens.

### Payments Table
Stores all payment transactions with Xendit integration data.

### Payment Logs Table
Tracks all events for each payment (created, paid, expired, etc.).

### Webhook Logs Table
Logs all webhook deliveries to your apps.

---

## Environment Variables Reference

```env
# Xendit Configuration
XENDIT_API_KEY=              # Your Xendit secret key
XENDIT_PUBLIC_KEY=           # Your Xendit public key
XENDIT_WEBHOOK_TOKEN=        # Webhook verification token
XENDIT_BASE_URL=             # Xendit API URL
XENDIT_SUCCESS_REDIRECT_URL= # Where to redirect after successful payment
XENDIT_FAILURE_REDIRECT_URL= # Where to redirect after failed payment
```

---

## Next Steps

1. **Build Admin Dashboard UI** - Create a frontend for managing apps
2. **Add Rate Limiting** - Protect API endpoints
3. **Setup Queue Workers** - For webhook forwarding
4. **Add Email Notifications** - Notify on payment events
5. **Implement API Documentation UI** - Using Swagger/OpenAPI
6. **Add Tests** - Unit and integration tests

---

## Useful Commands

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# View routes
php artisan route:list

# Run tests
php artisan test

# Check logs
tail -f storage/logs/laravel.log

# Database operations
php artisan migrate:fresh      # Reset database
php artisan db:seed            # Seed data
php artisan tinker             # Laravel REPL
```

---

## Troubleshooting

### "Class not found" errors
```bash
composer dump-autoload
```

### Migration errors
```bash
php artisan migrate:fresh
```

### Token issues
Make sure Sanctum is properly configured in `config/sanctum.php` and middleware is registered.

### Webhook not working
- Check if webhook URL is publicly accessible
- Verify `XENDIT_WEBHOOK_TOKEN` matches your Xendit dashboard
- Check webhook logs in database

---

## Security Checklist for Production

- [ ] Change all default passwords
- [ ] Use strong, unique access tokens
- [ ] Enable HTTPS
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure CORS properly
- [ ] Add rate limiting
- [ ] Setup monitoring and alerts
- [ ] Regular security updates
- [ ] Backup database regularly
- [ ] Use environment-specific Xendit keys
