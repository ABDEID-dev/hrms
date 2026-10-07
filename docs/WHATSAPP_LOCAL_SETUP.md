# WhatsApp Local Setup

This project supports both a local WAHA-compatible service and hosted WSAPI.

## Project Settings

Set these values in `.env` for WAHA:

```env
WHATSAPP_DRIVER=waha
WHATSAPP_BASE_URL=http://localhost:3000
WHATSAPP_SESSION=default
```

Set these values in `.env` for WSAPI:

```env
WHATSAPP_DRIVER=wsapi
WHATSAPP_BASE_URL=https://api.wsapi.chat
WHATSAPP_API_KEY=your_api_key
WHATSAPP_INSTANCE_ID=your_instance_id
```

## How It Works

For WAHA, the Laravel app sends requests to:

- `POST /api/sendText`
- `GET /api/contacts/check-exists`

using the configured session name.

For WSAPI, the Laravel app sends requests to:

- `POST /messages/text`
- `POST /messages/document`

using:

- `x-api-key`
- `x-instance-id`

## What You Need

WAHA needs:

1. A running WAHA-compatible service on port `3000` or another URL.
2. A work WhatsApp number linked to that service.
3. An active connected session, usually by scanning a QR code.

WSAPI needs:

1. An active WSAPI subscription.
2. Your API key.
3. Your instance ID.
4. A connected WhatsApp number in that instance.

## Recommended Flow

WAHA flow:

1. Start the WhatsApp service.
2. Create or connect session `default`.
3. Scan the QR code using the work phone number.
4. Keep that session connected.
5. Test from the HRMS messages page.

WSAPI flow:

1. Connect your number in the WSAPI dashboard.
2. Copy your API key and instance ID into `.env`.
3. Run `php artisan optimize:clear`.
4. Test from the HRMS messages page.

## Notes

- Messages are sent from the connected WhatsApp work number.
- Employee numbers are recipients only.
- If you change the session name in the WhatsApp service, update `WHATSAPP_SESSION` too.
