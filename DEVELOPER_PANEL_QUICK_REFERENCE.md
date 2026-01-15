# Developer Panel - Quick Reference

## 🚀 Quick Start

### Developer Login
```
URL: http://localhost:8000/login (same as staff)
Email: your-dev-email@example.com
Password: your-password
Auto-redirects to: /developer/dashboard
```

### After Login
Access to:
- **Dashboard**: `/developer/dashboard` - Overview & stats
- **Activity Logs**: `/developer/activity-logs` - System audit trail
- **API Test**: `/developer/api-test` - Test endpoints
- **System Info**: `/developer/system-info` - Configuration details
- **Database**: `/developer/database` - DB management tools

---

## 📋 User Roles

| Role | Can Access | Login At |
|------|-----------|----------|
| **staff** | Staff panel only | `/login` |
| **developer** | Developer panel | `/developer/login` |
| **admin** | Both (if role=admin) | `/login` |
| **patient** | Public pages | `/login` |

---

## 🔐 Assign Developer Role

### Option 1: Database
```sql
UPDATE users SET role = 'developer' WHERE email = 'dev@example.com';
```

### Option 2: Laravel Tinker
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->update(['role' => 'developer']);
```

---

## 📍 Key URLs

| Page | URL | Note |
|------|-----|------|
| Unified Login | `/login` | Single login for all roles |
| Developer Dashboard | `/developer/dashboard` | Auto-redirect after login |
| Activity Logs | `/developer/activity-logs` | View system audit trail |
| Log Details | `/developer/activity-logs/{id}` | Detailed log info |
| API Test Tool | `/developer/api-test` | Test endpoints |
| System Info | `/developer/system-info` | Config & versions |
| Database Tools | `/developer/database` | DB management |

---

## 🛠️ Developer Tools

### Activity Logs
**What it does**: Track all system changes  
**Filters**: Action type, Model type, Date range, Search  
**Info**: Timestamps, User, Before/After values

### API Test Tool
**What it does**: Test API endpoints  
**Methods**: GET, POST, PUT, PATCH, DELETE  
**Features**: Request body, Response timing, JSON formatting

### System Info
**What it does**: View system configuration  
**Shows**: App version, PHP version, Database, Health status

### Database Tools
**What it does**: Database maintenance  
**Tools**: Cache clear, Config clear, Optimization

---

## 🔄 Navigation

### From Staff Panel
Staff users see: No developer tools  
Developers see: "Developer Tools" link (opens in new tab)

### From Developer Panel
Sidebar options:
- Overview
- Activity Logs
- API Test
- System Info
- Database
- Back to Staff Panel

---

## 🧪 Test API Endpoints

### Quick Links Available
1. `GET /api/queue/status` - Queue information
2. `GET /api/rooms/status` - Room status
3. `GET /api/queue/stats` - Queue statistics

### Custom Endpoint
- Select method (GET/POST/PUT/PATCH/DELETE)
- Enter endpoint path
- Add JSON body if needed
- Click "Send Request"
- View response with timing

---

## 📊 Activity Logs Filters

### By Action
- **created** - New record created
- **updated** - Record modified
- **deleted** - Record removed
- **restored** - Soft-deleted record restored

### By Model
- Appointment
- Queue
- Service
- Dentist
- DentistSchedule
- (and all other models)

### By Date
- Select "From Date" to "To Date"
- View logs within range

### Search
- Search by description
- Search by user ID
- Search by model ID

---

## 🚨 Troubleshooting

### "Unauthorized" Error
→ Check user role in database: `SELECT role FROM users WHERE email = '...';`

### Can't Login
→ Verify email and password  
→ Check user exists: `SELECT * FROM users WHERE email = '...';`
→ If developer, ensure role = 'developer'

### Not Redirecting to Developer Dashboard
→ Check user role is 'developer': `SELECT role FROM users WHERE id = 1;`
→ Clear browser cache and re-login

### Can't Access Developer Tools After Login
→ Verify role is exactly 'developer' (case-sensitive)
→ Check role middleware is enabled

---

## 💡 Tips & Tricks

1. **Remember Me**: Checkbox saves login session
2. **Copy Response**: Use "Copy Response" button in API test
3. **Quick Links**: Save frequent endpoints as bookmarks
4. **Pagination**: Activity logs show 50 items per page
5. **Export**: Response can be saved/exported via browser

---

## 🔒 Security Notes

- Always use strong passwords
- Activity logs are audit trail - review regularly
- Developer role is restricted to authorized users only
- API test tool handles CSRF tokens automatically
- All actions are logged for security review

---

## 📞 Support

For issues:
1. Check this quick reference
2. Read full documentation: `DEVELOPER_PANEL_IMPLEMENTATION.md`
3. Review activity logs for errors
4. Check system info for configuration issues

---

**Last Updated**: January 15, 2026  
**Version**: 1.0  
**Status**: Production Ready ✅

