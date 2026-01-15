# Reporting System Quick Reference Guide

## For Staff & Administrators

### Accessing Staff Reports
1. **Via Sidebar:** Click "Reports & Analytics" in the staff dashboard sidebar
2. **Direct URL:** `http://yoursite/staff/reports/dashboard`

### Available Reports

#### 📊 Reports Dashboard
- **Location:** `/staff/reports/dashboard`
- **Features:**
  - KPI cards showing appointment statistics
  - Appointment status breakdown (completed, cancelled, no-show)
  - Total revenue and service breakdown
  - Dentist performance metrics
  - Service popularity ranking
  - Patient feedback ratings
  - Date range filtering (default: last 3 months)
  - Quick links to detailed reports

#### 📅 Appointment Analysis
- **Location:** `/staff/reports/appointments`
- **Features:**
  - Filter by date range, status, or dentist
  - Detailed appointment table with pagination
  - Patient information (name, email)
  - Service and dentist assignment
  - Appointment notes
  - Status badges (Completed, Cancelled, No Show)
  - CSV export capability

#### 💰 Revenue Report
- **Location:** `/staff/reports/revenue`
- **Features:**
  - Total revenue and appointment metrics
  - Revenue breakdown by service
  - Unit price and quantity information
  - Percentage contribution visualization
  - Dentist revenue contribution analysis
  - Financial performance summary

### Exporting Data
1. Click **"Export CSV"** button on any report
2. Select desired date range
3. Download will include all filtered results
4. Open in Excel/Google Sheets for further analysis

### Using Filters
1. Select **From Date** and **To Date**
2. (Optional) Select **Status** filter
3. (Optional) Select specific **Dentist**
4. Click **Filter** button
5. Click **Reset** to clear all filters

---

## For Patients

### Accessing Your Reports
1. **Via Navbar:** Click "My Reports" dropdown in top navigation
2. **Direct URLs:**
   - My Appointments: `/my-reports/appointments`
   - Treatment History: `/my-reports/treatments`
   - My Feedback: `/my-reports/feedback`

### Available Patient Reports

#### 📋 My Appointments
- **Shows:** All your appointment history
- **Includes:**
  - Appointment dates and times
  - Services booked
  - Assigned dentist
  - Current status (Completed, Cancelled, Upcoming)
  - Appointment notes
- **Features:**
  - Status summary cards
  - Pagination for easy browsing
  - Export your records as CSV

#### 🦷 Treatment History
- **Shows:** All completed treatments
- **Includes:**
  - Service received (with cost)
  - Dentist information
  - Treatment date and time
  - Service summary breakdown
- **Features:**
  - Total treatments count
  - Unique services count
  - Cost tracking per service
  - Pagination

#### ⭐ My Feedback
- **Shows:** Feedback you've submitted
- **Includes:**
  - Your ratings (1-5 stars)
  - Service and dentist context
  - Your comments
  - Submission date and time
- **Features:**
  - Average rating calculation
  - Rating distribution breakdown
  - Star visualization
  - Progress bars for distribution

### Exporting Your Records
1. Click **"Export Records"** button on any report
2. Download will include all your personal health records
3. Keep for personal records or share with other providers

---

## Common Use Cases

### Staff: Monthly Performance Review
1. Go to **Reports Dashboard**
2. Set date range to current month
3. Review KPI cards for overview
4. Click **Appointment Analysis** for detailed breakdown
5. Export data for record keeping

### Staff: Service Profitability Analysis
1. Go to **Revenue Report**
2. Review service revenue breakdown
3. Identify high-revenue and low-volume services
4. Make staffing/inventory decisions

### Staff: Dentist Performance Evaluation
1. Go to **Reports Dashboard**
2. Review dentist performance table
3. Check appointment completion rates
4. Compare with historical data (adjust date range)

### Patient: Checking Dental History
1. Click **My Appointments** → see full appointment history
2. Click **Treatment History** → see what was done and cost
3. Click **My Feedback** → see your ratings history
4. Export records for personal file or insurance

### Patient: Preparing Insurance Documentation
1. Go to **My Reports** → **Treatment History**
2. Click **Export Records**
3. Provide CSV to insurance company
4. Includes all service dates, types, and costs

---

## Tips & Tricks

**For Staff:**
- 💡 Use date range filters to compare periods (e.g., last month vs. this month)
- 💡 Filter by dentist to evaluate individual performance
- 💡 Export reports monthly for trend analysis
- 💡 Share exported data with management for reporting

**For Patients:**
- 💡 Bookmark your favorite report for quick access
- 💡 Export records before changing clinics
- 💡 Use treatment history for insurance claims
- 💡 Review feedback history to track satisfaction

---

## Report Parameters

### Date Range Options
- **Default:** Last 3 months
- **Custom:** Select any date range
- **Impact:** All metrics update based on selected range

### Status Filters (Appointment Reports)
- **Completed:** Successfully finished appointments
- **Cancelled:** Patient or clinic cancelled
- **No Show:** Patient didn't appear for appointment
- **All:** Show all statuses

### Export Format
- **Format:** CSV (Comma Separated Values)
- **Compatibility:** Excel, Google Sheets, Numbers
- **Contains:** All visible report data

---

## FAQ

**Q: Can I see other patients' reports?**
A: No. Patient reports only show your own data.

**Q: How often are reports updated?**
A: Reports show real-time data from the database. Refresh the page for latest data.

**Q: Can I print the reports?**
A: Yes. Use your browser's Print function (Ctrl+P). Export to PDF for better quality.

**Q: How far back can I view reports?**
A: All historical data is available. Set custom date range to any period.

**Q: What if the export button doesn't work?**
A: Ensure you have at least one record in the selected date range.

**Q: Can I schedule automated reports?**
A: Currently reports are on-demand. Contact admin for automated scheduling.

---

## Support

For issues or feature requests:
- Contact your clinic administrator
- Email: admin@yourclinic.com
- Report bugs through the system

---

**Last Updated:** January 2025
**Version:** 1.0
**Status:** Ready for Production
