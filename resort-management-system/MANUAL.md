# LuxeResort Manager - Professional User Manual

## 1. Introduction
Welcome to **LuxeResort Manager**, the definitive hospitality solution for WordPress. This plugin is designed to handle everything from real-time availability and dynamic pricing to secure online payments and guest relationship management.

---

## 2. Installation & Quick Start
### 2.1 Installation
1. Upload the `resort-management-system` folder to `/wp-content/plugins/`.
2. Activate via the **Plugins** menu in WordPress.

### 2.2 Onboarding Wizard
Navigate to **LuxeResort > Getting Started**.
- **One-Click Demo Setup**: Click "Install Demo Content" to automatically populate your site with sample accommodations, services, and create all necessary pages (Booking, Gallery, Dashboard).

---

## 3. Inventory Management
### 3.1 Accommodations (Rooms/Villas)
Manage your inventory under **LuxeResort > Accommodations**.
- **Base Price**: The standard nightly rate.
- **Capacity**: Maximum number of guests allowed.
- **Amenities**: List features like "Private Pool" or "Free WiFi".
- **iCal Sync**: Enter an external iCal URL (from Airbnb/Booking.com) to automatically block dates on your site.

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

### 6.3 Analytics & Reports
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
- Display them using the `[resort_reviews]` shortcode.

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
