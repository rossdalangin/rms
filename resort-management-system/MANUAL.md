# LuxeResort Manager - Elite Hospitality Suite
## Professional User Manual

## 1. Introduction
Welcome to **LuxeResort Manager**, the definitive hospitality solution for WordPress. Featuring a modern **Tropical Modern** UI, this plugin provides a 5-star experience for both guests and resort operators.

This manual provides detailed instructions on how to configure your resort, manage your inventory, and ensure a smooth experience for your guests.

From real-time availability and demand-based "Smart Pricing" to integrated marketing with Mailchimp and Twilio, LuxeResort Manager is built to scale with your paradise.

---

## 2. Getting Started & Onboarding
### 2.1 Installation
1. Upload the `resort-management-system` folder to `/wp-content/plugins/`.
2. Activate via the **Plugins** menu in WordPress.

### 2.2 The Executive Dashboard
Once activated, the **LuxeResort** main menu provides an **Executive Dashboard**. This is your real-time command center, showing:
- **Today's Summary**: Arrivals, Departures, and In-House guests.
- **Financial Snapshot**: Current month's revenue at a glance.
- **Occupancy Gauge**: Real-time visual of your resort's capacity utilization.
- **System Activity**: A live feed of recent staff actions and bookings.

### 2.3 Onboarding Wizard
Navigate to **LuxeResort > Getting Started**.
- **The One-Click Tropical Setup**: Click "Build My Paradise" to automatically populate your site with sample luxury villas, essential services, and six pre-configured pages using professional "Luxe" templates. This is the fastest way to see the plugin in action.
- **Manual Configuration**: Follow the numbered steps to configure your settings and manage your suites if you prefer to start from scratch.

---

## 3. Inventory Management
### 3.1 Accommodations (Rooms/Villas)
Manage your inventory under **LuxeResort > Accommodations**. Each accommodation represents a bookable unit.

- **Base Price**: The standard nightly rate.
- **Capacity**: Maximum number of guests allowed.
- **Amenities**: Features like "WiFi" or "Ocean View".
- **iCal Synchronization (2-Way)**:
    - **Import**: Paste a URL from Airbnb/Booking.com into the **External iCal URL** field to block those dates locally.
    - **Manual Sync**: Click the **"Sync Now"** button on the edit page for instant updates.
    - **Export**: Copy the unique **Export URL** found on the room's edit page and paste it into your other booking platforms.
- **Rules (Min/Max Stay)**: Global duration rules are set in **Settings**.

### 3.2 Services & Extras
Add optional perks under **LuxeResort > Services/Extras**.

- **Examples**: "Airport Transfer ($50)", "Daily Breakfast ($30)", "Spa Treatment ($120)".
- **Guest Experience**: These appear as simple checkboxes in Step 3 of the booking flow, making it easy for guests to upgrade their stay.

---

## 4. Pricing & Promotions
### 4.1 Dynamic Pricing (Seasonality)
Manage seasonal rates under **LuxeResort > Pricing Rules**.

- **Modifier Types**:
    - **Percentage**: Good for seasonal peaks (e.g., `+20%` for Christmas).
    - **Fixed**: Good for flat surcharges (e.g., `+$50` for weekends).
- **Priority Logic**: If multiple rules overlap, the system chooses the one with the highest priority.
    - *Example*: A "Holiday" rule (Priority 10) will override a standard "Weekend" rule (Priority 0).

### 4.2 Coupons & Promo Codes
Create discounts under **LuxeResort > Coupons**.

- **Example**: Create code `ISLAND2024` for a **15% discount** to celebrate your resort's anniversary.
- **Usage**: Guests enter the code at the final payment step to see their savings instantly.

### 4.3 Elementor & Gutenberg Integration
LuxeResort Manager is fully compatible with modern page builders.
- **Gutenberg**: Find the "Booking Engine", "Accommodations Grid", and "Guest Reviews" blocks in the editor.
- **Elementor**: Three custom widgets are available under the "General" category for easy drag-and-drop design.

### 4.4 Custom Page Templates
LuxeResort provides two specialized page templates for a more immersive experience:
- **LuxeResort Full Width Canvas**: Ideal for the main booking page, providing a distraction-free, 1200px wide centered container.
- **LuxeResort Dashboard Template**: A themed layout for the Guest Portal, featuring a primary-color header and a floating content card.
*To apply these, select them under 'Page Attributes' > 'Template' when editing any WordPress page.*

### 4.4 Loyalty Points Redemption
Guests can redeem their accumulated points for real discounts during Step 5 (Payment).
- **Rate**: 10 points = 1 PHP discount.
- **Usage**: Logged-in guests will see a "Redeem Points" section where they can enter the amount of points to use.

### 4.4 Room Packages (Bundles)
Create enticing bundles under **LuxeResort > Packages**.
- **Definition**: A package includes a base room and pre-selected services (like breakfast or spa) for a single daily price.
- **Display**: These appear prominently in the search results to encourage higher-value bookings.

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

### 5.3 Managing Enabled Methods
In **LuxeResort > Settings**, you can enable or disable specific payment gateways (Stripe, PayPal, or Offline).
- **Auto-Skip Selection**: If you only enable **Offline Payment**, the guest will not be asked to choose a method; the system will automatically proceed with an offline reservation for a smoother checkout experience.
- **Multi-Gateway**: If multiple methods are enabled, the guest will see a selection list at the final step.

---

## 6. Managing Reservations
### 6.1 Booking Lifecycle
- **Pending**: A guest has started a booking but hasn't completed payment. The room is temporarily held.
- **Confirmed**: Payment has been verified (or an offline booking recorded). Dates are blocked in the calendar.
- **Abandoned**: A "Pending" booking that has not been completed within 30 minutes. These are automatically cleaned up to release inventory.

### 6.2 Reservation Calendar
View a visual timeline of your resort's occupancy under **LuxeResort > Calendar**.
- **Manual Blocking**: To block a room for maintenance, go to the individual **Accommodation** edit screen and use the **Maintenance & Manual Date Blocking** sidebar box. These dates will appear greyed out in the calendar.
- **Visual Overview**: The calendar shows rooms on the vertical axis and dates on the horizontal.
- **Master Export**: Click "Export Master iCal" to get a feed of all bookings for your personal calendar or a master channel manager.
- **Controls**: Adjust the **Start Date** and **Timeline Range** (7, 14, or 30 days) to navigate through your schedule.
- **Status Indicators**:
    - **Free**: Room is available (White).
    - **Booked**: Confirmed or pending booking (Vibrant Green).
    - **Sync**: Blocked by an external calendar sync (Slate Grey).

### 6.3 Guest Waivers
All signed digital liability waivers are centrally managed under **LuxeResort > Waivers**. You can filter by date and view the associated booking for each waiver.

### 6.4 Managing Payments
Track all financial activity under **LuxeResort > Payments**.
- **Online Payments**: Stripe and PayPal transactions are logged automatically.
- **Offline Payments**: When a guest chooses "Pay at Resort", a pending record is created. Once they pay (cash/check), find the record and click **Update** to mark it as **Completed**.
- **Manual Recording**: Use the form at the top to record on-site payments that weren't part of an online flow.

### 6.4 Analytics & Reports
Monitor your performance under **LuxeResort > Reports**.
- **Visual Trends**: View a real-time revenue chart showing your income over the last 7 days.
- **Revenue Stats**: High-level overview of your total earnings and booking counts.
- **Occupancy Rate**: Real-time metric showing how much of your resort is currently occupied.
- **Inventory Maintenance**:
    - **Send Reminders**: Manually trigger reminder emails to guests with "Pending" bookings.
    - **Clean Up**: Release rooms held by abandoned bookings older than 30 minutes.
- **Date Filtering**: Filter your revenue stats and booking list by check-in date range for detailed periodic analysis.
- **CSV Export**: Generate a spreadsheet of all bookings for your financial records.
- **Revenue Optimization**: View "Smart Pricing" suggestions based on your upcoming occupancy trends.

---

## 7. Guest Engagement
### 7.1 Communication Log
Open any individual **Booking** to find the **Guest Communication Log**. Record phone calls, emails, or special request fulfillments here to maintain a 5-star service history.

### 7.2 Guest Reviews
Guests can leave reviews via the **Guest Dashboard** after their stay.
- Moderate new reviews under **LuxeResort > Reviews**.
- Display them using the `[resort_reviews]` shortcode, featuring star ratings and tropical design.

### 7.3 Paying the Balance (Self-Service)
Guests who choose "Pay at Resort" (Offline) can later choose to pay their balance online via Stripe or PayPal through their **Guest Dashboard**.
- Once the payment is completed online, the booking status automatically updates to "Fully Paid" in the admin.

### 7.4 Digital Self Check-in & Out
On the day of arrival, guests will see a **Self Check-in** button in their dashboard.
- On the day of departure, they will see a **Self Check-out** button.
- Clicking these marks their status appropriately and alerts the front desk staff via the administrative logs for key collection/room cleaning.

### 7.5 In-Stay Service Requests
During their stay, guests can submit requests (e.g., "Fresh Towels", "Room Cleaning") directly from their dashboard.
- These requests are instantly logged in the **Guest Communication Log** for that specific booking for staff fulfillment.
- **Admin Notifications**: Staff will receive an email notification for every new service request to ensure 5-star responsiveness.

### 7.6 Progressive Web App (PWA)
Guests can install LuxeResort as a standalone app on their mobile devices for offline access to their dashboard and faster service requests.

### 7.7 Loyalty & Perks
Guests automatically earn **1 point for every $10 spent**.
- **Loyalty Tiers**: As guests spend more, they unlock higher tiers:
    - **Island Explorer**: Base Tier.
    - **Silver Voyager**: Spend > ₱10,000.
    - **Gold Sanctuary Member**: Spend > ₱50,000.
    - **Diamond Elite**: Spend > ₱200,000.
- Points are displayed in the **Guest Dashboard**.
- Admin can view total points and booking counts under **LuxeResort > Guest Profiles**.

---

## 8. Shortcode Reference
| Shortcode | Description |
| :--- | :--- |
| `[resort_booking]` | The primary 5-step booking engine. |
| `[resort_rooms_grid]` | A beautiful gallery of all available accommodations. |
| `[resort_guest_dashboard]` | Private area for guests to manage their bookings and leave reviews. |
| `[resort_reviews]` | Displays the latest guest testimonials. (Attributes: `featured="1"`, `limit="5"`) |
| `[resort_lead_form]` | A sleek lead capture form to build your elite guest list. |
| `[resort_service_booking]` | A standalone booking form for guests who only want to book a spa or excursion without a room. |
| `[resort_gated_content]` | Hides exclusive content (like a 'Secret Package') until the guest submits a lead form. |
| `[resort_currency_switcher]` | Displays a dropdown for guests to view prices in PHP, USD, EUR, or GBP. |
| `[resort_room_calendar]` | Shows a 30-day availability calendar for a specific room (Attribute: `id`). |

---

## 9. Marketing & Lead Generation
### 9.1 Gated Lead Content
The `[resort_gated_content]` shortcode is a powerful tool for lead generation. It allows you to offer "Secret" deals or exclusive guides that are only visible to guests who join your list.
- **Usage**: `[resort_gated_content title="Secret 50% Villa Deal" desc="Join our list to see the promo code."] Promo Code: PARADISE50 [/resort_gated_content]`
- **How it works**: When a guest submits the lead form inside the gated block, the page reloads, and the content is revealed. The guest's status is saved in a cookie for 1 year.

## 10. Reporting & Exports
### 10.1 Print-Ready Reports
Every analytics dashboard and revenue report is optimized for high-quality printing or saving as a PDF.
- **How to use**: Simply click the **"Print / Export to PDF"** button found at the bottom of the **Revenue Reports** page.
- **Optimized View**: The system automatically hides sidebars, admin menus, and buttons to ensure a professional, clean document suitable for executive review.

## 11. Operational Workflows
Understanding how the system manages the "behind-the-scenes" logic.

### 9.1 Payment & Booking Association
The system maintains a direct link between transactions and reservations:
- **Automatic Linking**: Every payment initiated through the booking engine carries a `booking_id`. When a payment is successfully verified, the record in the **Payments** table is automatically linked to the correct reservation.
- **Manual Reconciliation**: In the **Payments** dashboard, you can view the Room and Guest associated with every transaction ID.

### 9.2 Booking Management (Admin)
To view and manage a reservation:
- **Modification Requests**: If a guest uses the "Modify Stay" button in their dashboard, their request will appear automatically in the **Guest Communication Log** for that booking.
1. Go to **LuxeResort > Bookings**.
2. Click on a specific booking to edit.
3. **Reservation Information**: See the complete breakdown of the stay, guest preferences (meal, special requests), and total price.
4. **Communication Log**: Record notes about guest interactions or special fulfillments.

### 9.3 Loyalty Points Logic
Guests earn points automatically to encourage repeat business:
- **Calculation**: Guests receive **1 Loyalty Point for every $10** spent on confirmed bookings (e.g., a $450 stay earns 45 points).
- **Assignment**: Points are credited to the guest's profile immediately after a payment is verified as "Completed".
- **Tracking**: Admin can monitor total points per guest under **LuxeResort > Guest Profiles**.

### 11.6 Guest Self-Service Cancellations
Guests can request a cancellation directly from their **Guest Dashboard**.
- **Admin Approval**: Cancellation requests appear in the booking's communication log.
- **Approval Flow**: Admin can click **"Cancel Booking"** from the Bookings list to officially release the inventory and update the status.

---

## 12. Troubleshooting & Support
- **Calendars not syncing?** Ensure your server can make outbound requests and that your external iCal URL is public.
- **Payments failing?** Double-check your API keys and ensure you are using the correct currency code (e.g., USD, EUR).
- **Data Reset**: If you need to wipe everything, use the **Maintenance Tools** in **Settings**.

---

## 13. Advanced Enterprise Features
LuxeResort Manager provides high-level tools for optimizing your operations.

### 11.1 Multi-Currency Support
LuxeResort supports viewing prices in multiple currencies (PHP, USD, EUR, GBP).
- Use the `[resort_currency_switcher]` shortcode anywhere on your site to allow guests to toggle their preferred currency.
- **Conversion**: The system uses a built-in elite conversion engine to estimate prices based on the resort's base currency (Default: PHP).

### 11.2 Revenue Optimization
Visit **LuxeResort > Reports > Revenue Optimization** for AI-driven pricing suggestions. The system analyzes your occupancy for the next 30 days and recommends rate increases during high-demand periods or promotional discounts during slow seasons.

#### 11.1.1 Competitor Intelligence
Configure your main competitors under **LuxeResort > Settings > Market Intelligence**.
- Enter the names and average nightly rates of nearby resorts.
- The system will calculate the percentage difference between your rates and theirs, helping you stay competitive.
- **Market Alerts**: If a competitor's price drops significantly below your own, the system will send an email alert to the administrator.

### 11.2 Guest Retention (Loyalty & CRM)
- **Automatic Account Creation**: Every guest gets a private dashboard to manage their stay.
- **Loyalty Program**: Guests earn points on every booking, encouraging repeat visits.
- **Mailchimp Integration**: Sync your guest list to Mailchimp for seasonal newsletters and exclusive member-only offers.

### 11.3 Staff Oversight & Operations
- **Activity Logs**: Monitor all administrative changes, from price updates to booking modifications. You can export these logs to CSV for audit purposes.
- **Housekeeping Dashboard**: A dedicated interface for operational staff to track room cleaning status (Clean, Dirty, Cleaning) and prioritize rooms based on today's check-outs. Access this under **LuxeResort > Housekeeping**.

### 11.4 CRM & Attribution (Marketing Intelligence)
- **Multi-CRM Integration**: Automatically sync guest contacts to HubSpot and Zoho CRM for advanced workflows.
- **Marketing Attribution**: Track `utm_source`, `utm_medium`, and `utm_campaign` for every booking. View these details in individual booking screens or export them via the **Revenue Reports** CSV for ROI analysis.
- **Outgoing Webhooks**: Connect LuxeResort Manager to Zapier, Make, or custom endpoints to trigger external automations upon booking confirmation.

### 11.5 Operational Automation
- **Concierge Emails**: The system automatically sends Pre-Arrival emails (2 days before check-in) and Post-Departure feedback requests (1 day after check-out).
- **Business Intelligence**: The Reports dashboard now provides real-time metrics for Customer Lifetime Value (LTV) and Average Order Value (AOV), alongside an Elite Guests leader board.

---

## 14. Developer API & Advanced Extensibility
LuxeResort Manager is built with developers in mind. Use the following hooks to customize the behavior.

### 14.1 Action Hooks
| Hook | Description | Parameters |
| :--- | :--- | :--- |
| `resort_booking_confirmed` | Fires when a payment is verified. | `$booking_id` |
| `resort_submit_review` | Fires when a guest submitted a review. | `$review_id`, `$booking_id` |
| `resort_service_request_submitted` | Fires on in-stay service requests. | `$booking_id`, `$details` |
| `resort_daily_sync` | Daily background task for iCal/concierge. | None |
| `resort_cleanup_abandoned` | Hourly task to release inventory. | None |

### 14.2 Filter Hooks
| Hook | Description | Default |
| :--- | :--- | :--- |
| `resort_loyalty_point_rate` | Change points earned per currency unit. | `10` |
| `resort_invoice_logo_height` | Customize logo size on invoices. | `60px` |
| `resort_email_primary_color` | Change brand color in emails. | `#008080` |

### 14.3 REST API Endpoints
All endpoints are under the `resort/v1` namespace.
- `GET /availability`: Fetch room/package availability.
- `GET /services`: List available extras.
- `POST /guest/requests`: Submit in-stay requests (requires auth).
- `GET /guest/history`: View logged-in guest stay history.

---

## 15. The WooCommerce Bridge
For resorts requiring specialized payment gateways (e.g., local banks, Crypto, or regional providers), LuxeResort Manager can bridge into **WooCommerce**.
- **Setup**: Enable "WooCommerce Checkout" in Settings.
- **Workflow**: The plugin creates a virtual product and order in WooCommerce. The guest completes payment via the WooCommerce checkout page.
- **Auto-Sync**: Once the WC order is marked as "Completed" or "Processing", LuxeResort automatically confirms the reservation and blocks the calendar.
