# Paypool Payment API Integration (for Third-Party Applications)

This document describes how to integrate your application with Paypool to create and manage payments through Midtrans.

## 1) Base URL
Use your Paypool server address, for example:
- http://localhost (local)
- https://paypool.yourdomain.com (production)

All API endpoints below are prefixed with `/api/v1`.

## 2) Authentication
Uses Bearer token authentication.

**Headers:**
- `Authorization: Bearer <APP_ACCESS_TOKEN>`
- `Content-Type: application/json`

The token is issued in the Paypool admin panel when creating an application.

## 3) Redirect URLs
Redirect URLs control where users are sent after payment completion. There are two levels of configuration:

**App-Level Defaults (Admin Panel):**
Each application has default `success_redirect_url` and `failure_redirect_url` configured in the Paypool admin panel. These are used for all payments unless overridden.

**Per-Payment Override (API):**
When creating a payment, you can pass `success_redirect_url` and `failure_redirect_url` in the request body to override the app defaults for that specific payment.

**Priority (Highest to Lowest):**
1. URLs passed in the payment creation request
2. App's default URLs (from admin panel)
3. No redirect (payment completes on Midtrans payment page)

**Example Scenarios:**

*Scenario A: Using app defaults*
- App configured with: `success_redirect_url: https://app.example.com/success`
- Payment request: No redirect URLs provided
- Result: User redirected to `https://app.example.com/success`

*Scenario B: Override for specific payment*
- App configured with: `success_redirect_url: https://app.example.com/success`
- Payment request includes: `success_redirect_url: https://app.example.com/special-flow`
- Result: User redirected to `https://app.example.com/special-flow`

*Scenario C: Different apps, different URLs*
- App A configured with: `success_redirect_url: https://appa.com/success`
- App B configured with: `success_redirect_url: https://appb.com/success`
- Result: Each app's users redirected to their own URLs

**POST** `/api/v1/payments/create`

**Request Body (JSON):**
- `external_id` (string, required, unique for your application)
- `amount` (number, required, minimum 10000)
- `currency` (string, optional, 3 characters, default `IDR`)
- `customer_name` (string, required)
- `customer_email` (string, required)
- `customer_phone` (string, optional)
- `description` (string, optional)
- `metadata` (object, optional)
- `success_redirect_url` (string, optional)
- `failure_redirect_url` (string, optional)

**Example Request:**
```json
{
  "external_id": "ORDER-10001",
  "amount": 250000,
  "currency": "IDR",
  "customer_name": "Budi",
  "customer_email": "budi@example.com",
  "customer_phone": "+628123456789",
  "description": "Order #10001",
  "metadata": {
    "order_id": 10001,
    "cart_total": 250000
  },
  "success_redirect_url": "https://app.example.com/payment/success",
  "failure_redirect_url": "https://app.example.com/payment/failed"
}
```

**Example Response (201):**
```json
{
  "success": true,
  "message": "Payment created successfully",
  "data": {
    "payment_id": 1,
    "external_id": "ORDER-10001",
    "amount": 250000,
    "currency": "IDR",
    "status": "pending",
    "invoice_url": "https://app.midtrans.com/payment-link/...",
    "expired_at": "2026-02-05T10:00:00Z"
  }
}
```

**Important:**
- If `success_redirect_url`/`failure_redirect_url` are not provided, Paypool uses the URLs configured for your application in the admin panel.
- `external_id` must be unique within your application.

## 5) Get Payment by external_id

## 5a) Continue Payment (Snap Redirect URL)
**GET** `/api/v1/payments/{externalId}/continue`

Returns the Snap payment page URL for a pending payment, so users can continue an unfinished payment.

**Example Response (200):**
```json
{
  "success": true,
  "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/xxxxxx"
}
```

**If the payment is not pending or no Snap URL is available:**
```json
{
  "success": false,
  "message": "Payment is not pending or cannot be continued"
}
```
**GET** `/api/v1/payments/{externalId}`

**Example Response (200):**
```json
{
  "success": true,
  "data": {
    "payment_id": 1,
    "external_id": "ORDER-10001",
    "amount": 250000,
    "currency": "IDR",
    "status": "paid",
    "customer_name": "Budi",
    "customer_email": "budi@example.com",
    "payment_method": "credit_card",
    "paid_at": "2026-02-05T10:05:00Z",
    "expired_at": "2026-02-05T10:00:00Z",
    "metadata": {"order_id": 10001},
    "created_at": "2026-02-05T09:55:00Z"
  }
}
```

## 6) List Payments
**GET** `/api/v1/payments`

**Query Parameters (optional):**
- `status` (pending|paid|expired|failed)
- `start_date` (YYYY-MM-DD)
- `end_date` (YYYY-MM-DD)
- `per_page` (number, default 15)

## 7) Cancel Payment
**POST** `/api/v1/payments/{externalId}/cancel`

Only payments with `pending` status can be cancelled.

## 8) Webhook to Your Application
Paypool will send webhooks to the `webhook_url` configured for your application in the admin panel.

**Payload Format:**
```json
{
  "event": "payment.updated",
  "payment": {
    "external_id": "ORDER-10001",
    "amount": 250000,
    "currency": "IDR",
    "status": "paid",
    "customer_name": "Budi",
    "customer_email": "budi@example.com",
    "payment_method": "credit_card",
    "paid_at": "2026-02-05T10:05:00Z",
    "metadata": {"order_id": 10001}
  },
  "midtrans_data": {"...": "raw midtrans payload"}
}
```

**Recommendations:**
- Return HTTP 200 as quickly as possible.
- Validate using `external_id` and status.
- Handle duplicate webhooks idempotently.

## 9) Errors
**401** — Invalid token

**422** — Validation errors

**500** — Internal Paypool error

**Example Error (422):**
```json
{
  "success": false,
  "errors": {
    "external_id": ["The external id field is required."]
  }
}
```

## 10) Recommended Flow
1. Create payment via `/payments/create`
2. Redirect user to `invoice_url` (Midtrans payment page)
3. Wait for webhook `payment.updated`
4. Check status via `/payments/{externalId}` if needed
