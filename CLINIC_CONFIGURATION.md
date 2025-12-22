# Clinic Location Configuration

## Setup Instructions

To configure which clinic this system serves, set the `clinic.location` in your configuration:

### Option 1: Environment Variable (Recommended)
Add to your `.env` file:
```
CLINIC_LOCATION=seremban
```

Then in your config or controller, use:
```php
config('clinic.location')
```

### Option 2: Config File
Create or update `config/clinic.php`:
```php
<?php

return [
    'location' => env('CLINIC_LOCATION', 'seremban'),
];
```

### Deployment
- **Seremban Clinic:** Set `CLINIC_LOCATION=seremban`
- **Kuala Pilah Clinic:** Set `CLINIC_LOCATION=kuala_pilah`

## How It Works

The treatment room management system is now **clinic-specific**:

✅ **Staff see only their clinic's rooms**
- List view filters by configured location
- Create form auto-assigns new rooms to clinic
- Edit form shows clinic location (read-only)
- Statistics show only clinic-specific data

✅ **Queue system uses clinic-specific rooms**
- Queue assignment queries only available rooms for clinic
- Automatic patient-to-room assignment
- No cross-clinic conflicts

✅ **Easy deployment**
- Single `.env` variable controls clinic location
- No code changes needed to switch clinics
- Each clinic instance has independent data

## Example Setup

### For Seremban Clinic
```env
CLINIC_LOCATION=seremban
```
Staff will see only Seremban clinic's rooms.

### For Kuala Pilah Clinic (Separate Deployment)
```env
CLINIC_LOCATION=kuala_pilah
```
Staff will see only Kuala Pilah clinic's rooms.

---

**Note:** Each clinic should have its own separate application instance/deployment. This configuration ensures each instance manages its own resources independently.
