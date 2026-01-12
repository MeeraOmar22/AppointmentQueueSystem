# Queue Automation - Visual Quick Start Guide

## 🎬 System Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    CLINIC QUEUE SYSTEM                      │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  PATIENT JOURNEY                  DENTIST INTERFACE         │
│  ═════════════════                ═══════════════════       │
│                                                              │
│  1️⃣  Patient Checks In                                      │
│      Status: waiting                                        │
│          ↓                                                  │
│  2️⃣  Auto-Called                    👨‍⚕️ Dentist Views:      │
│      Status: checked_in             - Current Patient      │
│      WhatsApp sent                  - Next Patient         │
│          ↓                           - All 24h Appts       │
│  3️⃣  Proceeds to Room                                      │
│      Status: called                                         │
│      Room: Room 1                 👨‍⚕️ ACTION NEEDED:       │
│          ↓                           Click "Complete"      │
│  4️⃣  Treatment Ongoing                                    │
│      Status: in_treatment         👨‍⚕️ OPTIONAL:           │
│          ↓                          Select Room #          │
│  5️⃣  Treatment Done               👨‍⚕️ Click "Complete"    │
│      Status: completed              Button                 │
│      ↓                              ↓                      │
│      LOOP                         Auto-call Next!          │
│      Back to Step 2              (unless paused)          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Status Badge Reference

```
┌──────────┬──────────┬─────────────────────┬────────────────┐
│ Status   │ Color    │ Description         │ Next Action    │
├──────────┼──────────┼─────────────────────┼────────────────┤
│ waiting  │ ⚪ Gray   │ Arrived, not queued │ System calls   │
├──────────┼──────────┼─────────────────────┼────────────────┤
│ checked_ │ 🔵 Blue  │ Checked in, waiting │ Auto-called    │
│ in       │          │ to be called        │ soon           │
├──────────┼──────────┼─────────────────────┼────────────────┤
│ called   │ 🔴 Red   │ Called, proceeding  │ Dentist ready  │
│          │          │ to room             │                │
├──────────┼──────────┼─────────────────────┼────────────────┤
│ in_      │ 🟠 Orange│ In treatment room   │ Mark complete  │
│ treatment│          │                     │ when done      │
├──────────┼──────────┼─────────────────────┼────────────────┤
│ completed│ 🟢 Green │ Treatment finished  │ Complete!      │
│          │          │ Ready to leave      │                │
└──────────┴──────────┴─────────────────────┴────────────────┘
```

---

## 🎮 Dentist Control Panel

### Treatment Completion Page (`/staff/treatment-completion`)

```
╔════════════════════════════════════════════════════════════╗
║  Treatment Completion & Queue Management                  ║
║  Auto-progression system - Dentist only needs click Comp  ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  [🟢 RUNNING] [⏸ Pause Queue]         Status: RUNNING    ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║  🔴 CURRENTLY IN TREATMENT                                 ║
║  ┌─────────────────────────────────────────────────────┐  ║
║  │ Ahmed Ali                           #001            │  ║
║  │ Phone: +60123456789 (WhatsApp link)                │  ║
║  │ Service: General Checkup                           │  ║
║  │ Dentist: Dr. John Smith                            │  ║
║  │ Room: 🔵 Room 1                                     │  ║
║  │ Status: ⬜ In Treatment                             │  ║
║  └─────────────────────────────────────────────────────┘  ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║  ⏳ NEXT PATIENT                                            ║
║  ┌─────────────────────────────────────────────────────┐  ║
║  │ Fatima Hassan                       #002            │  ║
║  │ Phone: +60123456799 (WhatsApp link)                │  ║
║  │ Service: Teeth Cleaning                            │  ║
║  │ Status: 🔴 CALLED - PROCEED TO ROOM                │  ║
║  └─────────────────────────────────────────────────────┘  ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║  📋 ALL TODAY'S APPOINTMENTS                                ║
║  ┌────┬───────────┬──────┬──────┬───────┬──────┬────────┐ ║
║  │Pat │ Time      │Phone │Svce  │Dentst │State │ Room   │ ║
║  ├────┼───────────┼──────┼──────┼───────┼──────┼────────┤ ║
║  │#001│ 09:00 AM  │ 🟢   │Chckup│Dr.J   │ 🟠In │ Room 1 │ ║
║  │    │           │      │      │       │      │ [✓End] │ ║
║  ├────┼───────────┼──────┼──────┼───────┼──────┼────────┤ ║
║  │#002│ 09:30 AM  │ 🟢   │Clean │Dr.J   │ 🔴Cal│ Waiting│ ║
║  │    │           │      │      │       │      │ [Waiting]║
║  ├────┼───────────┼──────┼──────┼───────┼──────┼────────┤ ║
║  │#003│ 10:00 AM  │ 🟢   │Crown │Dr.S   │ 🔵In │ -      │ ║
║  │    │           │      │      │       │      │ [Waiting]║
║  ├────┼───────────┼──────┼──────┼───────┼──────┼────────┤ ║
║  │#004│ 10:30 AM  │ 🟢   │Filling│Dr.S  │ ⚪Wait│ -     │ ║
║  │    │           │      │      │       │      │ [Waiting]║
║  └────┴───────────┴──────┴──────┴───────┴──────┴────────┘ ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
```

### What Dentist Does:

```
STEP 1: ✅ Patient finishes treatment
         ↓
STEP 2: 👆 Click [✓ Complete] Button
         ↓
STEP 3: (Optional) Select Room from dropdown
         📍 Room 1
         📍 Room 2
         📍 Room 3
         ↓
STEP 4: 👆 Click "Mark Completed"
         ↓
SYSTEM AUTOMATICALLY:
✅ Marks patient as completed
✅ Calls next patient (if not paused)
✅ Sends WhatsApp: "Your turn! Please proceed to Room X"
✅ Updates TV display
✅ Logs activity
         ↓
STEP 5: 👀 See next patient appear in "Currently In Treatment"
         ↓
REPEAT! 🔄
```

---

## 📺 Waiting Area TV Display

### `/public/waiting-area`

```
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║           🏥 Welcome to Our Clinic                        ║
║  Please wait for your queue number to be called           ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║                 🔴 NOW BEING CALLED                        ║
║                                                            ║
║            ╔─────────────────────────────────╗            ║
║            │                                 │            ║
║            │             #001                │            ║
║            │                                 │            ║
║            │         Ahmed Ali               │            ║
║            │      Service: General Checkup   │            ║
║            │      📍 Room 1                  │            ║
║            │                                 │            ║
║            └─────────────────────────────────┘            ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║           ⏳ PATIENTS WAITING (3)                          ║
║                                                            ║
║  ┌──────────────────────────────────────────────────┐    ║
║  │ #002  │ Fatima Hassan        │ Waiting           │    ║
║  │ #003  │ Hassan Mohammed      │ Waiting           │    ║
║  │ #004  │ Leila Ibrahim        │ Waiting           │    ║
║  └──────────────────────────────────────────────────┘    ║
║                                                            ║
║         (Auto-updates every 3 seconds)                    ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
```

---

## ⏸️ Pause/Resume Workflow

```
SCENARIO: Dentist needs a 15-minute break

1️⃣  BEFORE BREAK:
    👨‍⚕️ "I need a break"
         ↓
    👆 Click [⏸ PAUSE QUEUE] Button
         ↓
    ✅ Queue paused (🔴 PAUSED badge shows)
    ✅ Current patient finishes normally
    ✅ Next patient NOT called automatically
    ✅ TV display shows "⏸ Queue is Paused"

2️⃣  DURING BREAK:
    ☕ Dentist takes 15-minute break
    📺 TV shows: "Queue is Paused - Please wait"

3️⃣  AFTER BREAK:
    👨‍⚕️ "Ready to work again"
         ↓
    👆 Click [🟢 RESUME QUEUE] Button
         ↓
    ✅ Queue resumed (🟢 RUNNING badge shows)
    ✅ Next waiting patient auto-called
    ✅ WhatsApp sent: "Your turn! Please proceed to Room X"
    ✅ TV display updated with next patient

RESULT: Smooth pause/resume with no manual calling needed!
```

---

## 🔔 WhatsApp Message Examples

### When Patient is Called:

```
📱 Patient's Phone (WhatsApp):

"Your turn! Please proceed to Room 1. Thank you!"

OR (if no room assigned):

"Your turn! Please proceed to Waiting Area. Thank you!"
```

---

## 🎯 Key Concepts

### **Auto-Calling**
- When previous patient's treatment is marked complete
- System automatically finds next "checked_in" patient
- Changes status to "called"
- Sends WhatsApp notification
- Updates TV display
- **Unless**: Queue is paused ⏸

### **Pause Queue**
- Stops auto-calling new patients
- Current patient finishes normally
- Perfect for: breaks, lunch, emergencies
- Next patient waits (not called yet)
- TV shows pause status

### **Resume Queue**
- Restarts auto-calling
- First waiting patient auto-called
- Normal flow continues
- TV display updates

### **Room Assignment**
- Optional (can skip)
- Select from dropdown when marking complete
- Room # sent to patient via WhatsApp
- TV display shows room
- Helps direct patient to correct location

---

## 🚨 Status Code Quick Reference

**In Code/Database:**

```
waiting         = Patient arrived but not in queue yet
checked_in      = Checked in, waiting to be called
called          = Called, proceeding to room (RED in UI)
in_treatment    = In treatment room (ORANGE in UI)
completed       = Treatment finished (GREEN in UI)
```

---

## 📊 Queue Progression Example

```
TIME    PATIENT    STATUS              ACTION
────────────────────────────────────────────────────
09:00   Ahmed      waiting             ← Arrives
09:02   Ahmed      checked_in          ← Staff checks in
09:05   Ahmed      called              ← Auto-called
09:07   Fatima     checked_in          ← Next arrives
09:10   Ahmed      in_treatment        ← Enters room
09:12   Fatima     called              ← Auto-called
10:15   Ahmed      completed           ← Dentist clicks Complete
                                       ← Fatima auto-called
10:17   Hassan     checked_in          ← Arrives while treatment on
10:18   Fatima     in_treatment        ← Enters room
```

---

## 🎬 Daily Workflow

```
MORNING:
✅ Staff opens `/staff/treatment-completion`
✅ Queue status shows: 🟢 RUNNING
✅ TV display opens: `/public/waiting-area`

DURING DAY:
✅ Patients check in → auto move to "checked_in" status
✅ Dentist sees patients in table
✅ When patient done → Click "Complete" button
✅ Select room (optional)
✅ Next patient auto-called
✅ System handles everything else

LUNCH BREAK:
✅ Dentist clicks "⏸ Pause Queue"
✅ No new patients called
✅ After lunch: Click "🟢 Resume Queue"
✅ Back to normal flow

EVENING:
✅ Last patient completed
✅ Queue shows "All patients completed!"
✅ Safe to close system
```

---

## 📍 URLs to Bookmark

```
Dentist Treatment Page:
→ http://localhost:8000/staff/treatment-completion

Waiting Area TV Display:
→ http://localhost:8000/public/waiting-area

Queue API Status (for developers):
→ http://localhost:8000/api/queue/status
```

---

## ✨ Highlights

✅ **Minimal Clicks**: Only 1 click per patient (Complete)
✅ **Automatic Flow**: Patients progress automatically
✅ **Flexible**: Pause/resume when needed
✅ **Patient-Friendly**: WhatsApp notifications & TV display
✅ **Room Management**: Track which room patient is in
✅ **Logged**: All actions recorded for audit trail
✅ **Scalable**: Works for any number of patients/rooms

---

**Status**: 🟢 Ready to Use
**Version**: 1.0
**Last Updated**: January 13, 2026

