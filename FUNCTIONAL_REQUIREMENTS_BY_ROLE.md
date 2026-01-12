# FUNCTIONAL REQUIREMENTS BY USER ROLE
## Dental Clinic Appointment & Queue Optimization System

---

# TABLE OF CONTENTS
1. [Patient Requirements](#1-patient-functional-requirements)
2. [Staff Requirements](#2-staff-functional-requirements)
3. [System & Other Requirements](#3-system--other-requirements)

---

---

# 1. PATIENT FUNCTIONAL REQUIREMENTS

## 1.1 APPOINTMENT BOOKING

### FR-P-1.1: Smart Appointment Booking
- Browse available services with descriptions and duration
- View available dentists with credentials
- Book appointments with the following details:
  - Patient name, phone, email
  - Service selection
  - Date selection (future dates only, within X days - configurable)
  - Time slot selection
  - Dentist preference (specific or any available)
  - Clinic location selection (Seremban, Kuala Pilah)
- Intelligent slot recommendation based on:
  - Service duration + buffer time
  - Dentist availability and workload
  - Room availability
  - Operating hours
  - Patient's historical no-show patterns

### FR-P-1.2: Appointment Confirmation & Digital Receipt
- Receive confirmation with:
  - Unique visit token (UUID)
  - Unique visit code (DNT-YYYYMMDD-###)
  - QR code for easy check-in
  - Appointment details (date, time, dentist, service, room)
  - Clinic address and directions
  - Estimated wait time
- Digital receipt via email (downloadable/printable)

### FR-P-1.3: Appointment Reminders
- Automatic reminder 24 hours before appointment (email + SMS/WhatsApp)
- Automatic reminder 1 hour before appointment (SMS/WhatsApp)
- Reminder content includes:
  - Appointment time
  - Estimated wait time
  - Check-in instructions
  - Clinic location and directions
  - QR code for quick check-in

### FR-P-1.4: Self-Service Appointment Management
- Reschedule appointments to different date/time
- Cancel appointments with reason selection
- View upcoming appointments list
- View appointment history
- Manage appointment preferences

### FR-P-1.5: Waitlist Management
- Join waitlist if appointment slot is fully booked
- Receive notification if slot becomes available
- Option to accept or decline available slot

### FR-P-1.6: Overbooking Prevention
- System prevents double-booking of same slot
- Buffer times enforced between appointments
- Automatic conflict detection

### FR-P-1.7: Appointment Source Tracking
- System tracks where appointment came from:
  - Online booking
  - Walk-in
  - Phone booking (by staff)
  - Staff-created

---

## 1.2 SELF CHECK-IN SYSTEM

### FR-P-2.1: Multiple Check-In Methods
Patient can check-in using one of:
- Scan QR code from confirmation email/SMS
- Enter visit token (6+ digits)
- Enter visit code (DNT-YYYYMMDD-###)
- Phone number + appointment date lookup
- Select from "My Appointments" list

### FR-P-2.2: Quick Check-In Process
- Minimal steps required
- Patient confirmation (name + phone verification)
- Automatic queue assignment
- Check-in timestamp recorded
- Arrival time vs appointment time tracked

### FR-P-2.3: Check-In Status Feedback
- Real-time confirmation message
- Current queue position
- Estimated wait time
- Dentist assigned
- Room assigned (if available)
- Status page accessible after check-in

### FR-P-2.4: Walk-In Registration
- Walk-in patients can register without prior appointment
- Provide basic info (name, phone, service)
- Quick queue assignment
- Join queue immediately

### FR-P-2.5: Pre-Check-In Alerts
- Notification 5 minutes before appointment time
- "Are you arriving?" confirmation
- If not arriving, option to reschedule or cancel

### FR-P-2.6: Late Arrival Handling
- System detects if patient arrives after appointment time
- Mark as "Late" with arrival time recorded
- Still allowed to check-in and join queue
- Late status tracked for no-show prediction

---

## 1.3 REAL-TIME STATUS TRACKING

### FR-P-3.1: Appointment Status Visibility
Patient can view:
- Current appointment status:
  - Booking Confirmed
  - Reminder Sent
  - Arrived/Checked-In
  - Waiting in Queue
  - Being Called/In-Service
  - Completed
- Current position in queue
- Estimated wait time
- Current status explanation

### FR-P-3.2: Queue Position Tracking
- "Where am I in queue?" view with:
  - Current queue position (e.g., "2nd in queue")
  - Patient names ahead (if privacy allows)
  - Estimated wait time until called
  - Live countdown to estimated call time
  - Visual queue length indicator

### FR-P-3.3: Real-Time Updates
- Live status updates as appointment progresses
- Queue position updates as patients ahead complete
- Automatic page refresh with new information
- Push notifications for status changes (optional)

### FR-P-3.4: Mobile-Optimized Tracking
- Mobile-friendly status page
- Works on all devices (phone, tablet, desktop)
- Quick-load interface for real-time updates

---

## 1.4 PUBLIC INFORMATION ACCESS

### FR-P-4.1: Clinic Information
- View clinic operating hours per location and day of week
- View clinic contact information
- View clinic address and map directions
- View clinic facilities and equipment information

### FR-P-4.2: Services Browsing
- View all available services
- Service descriptions and details
- Estimated service duration
- Service category (preventive, restorative, cosmetic, etc.)
- Service pricing (if applicable)

### FR-P-4.3: Dentist Directory
- View all active dentists
- Dentist photos and profiles
- Dentist specialties and credentials
- Dentist experience/years in practice
- Dentist patient reviews/ratings
- Dentist current availability status

### FR-P-4.4: Staff Directory
- View staff members marked as "public visible"
- Staff contact information
- Staff position/role
- Staff photos

### FR-P-4.5: Public Pages Access
- Home page with clinic overview
- About page with clinic information
- Services page with service listings
- Dentists page with team information
- Contact page with contact form
- Hours page with operating schedule
- Chat page for chatbot assistance

---

## 1.5 FEEDBACK & COMMUNICATION

### FR-P-5.1: Post-Appointment Feedback
- Submit feedback after appointment completion
- Rate appointment experience (1-5 stars)
- Rate specific aspects:
  - Dentist professionalism
  - Staff friendliness
  - Cleanliness/facility
  - Wait time satisfaction
  - Overall experience
- Optional written feedback/comments
- Feedback is anonymous or identified (patient choice)

### FR-P-5.2: Feedback Submission
- Easy-to-use feedback form
- Accessible via email link
- Accessible via SMS link
- Accessible via patient portal
- Can submit within X days of appointment

### FR-P-5.3: Feedback Confirmation
- Immediate confirmation message
- Thank you page or email
- Option to provide additional feedback

### FR-P-5.4: Complaint Submission
- Submit complaints or issues
- Complaint escalation to management
- Receive response/follow-up

### FR-P-5.5: Chatbot Communication
- Chat with clinic chatbot for:
  - General inquiries
  - Booking assistance
  - Location/directions
  - Services information
  - Frequently asked questions
  - Appointment rescheduling help
- Chatbot escalation to staff if needed

---

## 1.6 PATIENT JOURNEY TRACKING

### FR-P-6.1: Appointment Journey Stages
System tracks patient through:
1. Booking confirmed
2. Reminder sent (24h)
3. Reminder sent (1h)
4. Patient arrived/checked-in
5. Queue assigned
6. Patient called
7. Service in progress
8. Service completed
9. Follow-up

### FR-P-6.2: Communication History
- View all communications from clinic:
  - Booking confirmations
  - Reminder messages
  - Status updates
  - Follow-up messages
  - Feedback request

### FR-P-6.3: Appointment History
- View past appointments
- View appointment details (date, time, dentist, service)
- View treatment notes (if clinic allows access)
- Reschedule past services
- Rebook same service

### FR-P-6.4: Patient Preferences Learning
- System tracks patient preferences:
  - Preferred dentist
  - Preferred time slots
  - Preferred clinic location
  - Preferred services
- Recommendations based on history

---

## 1.7 VALIDATION RULES FOR PATIENT BOOKING

### FR-P-7.1: Input Validation
- Patient name: Required, 3-100 characters, alphanumeric + spaces
- Phone: Required, valid format (Malaysia format 60X-XXXX-XXXX or +60X-XXXX-XXXX)
- Email: Optional, valid email format if provided
- Date: Required, must be future date, within X days (configurable, e.g., 30 days)
- Time: Required, within clinic operating hours
- Service: Required, active service only
- Dentist: Required if preference is "specific", optional if "any"

### FR-P-7.2: Business Logic Validation
- Cannot book in the past
- Cannot book outside operating hours
- Cannot book if dentist is on leave
- Cannot book if clinic is closed
- Cannot book if service is inactive
- Appointment duration must fit within operating hours
- Buffer time must be respected

### FR-P-7.3: Conflict Prevention
- No double-booking of same dentist at same time
- Buffer time enforced between appointments
- Room availability checked
- Dentist availability checked

---

## 1.8 SECURITY & PRIVACY

### FR-P-8.1: Data Security
- Email and phone number encrypted
- Patient data only visible to patient
- Unique token prevents unauthorized access
- Session timeout after inactivity
- HTTPS/SSL encryption for all data transmission

### FR-P-8.2: Privacy Controls
- Anonymize feedback option
- Control visibility of information
- Option to opt-out of communications
- Data retention policies

---

---

# 2. STAFF FUNCTIONAL REQUIREMENTS

## 2.1 APPOINTMENT MANAGEMENT

### FR-S-1.1: View Appointments
- View all appointments in list view
- Filter by:
  - Date range
  - Status (pending, confirmed, completed, cancelled, late, no-show)
  - Dentist
  - Service
  - Location
  - Patient name
  - Appointment source
- Sort by:
  - Date/time
  - Dentist
  - Status
  - Patient name
  - Check-in time

### FR-S-1.2: Create Appointments Manually
- Create appointment for patient with:
  - Patient details (name, phone, email)
  - Service selection
  - Date and time
  - Dentist assignment
  - Location
  - Clinic location
  - Source (phone booking, walk-in, staff-created)
- Validation applied same as online booking
- Confirmation email/SMS sent automatically

### FR-S-1.3: Edit Appointments
- Edit appointment details:
  - Patient name/phone/email
  - Appointment date and time
  - Service
  - Dentist assignment
  - Room assignment
  - Location
  - Status
  - Notes
- Changes logged in activity log
- Automatic notification to patient if major change
- Conflict checking before saving

### FR-S-1.4: Cancel/Delete Appointments
- Delete appointments with:
  - Reason for cancellation
  - Optional notes
  - Confirmation message
- Activity logged
- Patient notified via email/SMS
- Audit trail maintained

### FR-S-1.5: Register Walk-In Patients
- Quick walk-in registration:
  - Patient name
  - Phone
  - Service requested
  - Preferred dentist (optional)
- Automatic check-in and queue assignment
- No appointment pre-booking required
- Join queue immediately

### FR-S-1.6: Patient Check-In Management
- View check-in page showing:
  - Upcoming appointments
  - Expected arrivals
  - Not yet checked in
  - Already checked in
- Manual check-in for patients:
  - Enter visit code/token
  - Select patient from list
  - Verify patient details
  - Confirm check-in
  - Record check-in timestamp

### FR-S-1.7: Appointment Status Workflow
Staff can update appointment status:
- Pending → Confirmed (when patient books/staff confirms)
- Confirmed → In-Progress (when dentist calls patient)
- In-Progress → Completed (when service finishes)
- Alternative flows:
  - Any → Cancelled (if patient cancels)
  - Confirmed → Late (if patient doesn't check in by appointment time + buffer)
  - Confirmed → No-Show (if patient doesn't check in after time threshold)

### FR-S-1.8: Bulk Appointment Operations
- Bulk cancel appointments
- Bulk reschedule appointments
- Bulk status update
- Bulk delete (soft delete)

### FR-S-1.9: Appointment Export
- Export appointments to:
  - PDF report
  - Excel/CSV
  - Calendar format (iCal)
- Include filters (date range, dentist, etc.)

### FR-S-1.10: Calendar View
- Month view of all appointments
- Week view with time slots
- Day view with hourly breakdown
- Color-coded by status or dentist
- Drag-and-drop reschedule capability
- Click to view/edit appointment details

---

## 2.2 QUEUE MANAGEMENT

### FR-S-2.1: Queue Assignment
- Automatic queue assignment on check-in based on:
  - Shortest queue length
  - Available dentist (if preference specified)
  - Service specialization match
  - Room availability
  - Estimated completion time
  - Dentist workload balance
- Manual override with reason logging

### FR-S-2.2: Queue Monitoring Dashboard
- Real-time queue status showing:
  - Current patient being served per dentist (Name, Service, Room, Time in service)
  - Queue length per dentist/queue
  - Next patient to be called
  - Patients waiting (list with names, services)
  - Estimated wait time for each queue
  - Average service time per dentist
  - Room occupancy status

### FR-S-2.3: Queue Management Actions
- Call next patient (click one button)
  - System displays patient name and appointment details
  - Staff announces/notifies patient
  - Mark as "Called/In-Service" status
- Mark appointment as completed (one click)
  - Patient exits queue
  - Dentist marked as available
  - Room marked as available
  - Next patient automatically queued
- Reorder queue (drag-and-drop)
  - Reorder patients in queue
  - Assign reason for reordering
  - Override for priority cases
  - Audit trail logged

### FR-S-2.4: Queue Status Workflow
- Checked-In → Waiting (automatic)
- Waiting → Called (staff action or automatic)
- Called → In-Service (automatic when dentist starts)
- In-Service → Completed (staff action)
- Alternative:
  - Waiting/Called → No-Show (automatic after time threshold)
  - Waiting → Late (manual mark after X minutes without check-in)

### FR-S-2.5: Manual Queue Interventions
- Override automatic queue assignment with reason
- Priority override for emergencies
- Skip patient with reason and rescheduling
- Requeue patient if service incomplete
- Batch queue reordering
- All interventions logged with:
  - Staff who made change
  - Timestamp
  - Reason
  - Before/after state

### FR-S-2.6: Auto-Status Updates
- Auto-mark as "Late" if:
  - Checked in > X minutes (configurable, default 15 mins) after appointment time
- Auto-mark as "No-Show" if:
  - Not checked in by appointment time + buffer time (configurable, default 30 mins)
- Auto-transition to next status based on time spent

### FR-S-2.7: Queue Alerts & Notifications
Alerts for:
- Queue length exceeding threshold
- Wait time exceeding average
- Appointment running significantly over time
- Room becoming idle
- Dentist becoming idle
- Patient arriving late (>X minutes)
- No-show imminent (approaching time threshold)

### FR-S-2.8: Queue Performance Metrics
Real-time display of:
- Current wait time per dentist
- Average wait time today
- Estimated wait time for next patient
- Queue throughput (patients/hour)
- Dentist utilization % (active vs idle)
- Room utilization % (occupied vs empty)
- No-show rate today
- Late rate today

---

## 2.3 DENTIST MANAGEMENT

### FR-S-3.1: Create Dentist Profile
- Create new dentist with:
  - Name (required)
  - License number (unique)
  - Specialties (preventive, restorative, cosmetic, etc.)
  - Qualifications/credentials
  - Experience/years in practice
  - Photo/avatar
  - Phone number
  - Email
  - Status (active/inactive)
  - Public visible flag
  - Notes

### FR-S-3.2: Edit Dentist Information
- Edit any dentist field
- Changes logged in activity log
- Availability automatically updated
- Patient notifications if major changes

### FR-S-3.3: Dentist Status Management
- Activate/deactivate dentist (soft delete)
- Deactivated dentist:
  - No longer available for new bookings
  - Can complete existing appointments
  - Can be restored from trash
- Force delete with confirmation (permanent deletion)
- Quick toggle status without opening full edit

### FR-S-3.4: Dentist Availability Hours
- Set working hours per day of week per dentist:
  - Monday - Sunday
  - Start time, end time
  - Break times
  - Multiple sessions per day
- Different hours per location (if applicable)
- Hours apply to all appointments for that dentist

### FR-S-3.5: Dentist Break Management
- Set lunch breaks per dentist
- Set other non-working periods
- Breaks prevent appointment scheduling
- Breaks are displayed on calendar
- Flexible break taking during day

### FR-S-3.6: Dentist Leave Management
- Create dentist leave/vacation:
  - Date range
  - Leave type (vacation, sick leave, training, other)
  - Reason/notes
- Leave periods prevent appointment scheduling
- Leave is visible on calendar
- Can delete/cancel leave
- Patients notified if appointment affected

### FR-S-3.7: Dentist Statistics & Analytics
View per-dentist:
- Total appointments (today, this week, this month, this year)
- Completed appointments
- Cancelled appointments
- Late appointments
- No-show appointments
- Average service duration
- Patient satisfaction rating/reviews
- Utilization rate (active time / available time)
- Efficiency score (queue time vs service time ratio)
- No-show rate
- Comparison with other dentists

### FR-S-3.8: Dentist Bulk Operations
- Bulk delete dentists
- Bulk activate/deactivate
- Bulk assign availability hours
- Bulk import dentists (from file)

### FR-S-3.9: Quick Dentist Status Toggle
- Single click to activate/deactivate
- Quick visibility toggle for public page
- Quick actions from dashboard without opening edit form

### FR-S-3.10: Dentist Performance View
- Ranking of dentists by metrics:
  - Most bookings
  - Highest satisfaction
  - Best utilization
  - Fastest service
  - Fewest no-shows

---

## 2.4 DENTIST SCHEDULES & LEAVE

### FR-S-4.1: Manage Weekly Schedules
- Set weekly working hours template:
  - Days of week selection
  - Time slots per day
  - Breaks/lunch times
  - Apply to all dentists or specific dentist
- Copy schedule from one dentist to another
- Bulk edit schedules

### FR-S-4.2: Location-Specific Hours
- Set different hours for different clinic locations
- Seremban vs Kuala Pilah availability per dentist
- Dentist rotation across locations

### FR-S-4.3: Manage Dentist Leaves
- Create leave entry:
  - Dentist selection
  - Date range
  - Leave type (vacation, sick, training, conference)
  - Reason/description
- Delete/cancel leave
- Update leave dates
- View all leaves on calendar
- Prevent double-booking during leave

### FR-S-4.4: Leave Calendar View
- Visual calendar showing:
  - All dentists' leave periods
  - Color-coded by leave type
  - Hover for details
  - Click to edit/delete
- Filter by dentist
- Filter by leave type
- Filter by date range

### FR-S-4.5: Automatic Booking Prevention
- System prevents appointment scheduling:
  - Outside working hours
  - During breaks
  - During leave periods
  - When dentist is marked unavailable
  - When requested time is unavailable

### FR-S-4.6: Schedule Conflict Detection
- Alert if:
  - Dentist has overlapping appointments
  - Appointment duration exceeds available time
  - Leave conflicts with bookings

### FR-S-4.7: Schedule Export
- Export dentist schedule to:
  - PDF
  - Calendar format (iCal)
  - Excel/CSV

---

## 2.5 SERVICE MANAGEMENT

### FR-S-5.1: Create Services
- Create new service with:
  - Service name (required)
  - Description
  - Estimated duration (in minutes)
  - Category (preventive, restorative, cosmetic, whitening, orthodontics, etc.)
  - Price (optional, if billing module exists)
  - Status (active/inactive)
  - Recommended frequency (e.g., every 6 months for cleaning)
  - Notes

### FR-S-5.2: Edit Service Information
- Edit any service field
- Changes logged
- New appointments use updated duration
- Alert if changing duration affects existing appointments

### FR-S-5.3: Service Status Management
- Activate/deactivate services
- Inactive services:
  - Cannot be booked by patients
  - Can be used for existing appointments
  - Not visible in service list
- Quick toggle status (one click)
- Force delete with confirmation (soft delete)

### FR-S-5.4: Service Visibility Control
- Control which services are visible to patients
- Hide services from online booking
- Services remain available for staff to use

### FR-S-5.5: Bulk Service Operations
- Bulk delete services
- Bulk activate/deactivate
- Bulk categorize services
- Bulk import services

### FR-S-5.6: Service Statistics
View per-service:
- Booking frequency (how many bookings this month/year)
- Service popularity ranking
- Average wait time for service
- Average service duration (actual vs estimated)
- Services with inaccurate duration estimates
- Revenue (if billing module exists)

### FR-S-5.7: Duration Accuracy Monitoring
- Track actual service duration vs estimated
- Alert for services with large variance
- Suggest duration adjustments
- Historical duration trends

---

## 2.6 TREATMENT ROOM MANAGEMENT

### FR-S-6.1: Create Treatment Rooms
- Create new room with:
  - Room name/number (required)
  - Room type (general, surgical, pediatric)
  - Equipment list
  - Capacity
  - Status (active/inactive)
  - Photo/image
  - Notes

### FR-S-6.2: Edit Room Information
- Edit room details
- Update equipment list
- Enable/disable room
- Changes logged

### FR-S-6.3: Room Assignment
- Assign rooms to appointments:
  - Manual assignment by staff
  - Automatic assignment based on:
    - Service requirements
    - Room availability
    - Load balancing
- View room assignment history
- Change room assignment if needed

### FR-S-6.4: Room Status Management
- Activate/deactivate rooms
- Mark room as under maintenance
- Prevent appointments in inactive/maintenance rooms
- Quick toggle status
- Soft delete with restoration option

### FR-S-6.5: Room Occupancy View
- Real-time room status:
  - Currently occupied (patient name, dentist, appointment)
  - Time spent in room
  - Estimated time remaining
  - Next appointment in room
  - Currently empty/available
- Timeline view of room usage throughout day

### FR-S-6.6: Room Utilization Metrics
Per-room statistics:
- Occupancy time (total, percentage)
- Idle time
- Utilization rate
- Average occupancy duration
- Peak usage times
- Identify underutilized rooms

### FR-S-6.7: Bulk Room Operations
- Bulk activate/deactivate
- Bulk status update
- Bulk maintenance toggle

---

## 2.7 OPERATING HOURS MANAGEMENT

### FR-S-7.1: Set Operating Hours
Define clinic operating hours by:
- Location (Seremban, Kuala Pilah)
- Day of week (Monday-Sunday)
- Start time and end time
- Support multiple sessions per day
  - Morning session (e.g., 9am-12pm)
  - Afternoon session (e.g., 2pm-6pm)
- Mark as active/inactive

### FR-S-7.2: Define Break/Lunch Periods
- Set lunch break times
- Set other break periods
- Breaks prevent appointment scheduling
- Breaks visible on booking calendar

### FR-S-7.3: Public Holidays Management
- Mark specific dates as closed (public holidays)
- Prevent appointments on closed dates
- Closed dates visible on calendar
- Staff notified of upcoming closures

### FR-S-7.4: Edit Operating Hours
- Edit existing hour entries
- Change times
- Change active/inactive status
- Merge overlapping sessions
- Add new sessions

### FR-S-7.5: Duplicate Operating Hours
- Duplicate existing hours to other days/weeks
- Apply same schedule to multiple locations
- Quick copy to next week/month

### FR-S-7.6: Delete Operating Hours
- Delete specific hour entries
- Soft delete with restoration option
- Confirm deletion (might affect existing appointments)

### FR-S-7.7: Bulk Operations
- Bulk delete hour entries
- Bulk activate/deactivate
- Bulk set for multiple days at once

### FR-S-7.8: Calendar View
- Visual calendar of operating hours
- Color-coded by location
- Click to edit/delete
- Drag-and-drop to adjust times (future)

### FR-S-7.9: Appointment Prevention
- System prevents appointments:
  - Outside operating hours
  - During breaks
  - On closed dates
  - Before clinic opens
  - After clinic closes
- Booking form shows available hours only

---

## 2.8 STAFF MANAGEMENT

### FR-S-8.1: Create Staff Profiles
- Create new staff with:
  - Name (required)
  - Email (required, unique)
  - Phone
  - Position/role (receptionist, dentist, nurse, admin)
  - Password (auto-generated or custom)
  - Photo/avatar
  - Status (active/inactive)
  - Public visible flag
  - Notes

### FR-S-8.2: Edit Staff Information
- Edit staff details
- Change password
- Update position/role
- Enable/disable account
- Changes logged

### FR-S-8.3: Staff Status Management
- Activate/deactivate staff account
- Soft delete with restoration
- Force delete with confirmation
- Quick toggle active/inactive

### FR-S-8.4: Staff Public Visibility
- Toggle staff visibility on public pages:
  - Show on contact page
  - Hide from public view
- Only visible staff appear in staff directory
- Patients can contact visible staff

### FR-S-8.5: Password Management
- Reset staff password
- Force password reset on next login
- Password strength requirements
- Password expiration (optional)

### FR-S-8.6: Staff Directory
- View all staff members
- Filter by status (active/inactive)
- Filter by position/role
- Filter by public/private
- Export staff list

### FR-S-8.7: Role & Permission Assignment
- Assign role to staff:
  - Admin (full access)
  - Manager/Supervisor (reporting, settings)
  - Staff (daily operations)
  - Receptionist (bookings, check-in)
- Role-based access control
- Permissions per role defined

---

## 2.9 ACTIVITY LOGS & AUDIT TRAIL

### FR-S-9.1: View Activity Logs
- View all system activities:
  - User actions (login, logout, data changes)
  - Appointment changes (create, edit, delete, status)
  - Queue operations (assignment, reordering)
  - Staff actions (who did what, when)
- Include:
  - Timestamp
  - User who made change
  - Action performed
  - Record affected
  - Old value vs new value
  - Reason (if provided)
  - IP address

### FR-S-9.2: Activity Log Filtering
Filter by:
- User/staff member
- Action type (create, edit, delete, status change)
- Date range
- Record type (appointment, queue, dentist, etc.)
- Record ID/name
- Status/result (success/failure)

### FR-S-9.3: Activity Log Search
- Search for specific changes
- Full-text search in activity log
- Audit trail for specific appointment/record

### FR-S-9.4: Deleted Records Recovery
- View soft-deleted records:
  - Deleted dentists
  - Deleted staff
  - Deleted services
  - Deleted rooms
  - Deleted appointments
- Restore deleted records
- Permanently delete with confirmation
- View deletion timestamp and user

### FR-S-9.5: Audit Reports
- Generate audit reports:
  - Change history per record
  - User activity summary
  - System change history
  - Export audit log
- Compliance with retention policies

### FR-S-9.6: Restore Deleted Records
- Restore deleted dentists with their data
- Restore deleted staff with their data
- Restore deleted appointments
- Restore deleted other records
- Confirmation before restoration

---

## 2.10 FEEDBACK MANAGEMENT

### FR-S-10.1: View Patient Feedback
- View all patient feedback/reviews:
  - Patient name (or anonymous)
  - Rating (1-5 stars)
  - Written feedback/comments
  - Feedback date
  - Appointment details (date, dentist, service)
  - Feedback status (read/unread)

### FR-S-10.2: Filter Feedback
Filter by:
- Date range
- Rating (show 5-star, or <5-star, etc.)
- Status (read/unread)
- Patient
- Dentist
- Service
- Feedback type (positive, negative, neutral)

### FR-S-10.3: Detailed Feedback View
- Click feedback to see full details:
  - All feedback details
  - Associated appointment info
  - Patient contact info (if not anonymous)
  - Related feedback history
  - Feedback status

### FR-S-10.4: Mark Feedback as Read/Unread
- Track which feedback staff has reviewed
- Unread feedback highlighted
- Bulk mark as read

### FR-S-10.5: Respond to Feedback (Future)
- Optional: Staff can respond to feedback
- Response sent to patient via email
- Track feedback management

### FR-S-10.6: Feedback Analytics
- Feedback summary:
  - Average rating
  - Total feedback received
  - Rating distribution (pie chart)
  - Feedback trends over time
  - Feedback per dentist
  - Feedback per service
  - Correlation with wait time/satisfaction

### FR-S-10.7: Export Feedback Reports
- Export feedback to:
  - PDF report
  - Excel/CSV
  - Include filters applied

---

## 2.11 QUICK EDIT DASHBOARD

### FR-S-11.1: Single-Page Quick Management
- Consolidated view for frequently changed items:
  - Dentist status (active/inactive) - quick toggle
  - Service status (active/inactive) - quick toggle
  - Room status (active/inactive) - quick toggle
  - Staff visibility (public/private) - quick toggle
  - Operating hours status

### FR-S-11.2: Quick Toggle Features
- Single click to toggle status
- No page reload required
- Confirmation message
- Changes take effect immediately
- Audit logged

### FR-S-11.3: Quick Operating Hours Edit
- Edit operating hours inline:
  - Edit time without opening full edit form
  - Quick status toggle
  - Quick duplicate to other days
  - Quick delete

### FR-S-11.4: Quick Staff Visibility Management
- Toggle staff public visibility
- Manage who appears on contact page
- Quick actions without opening staff edit

### FR-S-11.5: Quick Statistics Widget
- Small dashboard showing:
  - Today's appointments (count)
  - Checked-in (count)
  - Waiting in queue (count)
  - Current no-show count
  - Current late count

---

## 2.12 REAL-TIME FEATURES & NOTIFICATIONS

### FR-S-12.1: Real-Time Appointment Updates
- Live appointment data:
  - New appointments appear immediately
  - Status changes reflect in real-time
  - Check-ins appear instantly
  - Dentist availability updates live

### FR-S-12.2: Real-Time Queue Updates
- Live queue status:
  - Queue position updates
  - Status changes in real-time
  - New patients added to queue immediately
  - Completed appointments remove from queue instantly

### FR-S-12.3: Staff Calendar with Live Events
- Calendar showing:
  - All appointments
  - Live status updates
  - Color-coded by status
  - Dentist availability
  - Room occupation

### FR-S-12.4: Real-Time Notifications
- Notifications for:
  - New appointment booked
  - Patient checked in
  - Patient arriving late
  - Appointment overrunning
  - Queue length exceeding threshold
  - New walk-in registered
  - Staff status changes

### FR-S-12.5: WebSocket/Live Updates
- < 1 second latency for updates
- Automatic page refresh
- Polling fallback if WebSocket unavailable
- Notification bell/badge for new events

---

## 2.13 DEVELOPER & ADMIN TOOLS

### FR-S-13.1: API Testing Interface
- Test API endpoints:
  - GET/POST/PATCH/DELETE requests
  - Request builder with parameters
  - Response viewer with formatting
  - Error debugging
  - Authentication testing

### FR-S-13.2: Developer Dashboard
- System statistics:
  - Database info (records count)
  - API metrics
  - System health
  - Queue depth
  - System configuration

### FR-S-13.3: Developer Authentication
- Special developer login
- Developer mode enable/disable
- API key management
- Request logging

### FR-S-13.4: API Endpoint Documentation
- View all available API endpoints
- Endpoint parameters and responses
- Authentication requirements
- Rate limiting info
- Example requests

---

## 2.14 CALENDAR VIEW

### FR-S-14.1: Staff Calendar Interface
- Month view of all appointments:
  - Color-coded by status or dentist
  - Visual grid showing appointments
  - Click to view/edit details
- Week view:
  - Time-based grid (7-day week)
  - Better for weekly planning
  - Drag-and-drop reschedule
- Day view:
  - Hourly breakdown
  - Detailed timeline
  - Easy rescheduling

### FR-S-14.2: Calendar Filtering
- Filter by:
  - Dentist
  - Service
  - Status
  - Location
  - Staff member

### FR-S-14.3: Calendar Events
- Live events showing:
  - Appointment start/end
  - Dentist assigned
  - Service type
  - Room assigned
  - Status color-coding

---

---

# 3. SYSTEM & OTHER REQUIREMENTS

## 3.1 ADMIN REQUIREMENTS

### FR-ADMIN-1.1: System Administration
Admin has all Staff privileges plus:
- User role and permission management
- System-wide settings and configuration
- Data backup and recovery
- Database maintenance
- Security settings
- Integration management

### FR-ADMIN-1.2: Complete User Management
- Create, edit, delete users
- Assign roles and permissions
- Reset user passwords
- Monitor user activity
- Disable/enable accounts
- Manage user access levels

### FR-ADMIN-1.3: System Settings
- Clinic configuration:
  - Queue timeout settings (when to mark no-show)
  - Overbooking policies
  - Buffer time per service
  - Peak hour definitions
  - Appointment max advance days
- Alert thresholds:
  - Queue length warning
  - Wait time threshold
  - Overrun threshold
  - Idle time threshold

### FR-ADMIN-1.4: System-Wide Audit Access
- View all audit logs
- Filter audit logs comprehensively
- Export audit reports
- Compliance reporting
- User activity tracking

### FR-ADMIN-1.5: Data Management
- Data backup functionality
- Data recovery capability
- Database cleanup
- Soft delete management
- Permanent data deletion (with confirmation)

---

## 3.2 OPERATIONAL INTELLIGENCE & ANALYTICS

### FR-OI-1.1: Executive Dashboard
Real-time overview showing:
- **KPI Summary:**
  - Total appointments today
  - Checked-in vs waiting count
  - Current queue length
  - Average wait time (real-time)
  - No-show count and rate today
  - Late count and rate today
- **Dentist Performance:**
  - Dentists currently with patients
  - Appointments completed count
  - Average service time
  - Utilization % per dentist
  - Queue depth per dentist
- **Room Status:**
  - Room occupancy status (visual)
  - Idle rooms
  - Occupied rooms with patient info
  - Room utilization %
- **Alert Panel:**
  - Active alerts (queue too long, wait time high, etc.)
  - Recent critical events

### FR-OI-1.2: Real-Time Metrics
Staff can see:
- Current wait time per dentist (live)
- Average wait time today (live)
- Estimated wait time for next patient
- Queue throughput (patients/hour, live)
- Dentist utilization % (active vs idle)
- Room occupancy %

### FR-OI-1.3: Alerts & Notifications
- Alerts for:
  - Queue length > threshold (e.g., > 5 patients)
  - Wait time > average (e.g., > 45 mins)
  - Appointment running > X minutes over
  - Room idle for > X minutes
  - Dentist idle for > X minutes
  - Patient arriving significantly late
  - No-show imminent (approaching time threshold)
  - System issues/errors

---

### FR-OI-2.1: Queue Performance Analytics
Detailed queue metrics:
- **Wait Time Analysis:**
  - Average wait time (today, this week, this month)
  - Min/max wait time
  - Wait time by time of day
  - Wait time by dentist
  - Wait time by service type
  - Wait time trends (line chart over time)
  - Wait time distribution
- **Service Time Analysis:**
  - Actual vs estimated service duration
  - Service time variance analysis
  - Dentist service time comparison
  - Identify services with inaccurate duration estimates
  - Service time trends
  - Buffer time effectiveness
- **Throughput Analysis:**
  - Patients served per hour/day
  - Queue clearance rate
  - Bottleneck identification
  - Peak demand periods
  - Throughput by dentist
  - Throughput by service

### FR-OI-2.2: No-Show & Late Pattern Analysis
- **No-Show Analytics:**
  - No-show rate by time slot (morning, afternoon, evening)
  - No-show rate by dentist preference
  - No-show rate by service type
  - No-show rate by location
  - Patient no-show history/pattern
  - Predictive no-show scoring
  - Seasonal trends
- **Late Pattern Analysis:**
  - Late rate by time slot
  - Late rate by distance/location
  - Late rate by service type
  - Recurring late patients

---

### FR-OI-3.1: Dentist Efficiency Analytics
Per-dentist metrics:
- **Utilization:**
  - Active time vs idle time
  - Utilization rate (%)
  - Patient preparation time
  - Documentation time
  - Transition time between patients
- **Productivity:**
  - Total patients served (daily, weekly, monthly)
  - Completed vs cancelled ratio
  - Average patient satisfaction
  - Efficiency score (composite metric)
- **Comparison:**
  - Rank dentists by efficiency
  - Benchmark against clinic average
  - Identify best/worst performers
  - Variation analysis

### FR-OI-3.2: Room Utilization Analytics
Per-room metrics:
- **Occupancy:**
  - Occupancy time (total hours)
  - Occupancy rate (%)
  - Idle time analysis
  - Peak usage times
  - Usage timeline throughout day
- **Efficiency:**
  - Room utilization score
  - Identify underutilized rooms
  - Recommendations for room allocation
  - Room capacity matching

### FR-OI-3.3: Service-Level Analytics
Per-service metrics:
- **Demand:**
  - Booking frequency (monthly, yearly)
  - Service popularity ranking
  - Seasonal demand patterns
  - Peak demand periods for service
- **Performance:**
  - Average wait time for service
  - Average service duration (actual)
  - Duration accuracy (estimated vs actual)
  - Service completion rate
- **Quality:**
  - Service-specific patient satisfaction
  - Service-specific no-show rate
  - Service-specific late rate

---

### FR-OI-4.1: Predictive Analytics
Machine learning-based predictions:
- **No-Show Prediction:**
  - Predict probability patient will no-show
  - Risk scoring (low, medium, high)
  - Alert staff of high-risk no-shows
  - Historical accuracy of prediction
- **Demand Forecasting:**
  - Forecast daily queue length
  - Forecast peak hours
  - Forecast required staffing
  - Seasonal demand forecasting
- **Wait Time Prediction:**
  - Estimate wait time for incoming appointment
  - Factors considered (current queue, service, dentist)
  - Accuracy tracking

### FR-OI-4.2: Prescriptive Recommendations
- **Appointment Scheduling:**
  - Recommend optimal appointment time
  - Suggest dentist with shortest queue
  - Recommend room allocation
  - Suggest buffer time adjustments
- **Queue Management:**
  - Recommend queue reordering
  - Alert for overcrowding prevention
  - Suggest walk-in deflection if queue too long
  - Recommend service split if appointment too long
- **Resource Optimization:**
  - Recommend dentist availability adjustments
  - Suggest room allocation changes
  - Recommend service duration refinement
  - Identify overbooked time slots

---

### FR-OI-5.1: Historical Analytics & Reporting
Comprehensive reporting system:
- **Predefined Reports:**
  - Daily performance report
  - Weekly performance summary
  - Monthly clinic summary
  - Dentist performance report
  - Service utilization report
  - Queue performance report
  - Patient satisfaction report
  - No-show/late report
- **Custom Report Builder:**
  - Select metrics to include
  - Choose date range
  - Filter by dentist/service/location
  - Choose visualization (table, chart, graph)
  - Save report template
  - Schedule report delivery (email)
- **Report Export:**
  - Export to PDF
  - Export to Excel/CSV
  - Export with charts/graphs
  - Email report distribution

### FR-OI-5.2: KPI Dashboard
Track against targets:
- **Service Level:**
  - Average wait time: Target < 30 mins ✓/✗
  - No-show rate: Target < 5% ✓/✗
  - Late rate: Target < 10% ✓/✗
- **Efficiency:**
  - Dentist utilization: Target > 80% ✓/✗
  - Room utilization: Target > 75% ✓/✗
  - Queue clearance: Target > 95% ✓/✗
- **Quality:**
  - Patient satisfaction: Target > 4.5/5 ✓/✗
  - Appointment accuracy: Target > 95% ✓/✗
  - System uptime: Target 99.5% ✓/✗
- **Financial (if applicable):**
  - Revenue per dentist
  - Revenue per service
  - Profit margins

---

## 3.3 CONFIGURATION & CUSTOMIZATION

### FR-CONFIG-1: Clinic Configuration
Configurable parameters:
- **Appointment Settings:**
  - Max advance booking days (default 30)
  - Min appointment duration (default 15 mins)
  - Max overbooking ratio (default 1:1)
  - Default buffer time between appointments (default 5 mins)
  - Per-service buffer time override
- **Queue Settings:**
  - Late marking threshold (default 15 mins)
  - No-show marking threshold (default 30 mins)
  - Queue timeout (when to clear waiting patient)
  - Queue reorder max changes per day
  - Priority override max per day
- **Notification Settings:**
  - 24h reminder enabled/disabled
  - 24h reminder time (e.g., 9am)
  - 1h reminder enabled/disabled
  - Email vs SMS preference
  - WhatsApp integration enabled
- **Operating Settings:**
  - Clinic name(s)
  - Clinic location(s) (Seremban, Kuala Pilah, others)
  - Timezone
  - Language
  - Currency (if billing)

### FR-CONFIG-2: Alert Thresholds
Configurable alert triggers:
- **Queue Alerts:**
  - Alert when queue length > X (default 5)
  - Alert when wait time > X mins (default 45)
- **Appointment Alerts:**
  - Alert when appointment overrun > X mins (default 15)
  - Alert when patient late > X mins (default 15)
- **Resource Alerts:**
  - Alert when dentist idle > X mins (default 30)
  - Alert when room idle > X mins (default 30)
  - Alert when queue clearance < X% (default 90%)

### FR-CONFIG-3: Notification Preferences
- **By Role:**
  - Receptionist notifications (check-ins, appointments)
  - Dentist notifications (next patient, alerts)
  - Admin notifications (system events, reports)
- **By Channel:**
  - Email notifications
  - SMS/WhatsApp notifications
  - In-app notifications
  - Push notifications (mobile)
- **By Priority:**
  - Critical alerts (always notify)
  - Warning alerts (notify if enabled)
  - Info notifications (optional)

---

## 3.4 DATA REQUIREMENTS

### FR-DATA-1: Core Data Entities
System manages:
- Patients/Appointments
- Dentists & Schedules
- Services
- Treatment Rooms
- Operating Hours
- Queue Records
- Activity Logs
- Feedback/Reviews
- Staff Users
- DentistLeave Records

### FR-DATA-2: Data Capture
Captured data includes:
- Appointment journey timestamps (11 stages)
- Service duration (actual vs estimated)
- Queue performance metrics
- Dentist efficiency data
- Room utilization data
- Patient satisfaction data
- Activity logs with audit trail
- No-show/late patterns

### FR-DATA-3: Data Retention Policy
- Appointment records: 7 years (compliance)
- Activity logs: 2 years
- Feedback: 3 years
- Soft-deleted records: 1 year before permanent deletion
- Temporary files: 30 days

### FR-DATA-4: Data Integrity
- Transaction rollback on failures
- Data consistency validation
- Duplicate prevention
- Referential integrity checks
- Soft delete preservation
- Audit trail completeness

---

## 3.5 INTEGRATION & EXTERNAL SYSTEMS

### FR-INT-1: Email Integration
- Send appointment confirmations
- Send reminders
- Send feedback requests
- Send notifications
- Support HTML email templates
- Customizable email content

### FR-INT-2: SMS/WhatsApp Integration
- Send appointment reminders via SMS
- Send appointment reminders via WhatsApp
- Send check-in links
- Configurable message content
- Delivery status tracking
- Scheduled sending support

### FR-INT-3: Calendar Export
- Export appointments to iCal format
- Patient can import to their calendar
- Staff calendar export
- Ongoing sync (future)

### FR-INT-4: Queue Display System
- Public-facing queue board display
- Large screen friendly interface
- Real-time queue updates
- Patient name display (or anonymous)
- Current service + room
- "Next in queue" indicator
- Visual queue length representation
- Refresh every few seconds

### FR-INT-5: Payment Integration (Future)
- Payment gateway integration
- Online payment for appointments
- Invoice generation
- Payment tracking
- Revenue reporting

---

## 3.6 SECURITY REQUIREMENTS

### FR-SEC-1: Authentication & Authorization
- User login with email and password
- Password hashing (bcrypt)
- Session management
- Role-based access control (RBAC)
- Permission-based access control
- Logout functionality
- Idle session timeout (30 mins default)

### FR-SEC-2: Data Protection
- HTTPS/SSL encryption for data in transit
- Database encryption for sensitive data (emails, phones)
- Passwords hashed and salted
- Sensitive data not logged
- PII (personally identifiable information) protection

### FR-SEC-3: Application Security
- CSRF token protection on forms
- SQL injection prevention (parameterized queries)
- XSS (Cross-Site Scripting) prevention
- Input validation and sanitization
- Output encoding
- Rate limiting on APIs
- Brute-force attack prevention

### FR-SEC-4: Audit & Logging
- Activity logging for all important actions
- Audit trail with before/after values
- User identification in logs
- Timestamp accuracy
- Non-repudiation (can't deny action)
- Log protection (append-only)

### FR-SEC-5: Access Control
- Staff can only see their clinic location (if applicable)
- Patients can only see their appointments
- Data isolation per clinic (if multi-clinic)
- Permission validation on every request
- Sensitive endpoints require authentication

---

## 3.7 PERFORMANCE REQUIREMENTS

### FR-PERF-1: Response Time
- Page load time: < 2 seconds
- API response time: < 500ms (95th percentile)
- Queue update latency: < 1 second
- Database query time: < 100ms for common queries
- Real-time update latency: < 1 second

### FR-PERF-2: Scalability
- Support 100+ concurrent authenticated users
- Support 50+ concurrent appointment bookings/minute
- Support 10,000+ appointments/month
- Support 50+ dentists
- Support 20+ clinics (future)
- Horizontal scaling capability for app servers

### FR-PERF-3: Resource Optimization
- Minimize database queries
- Implement caching (Redis)
- Optimize images
- Minify CSS/JavaScript
- Database indexing for common queries
- Connection pooling

---

## 3.8 RELIABILITY & BACKUP

### FR-REL-1: Uptime
- System uptime: 99.5% (< 3.5 hours downtime/month)
- Graceful degradation under load
- Error handling and recovery
- Automatic retry on transient failures

### FR-REL-2: Backup & Recovery
- Automated daily backups
- Multiple backup locations
- Backup retention: 30 days
- Recovery time objective (RTO): 4 hours
- Recovery point objective (RPO): 1 day
- Tested recovery procedures

### FR-REL-3: Disaster Recovery
- Backup to off-site location
- Database replication (standby)
- Health checks and monitoring
- Automated failover (future)
- Incident response plan

---

## 3.9 COMPLIANCE & REGULATIONS

### FR-COMP-1: Data Protection
- Comply with Malaysia Personal Data Protection Act (PDPA)
- Data retention policies documented
- User consent for data processing
- Right to access/delete personal data
- Privacy policy published

### FR-COMP-2: Healthcare Standards (Future)
- Comply with healthcare data protection regulations
- Secure patient health information
- Audit trails for healthcare data access
- Healthcare provider authentication

### FR-COMP-3: System Documentation
- Architecture documentation
- API documentation
- Security documentation
- Operations manual
- Disaster recovery procedures
- Change management log

---

## 3.10 TESTING & QUALITY ASSURANCE

### FR-QA-1: Testing Coverage
- Unit tests for business logic
- Integration tests for workflows
- API tests for endpoints
- Functional tests for features
- Performance/load tests
- Security tests
- User acceptance testing (UAT)

### FR-QA-2: Code Quality
- Code review process
- Static code analysis
- Linting and formatting standards
- Documentation requirements
- Test coverage minimum 70%

### FR-QA-3: Deployment
- Staging environment
- Pre-production testing
- Blue-green deployment
- Rollback capability
- Change log and release notes

---

## 3.11 MONITORING & SUPPORT

### FR-MON-1: System Monitoring
- Server health monitoring
- Database performance monitoring
- Application error tracking
- API endpoint monitoring
- Queue monitoring
- Real-time alerts for issues

### FR-MON-2: Reporting & Dashboards
- Admin dashboard
- System health dashboard
- Error rate monitoring
- User activity monitoring
- Performance metrics dashboard
- Incident tracking

### FR-MON-3: Support & Documentation
- User documentation
- Admin guides
- Troubleshooting guides
- FAQ section
- Support contact information
- Change log

---

## 3.12 VALIDATION RULES (SYSTEM-WIDE)

### FR-VAL-1: Appointment Validation
- Date must be in future (not past or today)
- Date must be within X days (configurable)
- Time must be within clinic operating hours
- Time must not be during breaks
- Date must not be clinic closed day
- Service must be active
- Dentist must be active and available (if preference)
- No double-booking same dentist
- Buffer time must be respected
- Duration must fit within operating hours

### FR-VAL-2: Data Validation
- Required fields enforced
- Phone format validation (Malaysia format)
- Email format validation
- Date format validation (YYYY-MM-DD)
- Time format validation (HH:MM)
- Numeric fields within valid range
- String length limits enforced

### FR-VAL-3: Business Logic Validation
- Cannot delete dentist with future appointments
- Cannot delete service with future appointments
- Cannot modify past appointments (audit only)
- Cannot override certain system rules without approval
- Service duration cannot be 0
- Appointment must have valid service
- Dentist must be available for appointment time

---

## 3.13 SUCCESS METRICS & KPIs

System success measured by:

| Category | Metric | Target | Status |
|----------|--------|--------|--------|
| **Service Quality** | Average Wait Time | < 30 mins | To achieve |
| | No-Show Rate | < 5% | To achieve |
| | Late Rate | < 10% | To achieve |
| | Patient Satisfaction | > 4.5/5 | To achieve |
| **Operational** | Dentist Utilization | > 80% | To achieve |
| | Room Utilization | > 75% | To achieve |
| | Appointment Accuracy | > 95% | To achieve |
| | Queue Clearance Rate | > 95% | To achieve |
| **System** | System Uptime | 99.5% | To achieve |
| | Page Load Time | < 2 sec | To achieve |
| | API Response Time | < 500ms | To achieve |
| **Patient** | Online Booking Completion | > 80% | To achieve |
| | Self Check-In Rate | > 60% | To achieve |
| | Feedback Response Rate | > 30% | To achieve |

---

## 3.14 FUTURE ENHANCEMENTS

### FR-FUTURE-1: Advanced Features
- SMS/WhatsApp appointment booking
- Mobile app (iOS/Android)
- Video consultation integration
- Patient health records management
- Treatment planning system
- Insurance claim processing
- Payment processing and invoicing
- Marketing automation (email campaigns)

### FR-FUTURE-2: Advanced Analytics
- AI-powered scheduling optimization
- Predictive patient behavior
- Revenue optimization
- Staff scheduling optimization
- Multi-location analytics
- Benchmarking against industry standards

### FR-FUTURE-3: Integration
- EMR/EHR system integration
- Lab management system integration
- Insurance provider integration
- Payment gateway integration
- Third-party appointment software

### FR-FUTURE-4: Scalability
- Multi-tenant SaaS platform
- Enterprise features
- API marketplace
- White-label solution
- Global expansion (multiple countries)

---

# END OF FUNCTIONAL REQUIREMENTS DOCUMENT

**Document Version:** 1.0  
**Last Updated:** December 23, 2025  
**Total Requirements:** 250+  
**Coverage:** Patient (70), Staff (130), System (50+)
