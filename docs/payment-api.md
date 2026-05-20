# Paypool Payment API Integration (for Third-Party Applications)

This document describes how to integrate your application with Paypool to create and manage payments through Midtrans.

## 1) Base URL
The live Paypool server is hosted at:

```
https://paypool.icminovasi.my.id
```

All API endpoints below are prefixed with `/api/v1`.

**Full example:** `https://paypool.icminovasi.my.id/api/v1/payments/create`

## 2) Authentication
Uses Bearer token authentication.

**Headers:**
- `Authorization: Bearer <APP_ACCESS_TOKEN>`
- `Content-Type: application/json`

The token is issued in the Paypool admin panel when creating an application.

## 3) Sandbox vs Production Environment

Paypool supports both Midtrans **sandbox** (for testing) and **production** (for live payments). The environment is configured **inside Paypool** by the Paypool administrator — not in your application and not via API parameters. Your API calls are identical regardless of environment; the environment is already determined by how your application is registered in Paypool.

### How It Works

Each application registered in Paypool has a **Midtrans Environment** setting (`sandbox` or `production`). When you create a payment using your access token, Paypool automatically routes the request to the correct Midtrans environment based on that setting.

To switch your application between sandbox and production, contact the **Paypool administrator** and request a change to your application's environment setting.

### Environment Behaviour

| Setting | Midtrans API | `invoice_url` domain | Real money? |
|---|---|---|---|
| `sandbox` | `api.sandbox.midtrans.com` | `app.sandbox.midtrans.com` | No |
| `production` | `api.midtrans.com` | `app.midtrans.com` | **Yes** |

**Tips:**
- Request two separate application registrations from the Paypool admin — one for sandbox and one for production. Each will have its own access token, keeping your test and live traffic fully separated.
- The environment used for a payment is recorded on the payment record and visible via the List Payments API (`midtrans_environment` filter).
- The `invoice_url` in the payment creation response will reflect the correct Midtrans environment automatically.

---

## 4) Redirect URLs & Webhook Handling
Redirect URLs control where users are sent after payment completion. There are two levels of configuration:

**App-Level Defaults (Admin Panel):**
Each application has default `success_redirect_url` and `failure_redirect_url` configured in the Paypool admin panel. These are used for all payments unless overridden.

**Per-Payment Override (API):**
When creating a payment, you can pass `success_redirect_url` and `failure_redirect_url` in the request body to override the app defaults for that specific payment.

**Priority (Highest to Lowest):**
1. URLs passed in the payment creation request
2. App's default URLs (from admin panel)
3. No redirect (payment completes on Midtrans payment page)

**Important:**
- Redirect URLs are for user experience only. They do NOT affect payment status updates in Paypool.
- Payment status is updated by the Midtrans webhook, which must be configured to point to your Paypool server (see below).

**Example Scenarios:**
## 5) Webhook Handling (REQUIRED for Status Updates)

Paypool updates payment status based on the Midtrans webhook/callback, not the user redirect. You must configure your Midtrans account to send payment notifications (webhook) to your Paypool server. This ensures payment status is always accurate, even if the user does not return to your app.

**Webhook Endpoint:**
```
POST https://paypool.icminovasi.my.id/webhook/midtrans
```

Configure this URL in your **Midtrans Dashboard → Settings → Configuration → Payment Notification URL** for both sandbox and production if needed.

**Supported Midtrans Statuses:**
- `settlement`, `capture`, `paid`, `settled` → marked as paid
- `expired` → marked as expired
- `failed` → marked as failed
- All other statuses are logged for reference

**Webhook Security:**
- Paypool does not require a custom signature header. Standard Midtrans HTTP Basic Auth is sufficient.
- The webhook handler supports both `external_id` and `order_id` fields to match payments.

**If you set redirect URLs to your own app:**
- Paypool will still update payment status as long as the webhook is sent to Paypool.
- Your app can use the webhook from Paypool (see section 8) to receive updates.

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

## 5a) Create Payment

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

## 6) Get Payment by external_id

## 6a) Continue Payment (Snap Redirect URL)
**GET** `/api/v1/payments/{externalId}/continue`

**Full URL example:** `https://paypool.icminovasi.my.id/api/v1/payments/ORDER-10001/continue`

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
## 6b) Get Payment Detail

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

## 7) List Payments
**GET** `/api/v1/payments`

**Query Parameters (optional):**
- `status` (pending|paid|expired|failed)
- `midtrans_environment` (sandbox|production)
- `start_date` (YYYY-MM-DD)
- `end_date` (YYYY-MM-DD)
- `per_page` (number, default 15)

## 8) Cancel Payment
**POST** `/api/v1/payments/{externalId}/cancel`

Only payments with `pending` status can be cancelled.

## 9) Webhook to Your Application (Optional, for Downstream Notification)
Paypool will send webhooks to the `webhook_url` configured for your application in the admin panel whenever a payment status changes. This allows your app to stay in sync with Paypool's status.

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

## 10) Errors
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

## 11) Recommended Flow
1. Create payment via `/payments/create`
2. Redirect user to `invoice_url` (Midtrans payment page)
3. Wait for webhook `payment.updated`
4. Check status via `/payments/{externalId}` if needed
