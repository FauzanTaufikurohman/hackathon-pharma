# Anteraja Pharma frontend

Next.js frontend for the Laravel API. Authentication, shipments, customer and
product management, thermal checks and simulations, courier tracking, and
reports use the live API rather than local demo data.

## Local setup

1. Start the Laravel backend and PostgreSQL database. Configure the backend
   environment and run its migrations as described in [backend/README.md](../backend/README.md).
2. Create `frontend/.env.local` with the API base URL:

   ```dotenv
   NEXT_PUBLIC_API_URL=http://localhost:8000/api
   ```

3. Install dependencies and start Next.js:

   ```bash
   npm install
   npm run dev
   ```

Open `http://localhost:3000`. Customer accounts can be registered on the login
page. Admin, checker, and courier accounts must already exist in the backend.
The frontend sends Sanctum bearer tokens and checks each account's role before
rendering its dashboard. The admin shipment form selects a manufacturer and a
distributor/pharmacy customer account; those related accounts can both view
tracking and receive the delivery PDF when their WhatsApp opt-in is enabled.
Couriers can enable browser geolocation while an in-transit shipment is open;
coordinates are sent in the background and rendered as an OpenStreetMap view.

## Validation

```bash
npm run lint
npm run build
```
