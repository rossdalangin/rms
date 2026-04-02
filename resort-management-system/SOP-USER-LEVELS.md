# 📋 Standard Operating Procedures (SOP): LuxeResort Manager
**"Operational Excellence for 5-Star Hospitality"**

This document defines the roles, responsibilities, and workflows for every user level within the LuxeResort ecosystem.

---

## 🏛️ LEVEL 1: THE ADMINISTRATOR (Owner/General Manager)
**User Role:** `administrator`
**Focus:** Strategy, Financial Health, and System Integrity.

### 📅 Daily (The Morning Audit)
1.  **Revenue & Dashboard Review:** Log in to the **Executive Dashboard**. Review today’s arrivals/departures and current month's revenue against targets.
2.  **Lead Management:** Check **LuxeResort > Lead Captures**. Ensure any high-priority inquiries are followed up or synced to CRM (HubSpot/Zoho).
3.  **Calendar Scan:** Quickly check the **Reservation Calendar** for any sync conflicts or maintenance blocks.

### 🔄 Weekly (System Optimization)
1.  **iCal Health Check:** Navigate to the Accommodations list. Verify all rooms show a "Healthy" sync status. Manually trigger "Sync Now" if needed.
2.  **Pricing Review:** Review the **Revenue Optimization** report. Adjust base rates if occupancy for the next 30 days is significantly above or below targets.
3.  **Promotional Planning:** Deactivate expired coupons and create new codes for upcoming "Secret Deals."

### 🛠️ Monthly (Business Intelligence)
1.  **Staff Audit:** Review and export **Staff Activity Logs** to CSV for accountability and training feedback.
2.  **Financial Export:** Generate a full **Revenue Report** CSV for accounting and periodic analysis (AOV/LTV).
3.  **Marketing ROI:** Compare booking attribution data (UTM) with marketing spend to optimize future campaigns.

---

## 🛎️ LEVEL 2: RESORT STAFF (Front Desk/Housekeeping)
**User Role:** `resort_staff`
**Focus:** Guest Flow, Service Response, and Room Readiness.

### 🌅 Shift Start (Operational Setup)
1.  **Arrivals & Departures:** Check **LuxeResort > Housekeeping Dashboard**. Identify rooms that require priority cleaning for early check-ins.
2.  **Pending Requests:** Review the **Guest Communication Log** for any unfulfilled service requests from the previous shift.

### 🧼 Housekeeping Workflow
1.  **Room Status:** As soon as a guest departs, mark the room as **'Cleaning'**.
2.  **Checklist Completion:** Follow the room-specific checklist (e.g., replenish coffee, fresh linens).
3.  **Ready for Front Desk:** Once complete, mark the room as **'Clean'**. This instantly updates the status for the Front Desk staff.

### 📞 Front Desk & Concierge
1.  **Guest Check-in:** For guests arriving in person, find their booking and update the status to **'Checked In'**. Verify if they have signed the **Digital Waiver**.
2.  **Service Request Response:** Monitor the dashboard for "In-Stay Service Requests" (e.g., "Fresh Towels"). Assign tasks to housekeeping and mark as "Resolved" when finished.
3.  **Payment Reconciliation:** For "Offline" bookings, record any cash/card payments directly in the **LuxeResort > Payments** screen.

---

## 👤 LEVEL 3: THE GUEST (Self-Service)
**Focus:** Convenience and Personalized Experience.

### 🏠 Pre-Arrival
1.  **Confirmation:** Review automated confirmation emails for stay details and location.
2.  **Digital Waiver:** Access the link in the email to sign the liability waiver before arrival.

### 🏖️ During Stay
1.  **Self Check-in:** On arrival day, use the **Guest Dashboard** to perform a "Digital Check-in."
2.  **Concierge Requests:** Submit service requests (e.g., "More Pillows") directly through the dashboard instead of calling the desk.
3.  **Balance Payment:** Log in to the dashboard at any time to pay any remaining balance via Stripe/PayPal.

### 🌅 Departure
1.  **Self Check-out:** Click "Check-out" on the dashboard to notify housekeeping that the room is ready for cleaning.
2.  **Feedback & Reviews:** Submit a star-rated review via the dashboard to earn **Loyalty Points** for the next visit.

---

## 🛑 EMERGENCY & SPECIAL PROCEDURES
-   **Double Booking Recovery:** In case of sync lag, the **Administrator** must immediately upgrade the guest to a higher-tier room or coordinate with the external channel (e.g., Airbnb) for relocation.
-   **Payment Failure:** If an automated payment fails, the **Front Desk** must contact the guest to provide a secure manual payment link or arrange for an alternative method.
