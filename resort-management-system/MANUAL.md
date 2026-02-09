# LuxeResort Manager - User Manual

## 1. Installation
1. Upload the `resort-management-system` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## 2. Getting Started
Navigate to the **LuxeResort** menu in your WordPress dashboard. This is your central hub for all resort management tasks.

### 2.1 Configuration
Go to **LuxeResort > Settings** to configure:
- **Resort Name & Currency**: Basic identity and financial settings.
- **Stripe & PayPal**: Enter your API keys to enable real transactions. You can toggle between Test and Live modes.

### 2.2 Quick Setup (Sample Data)
In the **Settings** page, use the **Maintenance Tools** to:
- **Install Sample Data**: Populates rooms and services with placeholder content.
- **Create Default Pages**: Automatically generates the frontend pages (Booking, Gallery, etc.).

## 3. Inventory & Pricing
- **Accommodations**: Manage your rooms and villas. Set capacity and base price in the details box.
- **Services/Extras**: Add add-on services like Spa, Airport Transfer, or Meal Plans.
- **Pricing Rules**: Create dynamic modifiers (seasonal, weekend) to automatically adjust rates.

## 4. Reservations & Management
- **Bookings**: Central list of all guest reservations.
- **Calendar**: Visual timeline of occupancy.
- **Reports**: Analytics dashboard for revenue and occupancy rates.
- **Coupons**: Manage promotional discount codes.
- **Communication Log**: Open any booking to view or add notes regarding guest follow-ups.
- **Sync External Calendars**: Use the 'Sync' button on the main dashboard to import bookings from Airbnb/VRBO.

## 5. Shortcodes
Use these shortcodes on any page or post:

- `[resort_booking]`: The main 4-step booking engine.
- `[resort_rooms_grid]`: Displays a beautiful grid of all your rooms with 'View Details' links.
- `[resort_guest_dashboard]`: A private area for logged-in guests to see their booking history and status.
- `[resort_reviews]`: Displays a list of the latest guest reviews.

## 6. Maintenance
If you want to start over, use the **Reset All Data** button in the settings. **Warning:** This will permanently delete all rooms and bookings.
