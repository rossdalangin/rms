# Resort Management System - Product Specification

## 1. Overview
**LuxeResort Manager** is a premium WordPress plugin designed for 5-star resorts and property operators. It provides a seamless booking experience for guests and a powerful management dashboard for administrators.

## 2. Core Architecture
### 2.1 Database Schema
We use a combination of Custom Post Types (CPT) for content and Custom Tables for performance-critical data.

#### Custom Post Types:
- `accommodation`: Represents room types, villas, or units.
  - Meta: `capacity`, `base_price`, `amenities`, `gallery`.
- `booking`: Represents a reservation.
  - Meta: `checkin`, `checkout`, `guest_id`, `total_price`, `status`.

#### Custom Tables:
- `wp_resort_availability`:
  - `id`, `room_id`, `date`, `status` (available, booked, blocked), `booking_id`.
- `wp_resort_pricing`:
  - `id`, `room_id`, `start_date`, `end_date`, `price_modifier`, `priority`.
- `wp_resort_payments`:
  - `id`, `booking_id`, `transaction_id`, `amount`, `method`, `status`, `created_at`.

### 2.2 API Flows
- **Availability Search**: `GET /wp-json/resort/v1/availability?checkin=...&checkout=...&guests=...`
- **Booking Creation**: `POST /wp-json/resort/v1/book` (Payload: room_id, dates, guest_info, payment_method)
- **iCal Export**: `GET /wp-json/resort/v1/sync/ical/{room_id}`

## 3. UI/UX Design (Sketches)
### 3.1 Guest Booking Flow
1. **Search Bar**: Horizontal bar with Check-in, Check-out, Guests, and "Check Availability" button.
2. **Results Grid**: Cards showing room image, title, capacity, price, and "Select" button.
3. **Checkout Page**: Summary on the right, Guest Info form on the left, Payment options below.
4. **Confirmation**: Thank you message with booking ID and Add to Calendar (iCal) button.

### 3.2 Admin Dashboard
- **Main Dashboard**: Widgets for "Today's Arrivals", "Today's Departures", and "Monthly Revenue".
- **Calendar View**: A multi-row timeline view where Y-axis is Rooms and X-axis is Dates.

## 4. User Journeys
### 4.1 Guest
- Lands on homepage -> Uses search widget -> Selects Luxury Villa -> Fills info -> Pays via Stripe -> Receives email confirmation.
### 4.2 Admin
- Logs in -> Sees occupancy at 80% -> Manually blocks a room for maintenance -> Checks revenue report for the week.

## 5. Settings Screens
- **General**: Resort info, Currency, Timezone.
- **Rooms**: Manage room types and tiers.
- **Payments**: Stripe/PayPal keys, Deposit % settings.
- **Notifications**: Edit Email/SMS templates.
