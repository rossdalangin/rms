# 📋 Standard Operating Procedures (SOP): LuxeResort Manager
**"Operational Excellence for 5-Star Hospitality"**

This document defines the roles, responsibilities, and workflows for every user level within the LuxeResort ecosystem.

---

## 🏛️ LEVEL 1: THE ADMINISTRATOR (Owner/Manager)
**Focus:** Strategy, Finance, and System Integrity.

### 📅 Daily Tasks (Morning Review)
1.  **Revenue Check:** Navigate to **LuxeResort > Reports**. Review the revenue for the last 24 hours.
2.  **Occupancy Audit:** Check the **Calendar** for any last-minute manual bookings or channel sync issues.
3.  **Lead Follow-up:** Check **LuxeResort > Lead Captures**. Ensure high-value inquiries are added to the CRM.

### 🔄 Weekly Tasks
1.  **iCal Health Check:** Go to the Accommodations list. Verify the "Sync Status" for all rooms. If a sync failed, click "Sync Now" manually.
2.  **Coupon Management:** Deactivate expired promo codes and create new ones for upcoming weekend "Secret Deals."
3.  **Pricing Optimization:** Review the **Revenue Optimization** report. If occupancy is > 80% for next month, consider increasing base rates by 10%.

### 🛠️ System Maintenance
-   **Security:** Ensure Stripe/PayPal API keys are valid and "Test Mode" is OFF for live operations.
-   **Data Export:** Every month, export **Staff Activity Logs** and **Revenue Reports** to CSV for accounting records.

---

## 🛎️ LEVEL 2: RESORT STAFF (Front Desk/Housekeeping)
**User Role:** `resort_staff`
**Focus:** Guest Flow, Service Fulfillment, and Room Readiness.

### 🌅 Morning Routine (The Shift Start)
1.  **Arrivals List:** Check **LuxeResort > Housekeeping**. Identify which rooms are checking out today and which new guests are arriving.
2.  **Room Priority:** Prioritize cleaning for rooms where guests have requested "Early Check-in."

### 🧼 Operational Flow (Housekeeping)
1.  **Room Status Update:** As soon as a guest leaves, mark the room as **'Cleaning'** in the Housekeeping Dashboard.
2.  **The Checklist:** Follow the room-specific checklist (e.g., "Replenish Champagne", "Change Linens").
3.  **Ready for Arrival:** Once complete, mark the room as **'Clean'**. This instantly notifies the front desk.

### 📞 Guest Interaction (Front Desk)
1.  **In-Stay Requests:** Monitor the **Guest Communication Log** for incoming service requests (e.g., "Fresh Towels").
2.  **Assignment:** Assign the request to the available staff member and mark it as "Resolved" once fulfilled.
3.  **Manual Check-in:** If a guest arrives in person, navigate to **Bookings**, find their ID, and update status to **'Checked In'**.

---

## 👤 LEVEL 3: THE GUEST (Self-Service)
**Focus:** Convenience and Seamless Experience.

### 🏠 Pre-Arrival
1.  **Confirmation:** Review the automated confirmation email.
2.  **Personal Portal:** Access the **Guest Dashboard** to review stay details and download the digital invoice.

### 🏖️ During Stay
1.  **Self-Service:** Use the dashboard to perform **Self Check-in** on arrival day.
2.  **Requests:** Instead of calling the front desk, submit "Service Requests" (e.g., "More Coffee Pods") directly through the dashboard.
3.  **Balance Payment:** If the stay was booked as "Pay at Resort," the guest can log in and pay their remaining balance via Stripe/PayPal at any time.

### 🌅 Post-Departure
1.  **Self Check-out:** Click the **Check-out** button on the dashboard to notify staff for room cleaning.
2.  **Review:** Submit a star-rated review via the dashboard to earn bonus **Loyalty Points**.

---

## 🛑 EMERGENCY PROCEDURES
-   **Double Booking:** In the rare event of a sync lag, the **Administrator** must immediately move one guest to a higher-tier room (Upgrade) or contact the external channel (e.g., Airbnb) to re-accommodate.
-   **Payment Failure:** If an online payment fails, the **Front Desk** should contact the guest to confirm an alternative "Offline" payment method or provide a secure payment link.
