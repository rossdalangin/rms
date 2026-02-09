# LuxeResort Manager - Elite Hospitality Suite
## Professional User Manual

## 1. Introduction
Welcome to **LuxeResort Manager**, the definitive hospitality solution for WordPress. Featuring a modern **Tropical Modern** UI, this plugin provides a 5-star experience for both guests and resort operators.

From real-time availability and demand-based "Smart Pricing" to integrated marketing with Mailchimp and Twilio, LuxeResort Manager is built to scale with your paradise.

---

## 2. Getting Started & Onboarding
### 2.1 Installation
1. Upload the `resort-management-system` folder to `/wp-content/plugins/`.
2. Activate via the **Plugins** menu in WordPress.

### 2.2 Onboarding Wizard
Navigate to **LuxeResort > Getting Started**.
- **The One-Click Tropical Setup**: Click "Build My Paradise" to automatically populate your site with sample luxury villas, essential services, and pre-configured pages. This is the fastest way to see the plugin in action.

---

## 3. Inventory Management
### 3.1 Accommodations (Rooms/Villas)
Manage your inventory under **LuxeResort > Accommodations**.
- **Base Price**: The standard nightly rate.
- **Capacity**: Maximum number of guests allowed.
- **Amenities**: List features like "Private Pool" or "Free WiFi".
- **iCal Sync**: Enter an external iCal URL (from Airbnb/Booking.com) to automatically block dates on your site.
- **Rules**: Use the **Settings** to define **Min/Max Stay** and **Lead Time** (e.g., must book 48 hours in advance).

### 3.2 Services & Extras
Add value to your stays under **LuxeResort > Services/Extras**.
- Create add-ons like "Airport Transfer", "Spa Treatment", or "Breakfast Buffet".
- These will appear as optional checkboxes during the guest booking flow.

---

## 4. Pricing Strategies
### 4.1 Dynamic Pricing Rules
Go to **LuxeResort > Pricing Rules** to manage seasonal rates.
- **Fixed Modifiers**: Add/subtract a specific amount (e.g., +$50 for weekends).
- **Percentage Modifiers**: Adjust rates by a percentage (e.g., +20% for Peak Season).
- **Priority**: Higher priority rules override lower ones for overlapping dates.

---

## 5. Payment Gateway Setup
LuxeResort supports real-world transactions via Stripe and PayPal. Configure these in **LuxeResort > Settings**.

### 5.1 Stripe Setup
1. Log in to your [Stripe Dashboard](https://dashboard.stripe.com).
2. Get your **Publishable Key** and **Secret Key**.
3. Paste them into the LuxeResort Settings.
4. Toggle **Test Mode** for initial setup verification.

### 5.2 PayPal Setup
1. Log in to the [PayPal Developer Portal](https://developer.paypal.com).
2. Create an App to get your **Client ID** and **Secret**.
3. Paste them into the LuxeResort Settings.
4. Toggle **Sandbox Mode** for testing.

---

## 6. Managing Reservations
### 6.1 Booking Lifecycle
- **Pending**: Guest started the flow but hasn't paid.
- **Confirmed**: Payment received and dates blocked.
- **Abandoned**: Pending booking older than 30 minutes.

### 6.2 Reservation Calendar
View a visual timeline of your resort's occupancy under **LuxeResort > Calendar**.
- **Start Date**: Select a specific day to start the view from.
- **Timeline Range**: Choose between 7, 14, or 30-day views to see short or long-term availability.
- **Status Indicators**:
    - **Free**: Room is available for booking.
    - **Booked**: Room has a confirmed or pending reservation.
    - **Sync**: Room is blocked via external iCal (e.g., Airbnb).

### 6.3 Managing Payments
Track all financial activity under **LuxeResort > Payments**.
- **Online Payments**: Stripe and PayPal transactions are logged automatically.
- **Offline Payments**: When a guest chooses "Pay at Resort", a pending record is created. Once they pay (cash/check), find the record and click **Update** to mark it as **Completed**.
- **Manual Recording**: Use the form at the top to record on-site payments that weren't part of an online flow.

### 6.4 Analytics & Reports
Monitor your performance under **LuxeResort > Reports**.
- **Revenue Stats**: Track total income from confirmed stays.
- **Occupancy**: View today's resort load.
- **Inventory Maintenance**: Use the "Clean Up" tool to release rooms held by abandoned bookings.
- **CSV Export**: Download your entire booking history for external accounting.

---

## 7. Guest Engagement
### 7.1 Communication Log
Open any individual **Booking** to find the **Guest Communication Log**. Record phone calls, emails, or special request fulfillments here to maintain a 5-star service history.

### 7.2 Guest Reviews
Guests can leave reviews via the **Guest Dashboard** after their stay.
- Moderate new reviews under **LuxeResort > Reviews**.
- Display them using the `[resort_reviews]` shortcode, featuring star ratings and tropical design.

### 7.3 Loyalty & Perks
Guests automatically earn **1 point for every $10 spent**.
- Points are displayed in the **Guest Dashboard**.
- Admin can view total points and booking counts under **LuxeResort > Guest Profiles**.

---

## 8. Shortcode Reference
| Shortcode | Description |
| :--- | :--- |
| `[resort_booking]` | The primary 5-step booking engine. |
| `[resort_rooms_grid]` | A beautiful gallery of all available accommodations. |
| `[resort_guest_dashboard]` | Private area for guests to manage their bookings and leave reviews. |
| `[resort_reviews]` | Displays the latest guest testimonials. |

---

## 9. Troubleshooting & Support
- **Calendars not syncing?** Ensure your server can make outbound requests and that your external iCal URL is public.
- **Payments failing?** Double-check your API keys and ensure you are using the correct currency code (e.g., USD, EUR).
- **Data Reset**: If you need to wipe everything, use the **Maintenance Tools** in **Settings**.

---

## 10. Advanced Enterprise Features
LuxeResort Manager provides high-level tools for optimizing your operations.

### 10.1 Revenue Optimization
Visit **LuxeResort > Reports > Revenue Optimization** for AI-driven pricing suggestions. The system analyzes your occupancy for the next 30 days and recommends rate increases during high-demand periods or promotional discounts during slow seasons.

### 10.2 Guest Retention (Loyalty & CRM)
- **Automatic Account Creation**: Every guest gets a private dashboard to manage their stay.
- **Loyalty Program**: Guests earn points on every booking, encouraging repeat visits.
- **Mailchimp Integration**: Sync your guest list to Mailchimp for seasonal newsletters and exclusive member-only offers.

### 10.3 Staff Oversight
Use the **Activity Logs** to monitor all administrative changes, from price updates to booking modifications, ensuring full accountability.

---

## 11. Developer API
Extend LuxeResort Manager with custom code.

### 11.1 PHP Hooks
- `resort_booking_confirmed`: Triggers after successful payment.
- `resort_submit_review`: Triggers when a guest submits feedback.

### 11.2 REST API Endpoints
- `GET /wp-json/resort/v1/availability`: Fetch real-time room availability.
- `GET /wp-json/resort/v1/services`: List all available extras.
- `POST /wp-json/resort/v1/validate_coupon`: Validate promotional codes.
- `GET /wp-json/resort/v1/sync/ical/{id}`: Export iCal feed.
