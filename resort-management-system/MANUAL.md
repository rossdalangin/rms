# LuxeResort Manager - Elite Hospitality Suite
## Professional User Manual (v1.1 Enterprise Elite)

## 1. Introduction
Welcome to **LuxeResort Manager**, the definitive hospitality solution for WordPress. Featuring a modern **Tropical Modern** UI, this plugin provides a 5-star experience for both guests and resort operators.

This manual provides detailed instructions on how to configure your resort, manage your inventory, and ensure a smooth experience for your guests. From real-time availability and demand-based "Smart Pricing" to integrated marketing with Mailchimp and HubSpot, LuxeResort Manager is built to scale with your paradise.

---

## 2. Getting Started & Onboarding
### 2.1 Installation
1. Upload the `resort-management-system` folder to `/wp-content/plugins/`.
2. Activate via the **Plugins** menu in WordPress.

### 2.2 The Executive Dashboard
Once activated, the **LuxeResort** main menu provides an **Executive Dashboard**. This is your real-time command center, showing:
- **Today's Summary**: Arrivals, Departures, and In-House guests.
- **Financial Snapshot**: Current month's revenue, Average Order Value (AOV), and Customer Lifetime Value (LTV).
- **Occupancy Gauge**: Real-time visual of your resort's capacity utilization.
- **System Activity**: A live feed of recent staff actions and bookings.

### 2.3 Onboarding Wizard
Navigate to **LuxeResort > Getting Started**.
- **The One-Click Tropical Setup**: Click "Build My Paradise" to automatically populate your site with sample luxury villas, essential services, and six pre-configured pages using professional "Luxe" templates.
- **Manual Configuration**: Follow the guided steps to configure your settings and manage your suites if you prefer to start from scratch.

---

## 3. Inventory Management
### 3.1 Accommodations (Rooms/Villas)
Manage your inventory under **LuxeResort > Accommodations**. Each accommodation represents a bookable unit.
- **Base Price**: The standard nightly rate in your base currency (Default: PHP).
- **Capacity**: Maximum number of guests allowed.
- **Amenity Management**: Tag rooms with features like "Private Pool," "WiFi," or "Ocean View."
- **iCal Synchronization (2-Way)**:
    - **Import**: Paste a URL from Airbnb/Booking.com/VRBO into the **External iCal URL** field.
    - **Export**: Copy the unique **Export URL** to your other platforms to block dates when booked on your site.
    - **Manual Sync**: Use the "Sync Now" button on the room edit screen for immediate updates.
- **Rules**: Set Min/Max stay durations per room or globally in Settings.

### 3.2 Services & Extras
Add optional perks under **LuxeResort > Services/Extras**.
- **Upselling**: These appear in Step 3 of the booking flow, encouraging guests to upgrade with spa treatments, tours, or airport transfers.

---

## 4. Pricing & Promotions
### 4.1 Dynamic Pricing & Seasonality
Manage seasonal rates under **LuxeResort > Pricing Rules**.
- **Modifiers**: Apply percentage (e.g., +20% for Christmas) or fixed (e.g., +$50 for weekends) rate changes.
- **Priority Logic**: Higher priority rules override lower ones during date overlaps.

### 4.2 Coupons & Promo Codes
Create discounts under **LuxeResort > Coupons**. Support for percentage or fixed discounts with usage limits and expiration dates.

### 4.3 Market Intelligence & Smart Pricing
Navigate to **Settings > Market Intelligence** to track competitors.
- **Competitor Tracking**: Monitor base rates of nearby resorts.
- **Market Alerts**: Receive email notifications when competitor prices drop significantly.
- **Optimization**: Use the Reports dashboard for AI-driven suggestions based on occupancy trends.

---

## 5. Payment Gateway Setup
LuxeResort supports multiple payment flows via **LuxeResort > Settings**.

### 5.1 Stripe & PayPal
Configure API keys for secure credit card and PayPal transactions. Supports "Test/Sandbox Mode" for verification.

### 5.2 WooCommerce Bridge
Enable the "WooCommerce Checkout" option to use any payment gateway supported by WooCommerce (e.g., regional banks, Crypto, specialized providers).

### 5.3 Offline Payments
Allow guests to book now and pay on arrival. Admin can manually record these payments in the dashboard.

---

## 6. Managing Reservations
### 6.1 Reservation Calendar
A visual timeline of your resort's occupancy under **LuxeResort > Calendar**.
- **Manual Blocking**: Use the sidebar on Accommodation screens to block dates for maintenance.
- **Status Indicators**: Free (White), Booked/Pending (Green), External Sync (Grey).
- **Master Export**: Get a comprehensive iCal feed of all bookings for external calendar apps.

### 6.2 Housekeeping Dashboard
A dedicated interface for operational staff to manage room status:
- **Status Tracking**: Mark rooms as "Clean," "Dirty," or "Cleaning."
- **Priority View**: Prioritize rooms based on today's departures and arrivals.
- **Staff Role**: Access is limited to users with the `resort_staff` or `administrator` roles.

### 6.3 Guest Waivers & Terms
Centrally manage signed digital liability waivers under **LuxeResort > Waivers**. Admins can configure the global waiver text in Settings.

---

## 7. Guest Engagement & Loyalty
### 7.1 The Guest Dashboard
A private portal where guests can:
- **Self Check-in/Out**: Update their status on arrival/departure dates.
- **Service Requests**: Submit in-stay requests (e.g., "Extra Towels") directly to staff.
- **History & Invoices**: View past stays and download PDF invoices.

### 7.2 Loyalty Program (Elite Tiers)
Guests earn **1 point for every $10 spent**.
- **Tiers**: Island Explorer, Silver Voyager, Gold Sanctuary, Diamond Elite.
- **Redemption**: Points can be redeemed for discounts during the booking process.

---

## 8. Shortcode Reference
- `[resort_booking]`: The primary 5-step booking engine.
- `[resort_rooms_grid]`: A gallery of accommodations with filters.
- `[resort_guest_dashboard]`: The private guest portal.
- `[resort_reviews]`: Display testimonials (Attributes: `featured="1"`, `limit="5"`).
- `[resort_lead_form]`: Build your elite guest list.
- `[resort_currency_switcher]`: Toggle between PHP, USD, EUR, and GBP.
- `[resort_gated_content]`: Hide exclusive deals behind a lead form.

---

## 9. Troubleshooting
- **Sync Issues**: Verify that the external iCal URL is publicly accessible and your server allows outbound requests.
- **Payment Failures**: Ensure your currency (e.g., USD) matches your Stripe/PayPal account capabilities.
- **Template Errors**: Ensure your theme has a `wp_footer()` call for the booking engine's JavaScript to load.

---

## 10. Developer Reference
- **Action Hooks**: `resort_booking_confirmed`, `resort_submit_review`, `resort_service_request_submitted`.
- **Filter Hooks**: `resort_loyalty_point_rate`, `resort_email_primary_color`.
- **REST API**: Fully documented endpoints under `resort/v1` for custom integrations.
