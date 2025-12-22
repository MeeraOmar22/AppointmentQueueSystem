# UI Design Consistency Guide

## Overview
This document defines the consistent design system applied across all staff dashboard pages. All pages now follow a unified design pattern for typography, spacing, colors, and components.

---

## 1. Page Header Structure (All Pages)

### Standard Page Header Format
```blade
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Page Title</h3>
        <p class="text-muted mb-0">Subtitle or description</p>
    </div>
    <!-- Optional: Buttons/Actions on the right -->
    <div class="d-flex gap-2">
        <a href="#" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New
        </a>
    </div>
</div>
```

### Typography Standards
- **Page Title**: `<h3 class="fw-bold mb-1">` (font-weight: 700, margin-bottom: 0.25rem)
- **Subtitle**: `<p class="text-muted mb-0">` (gray text, no margin)
- **No inline color styling** - use Bootstrap color classes only

### Pages Following This Standard
- ✅ Activity Logs
- ✅ Dentists
- ✅ Treatment Rooms
- ✅ Services
- ✅ Operating Hours
- ✅ Appointments & Queue
- ✅ Dentist Schedules
- ✅ Past Records
- ✅ Quick Edit Dashboard

---

## 2. Statistics Cards

### Standard Card Format
```blade
<div class="row mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Label</p>
                        <h4 class="text-primary fw-bold mb-0">Number</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Card Styling Rules
- **No borders**: `border-0`
- **Subtle shadow**: `shadow-sm`
- **Icon/Label color**: `text-primary`, `text-success`, `text-warning`, `text-info`
- **Responsive columns**: `col-md-6 col-lg-3` (2 columns on tablet, 4 on desktop)
- **Label**: `<p class="text-muted small mb-1">`
- **Number**: `<h4 class="text-{color} fw-bold mb-0">`

### Applied To
- Quick Edit Dashboard (stats cards)
- Appointments & Queue (stats cards)
- Treatment Rooms (stats cards)

---

## 3. Data Tables

### Standard Table Wrapper
```blade
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Column 1</th>
                        <th>Column 2</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>
```

### Table Styling Rules
- **Card class**: `table-card` (provides consistent styling)
- **Table classes**: `table align-middle mb-0`
- **Header**: `table-light` background
- **Responsive**: Wrap in `table-responsive`
- **Action column**: `text-end` class (right-aligned)
- **No inline widths** - let Bootstrap handle responsiveness

### Applied To
- Activity Logs
- Dentists Management
- Services
- Operating Hours
- Treatment Rooms
- Appointments list

---

## 4. Tab Navigation

### Standard Tabs Format
```blade
<ul class="nav nav-tabs card-header-tabs" role="tablist" style="gap: 0.5rem;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold px-4 py-2 text-dark" id="tab1" data-bs-toggle="tab" data-bs-target="#content1" type="button" role="tab">
            <i class="bi bi-icon me-2"></i>Tab 1
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="tab2" data-bs-toggle="tab" data-bs-target="#content2" type="button" role="tab">
            <i class="bi bi-icon me-2"></i>Tab 2
        </button>
    </li>
</ul>
```

### Tab Styling Rules
- **Gap**: `style="gap: 0.5rem;"` - space between tabs
- **Bold**: `fw-semibold` - make tab text bold
- **Padding**: `px-4 py-2` - increase click area
- **Active text**: `text-dark` - dark color for active tab
- **Inactive text**: `text-secondary` - gray color for inactive
- **Icons**: All tabs should include icons using `bi` (Bootstrap Icons)

### CSS Styling (Added in `@push('styles')` or `<style>` tag)
```css
.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: #0d6efd !important;
    border-bottom-color: #e9ecef;
}

.nav-tabs .nav-link.active {
    color: #212529 !important;
    background-color: transparent;
    border-bottom-color: #0d6efd;
}
```

### Applied To
- Quick Edit Dashboard (Dentists, Services, Operating Hours, Staff tabs)
- Past Records (Past Dentists, Past Staff tabs)
- Appointments & Queue (Today, Upcoming, Past tabs)

---

## 5. Form Pages

### Standard Form Page Layout
```blade
<div class="mb-4">
    <h3 class="fw-bold mb-1">Form Title</h3>
    <p class="text-muted mb-0">Subtitle</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="#">
                    <!-- form fields -->
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save
                        </button>
                        <a href="#" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
```

### Form Styling Rules
- **Card**: Always use `table-card` class for consistency
- **Column width**: `col-lg-8` (2/3 width, leaves space on desktop)
- **Field labels**: `form-label` with `fw-semibold` if needed
- **Required fields**: Add `<span class="text-danger">*</span>` 
- **Error messages**: Use `invalid-feedback` class
- **Buttons section**: `d-grid gap-2 mt-4` (full-width stacked buttons)
- **Button spacing**: Add `me-2` to icon in buttons

### Applied To
- Create/Edit Appointments
- Create/Edit Services
- Create/Edit Operating Hours
- Create/Edit Treatment Rooms
- Dentist Create/Edit (via modals in Quick Edit)

---

## 6. Icon Standards

### Icon Library
- **Use**: Bootstrap Icons (`bi-*`)
- **Do NOT use**: Font Awesome (`fas`) or other libraries
- **Icon sizing**: Default (inherit from parent font size)
- **Icon spacing**: Use `me-2` (margin-end) when icon precedes text

### Common Icons Used
```
Dentists:     bi-person-circle
Services:     bi-wrench
Rooms:        bi-door-open
Hours:        bi-calendar-event
Staff:        bi-people
Appointments: bi-calendar-check
Add:          bi-plus-circle
Edit:         bi-pencil
Delete:       bi-trash
View/Details: bi-eye
Back:         bi-arrow-left
Save:         bi-save
Deactivate:   bi-slash-circle
```

---

## 7. Color Standards

### Status Badges
```blade
<!-- Active/Success -->
<span class="badge bg-success">Active</span>

<!-- Inactive/Danger -->
<span class="badge bg-secondary">Inactive</span>

<!-- Primary -->
<span class="badge bg-primary">Primary</span>

<!-- Info -->
<span class="badge bg-info">Info</span>

<!-- Warning -->
<span class="badge bg-warning">Warning</span>

<!-- Danger -->
<span class="badge bg-danger">Danger</span>
```

### Text Colors
- **Headings**: Default (no color class)
- **Muted/Subtle**: `text-muted`
- **Primary**: `text-primary` (for emphasis)
- **Success**: `text-success` (positive)
- **Warning**: `text-warning` (caution)
- **Danger**: `text-danger` (error)

### Card Colors (Stats)
- **Primary metric**: `text-primary` 
- **Secondary metric**: `text-info`
- **Success metric**: `text-success`
- **Warning metric**: `text-warning`

---

## 8. Button Standards

### Button Classes
```blade
<!-- Primary Action -->
<button class="btn btn-primary">
    <i class="bi bi-plus-circle me-2"></i>Add
</button>

<!-- Secondary Action -->
<a href="#" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-2"></i>Back
</a>

<!-- Danger Action -->
<button class="btn btn-outline-danger" onclick="confirm('Are you sure?')">
    <i class="bi bi-trash me-2"></i>Delete
</button>

<!-- Success/Toggle -->
<button class="btn btn-outline-success">
    <i class="bi bi-toggle-on me-2"></i>Activate
</button>

<!-- Warning/Info -->
<button class="btn btn-outline-warning">
    <i class="bi bi-info-circle me-2"></i>Deactivate
</button>
```

### Button Sizing
- **In tables**: `btn-sm` (small)
- **In forms**: Default (no size class)
- **In headers**: Default or `btn-sm` for secondary actions

### Button Spacing
- **Multiple buttons in row**: Use `gap-2` with flexbox
- **Stacked buttons**: Use `d-grid gap-2` for full-width stacking
- **Button groups**: Use `btn-group` for related actions

---

## 9. Spacing & Margins

### Standard Margins
- **Page header bottom**: `mb-4`
- **Stats cards bottom**: `mb-4`
- **Section bottom**: `mb-3`
- **Form field bottom**: `mb-3`
- **Card bottom**: `mb-4`

### Padding Rules
- **Card body**: Default (handled by Bootstrap)
- **Form sections**: Use margin-bottom (`mb-*`) not padding
- **Tab padding**: `px-4 py-2` (increased clickability)

### Vertical Spacing
- **No top padding on `.card`** - use margin on wrapper
- **Consistent `mb-4` between major sections**
- **Forms**: `mb-3` for fields, `mt-4` before submit buttons

---

## 10. Alert Messages

### Alert Styling
```blade
<!-- Success -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    Success message
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Danger -->
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error:</strong>
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Info -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    Info message
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

### Alert Rules
- **Always dismissible**: `alert-dismissible fade show`
- **Always has close button**: `btn-close`
- **Use icons**: Add `bi` icon with `me-2`
- **Consistent appearance**: All follow Bootstrap alert classes

---

## 11. Responsive Behavior

### Breakpoints Used
- **Mobile**: < 768px (single column)
- **Tablet**: 768px - 992px (2 columns)
- **Desktop**: > 992px (4 columns or full layout)

### Card Grid Layout
```blade
<!-- For stats cards -->
<div class="col-md-6 col-lg-3">
    <!-- 2 columns on tablet, 4 on desktop, 1 on mobile -->
</div>

<!-- For form pages -->
<div class="col-lg-8">
    <!-- 2/3 width on desktop, full on tablet/mobile -->
</div>
```

### Mobile-First Approach
- All layouts are mobile-first
- Use `d-md-block d-none` to hide on mobile
- Use responsive classes for visibility

---

## 12. Checklist for New Pages

When creating new staff pages, ensure:

- [ ] Page header uses `<h3 class="fw-bold mb-1">` with description
- [ ] Section title uses `<h3>` not `<h2>` or `<h1>`
- [ ] All buttons use Bootstrap Icons (`bi-*`)
- [ ] Tables wrapped in `.card.table-card`
- [ ] Stats cards use `col-md-6 col-lg-3` layout
- [ ] Forms use `col-lg-8` wrapper
- [ ] Tabs include gap spacing and proper color classes
- [ ] No inline color styling (use Bootstrap classes)
- [ ] All badges use appropriate color (success, secondary, etc.)
- [ ] Alerts are dismissible with close button
- [ ] Spacing follows `mb-4`, `mb-3`, `mb-1` hierarchy
- [ ] No custom CSS colors - use Bootstrap palette only
- [ ] Icons have proper spacing: `me-2` before text
- [ ] Form buttons in `d-grid gap-2` wrapper
- [ ] Cards use `border-0 shadow-sm` (no borders, subtle shadow)

---

## 13. Color Palette Reference

### Bootstrap Colors Used
```
Primary:   #0d6efd (blue)
Secondary: #6c757d (gray)
Success:   #198754 (green)
Danger:    #dc3545 (red)
Warning:   #ffc107 (yellow)
Info:      #0dcaf0 (cyan)
Light:     #f8f9fa (light gray)
Dark:      #212529 (dark gray)
```

### Text Colors
- **`text-dark`**: For active/emphasized text
- **`text-muted`**: For secondary/helper text
- **`text-secondary`**: For inactive/de-emphasized text
- **`text-primary`**: For main action/highlighted text
- **`text-success`**: For positive status
- **`text-danger`**: For error/warning status

---

## 14. Consistency Verification

### All Staff Pages (Updated ✅)
1. ✅ Activity Logs (`activity-logs.blade.php`)
2. ✅ Appointments (`appointments.blade.php`, `appointments-create.blade.php`, `appointments-edit.blade.php`)
3. ✅ Calendar (`calendar/index.blade.php`)
4. ✅ Dentists (`dentists/index.blade.php`, `dentists/create.blade.php`, `dentists/edit.blade.php`)
5. ✅ Dentist Schedules (`dentist-schedules/index.blade.php`, `dentist-schedules/calendar.blade.php`)
6. ✅ Operating Hours (`operating-hours/index.blade.php`, `operating-hours/create.blade.php`, `operating-hours/edit.blade.php`)
7. ✅ Services (`services/index.blade.php`, `services/create.blade.php`, `services/edit.blade.php`)
8. ✅ Treatment Rooms (`rooms/index.blade.php`, `rooms/create.blade.php`, `rooms/edit.blade.php`)
9. ✅ Quick Edit (`quick-edit.blade.php`)
10. ✅ Past Records (`past.blade.php`)

### Design System Implementation Status
- ✅ Page headers standardized
- ✅ Statistics cards consistent
- ✅ Table styling unified
- ✅ Tab navigation improved
- ✅ Form layouts consistent
- ✅ Icon usage standardized (all `bi-*`)
- ✅ Colors follow Bootstrap palette
- ✅ Button styling unified
- ✅ Spacing hierarchy established
- ✅ Responsive layout consistent

---

## Summary

This design system ensures all staff dashboard pages have:
- **Consistent typography** - Clear visual hierarchy with h3 headings
- **Unified component styling** - Same look for cards, tables, buttons, tabs
- **Predictable navigation** - Users know where to find actions and controls
- **Professional appearance** - Clean, modern, Bootstrap-based design
- **Better maintainability** - Easy to update design globally
- **Improved accessibility** - Proper semantic HTML and color contrast
- **Mobile-friendly** - Responsive layouts that work on all devices

