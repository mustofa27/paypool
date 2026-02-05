# Paypool Payment API интеграция (для сторонних приложений)

Этот документ описывает, как интегрировать ваше приложение с Paypool для создания и управления платежами через Xendit.

## 1) Базовый URL
Используйте адрес вашего Paypool сервера, например:
- http://localhost (локально)
- https://paypool.yourdomain.com (прод)

Все API-эндпоинты ниже находятся по префиксу `/api/v1`.

## 2) Аутентификация
Используется Bearer токен приложения.

**Заголовки:**
- `Authorization: Bearer <APP_ACCESS_TOKEN>`
- `Content-Type: application/json`

Токен выдается в админ-панели Paypool при создании приложения.

## 3) Создание платежа
**POST** `/api/v1/payments/create`

**Тело запроса (JSON):**
- `external_id` (string, обязательный, уникальный для вашего приложения)
- `amount` (number, обязательный, минимум 10000)
- `currency` (string, опционально, 3 символа, по умолчанию `IDR`)
- `customer_name` (string, обязательный)
- `customer_email` (string, обязательный)
- `customer_phone` (string, опционально)
- `description` (string, опционально)
- `metadata` (object, опционально)
- `success_redirect_url` (string, опционально)
- `failure_redirect_url` (string, опционально)

**Пример запроса:**
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

**Пример ответа (201):**
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
    "invoice_url": "https://checkout.xendit.co/...",
    "expired_at": "2026-02-05T10:00:00Z"
  }
}
```

**Важно:**
- Если `success_redirect_url`/`failure_redirect_url` не переданы, Paypool использует URLs, заданные для вашего приложения в админ-панели.
- `external_id` должен быть уникальным в рамках вашего приложения.

## 4) Получить платеж по external_id
**GET** `/api/v1/payments/{externalId}`

**Пример ответа (200):**
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
    "payment_method": "EWALLET",
    "paid_at": "2026-02-05T10:05:00Z",
    "expired_at": "2026-02-05T10:00:00Z",
    "metadata": {"order_id": 10001},
    "created_at": "2026-02-05T09:55:00Z"
  }
}
```

## 5) Список платежей
**GET** `/api/v1/payments`

**Query параметры (опционально):**
- `status` (pending|paid|expired|failed)
- `start_date` (YYYY-MM-DD)
- `end_date` (YYYY-MM-DD)
- `per_page` (число, по умолчанию 15)

## 6) Отмена платежа
**POST** `/api/v1/payments/{externalId}/cancel`

Отменить можно только платеж со статусом `pending`.

## 7) Webhook в ваше приложение
Paypool будет отправлять вебхуки в `webhook_url`, заданный для вашего приложения в админ-панели.

**Формат payload:**
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
    "payment_method": "EWALLET",
    "paid_at": "2026-02-05T10:05:00Z",
    "metadata": {"order_id": 10001}
  },
  "xendit_data": {"...": "raw xendit payload"}
}
```

**Рекомендации:**
- Верните HTTP 200 как можно быстрее.
- Валидацию можно делать по `external_id` и статусу.
- Обрабатывайте повторные вебхуки идемпотентно.

## 8) Ошибки
**401** — неверный токен

**422** — ошибки валидации

**500** — внутренняя ошибка Paypool

**Пример ошибки (422):**
```json
{
  "success": false,
  "errors": {
    "external_id": ["The external id field is required."]
  }
}
```

## 9) Рекомендованный flow
1. Создайте платеж через `/payments/create`
2. Перенаправьте пользователя на `invoice_url`
3. Дождитесь webhook `payment.updated`
4. Проверьте статус через `/payments/{externalId}` при необходимости
