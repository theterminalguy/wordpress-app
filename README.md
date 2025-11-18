# WordPress Booking Plugin MVP

A Calendly-like booking system for WordPress with admin approval workflow.

## Features

- **Admin Panel** - Configure available time slots with weekly schedule
- **Public Booking Form** - Clean, modern interface with calendar picker
- **Email Notifications** - Automatic emails for booking confirmations and approvals
- **Admin Approval Workflow** - Review and approve/reject bookings
- **Custom Post Type** - Bookings stored as WordPress custom posts
- **Responsive Design** - Mobile-friendly interface
- **Translation Ready** - i18n support for multiple languages

## Quick Start

### 1. Start Docker Environment

```bash
make up
```

This will start:
- WordPress at http://localhost:8080
- phpMyAdmin at http://localhost:8081
- MySQL database

### 2. Install WordPress

Wait about 30 seconds for the containers to fully start, then:

1. Open your browser and go to http://localhost:8080
2. Complete the famous 5-minute WordPress installation
3. Choose any site title, username, and password you prefer

### 3. Activate Plugin

After logging into WordPress admin:

1. Go to **Plugins** → **Installed Plugins**
2. Find **WP Booking Plugin**
3. Click **Activate**

### 4. Configure Settings

1. Go to **Bookings → Settings** in WordPress admin
2. Configure your available time slots:
   - Time slot duration (15, 30, 45, 60, 90, or 120 minutes)
   - Start time (e.g., 09:00)
   - End time (e.g., 17:00)
   - Available days (select which days of the week)
   - Minimum booking notice (hours in advance)
   - Admin notification email

### 4. Add Booking Form to a Page

Create a new page (Pages → Add New) and add the shortcode:

```
[wp_booking_form]
```

Publish the page and view it to see the booking form in action.

## Makefile Commands

| Command | Description |
|---------|-------------|
| `make up` | Start all services |
| `make down` | Stop all services |
| `make logs` | View all logs (follow mode) |
| `make logs-wp` | View WordPress logs only |
| `make logs-db` | View database logs only |
| `make clean` | Remove all containers and volumes |
| `make restart` | Restart all services |
| `make status` | Show status of all services |
| `make shell` | Open bash in WordPress container |
| `make db-shell` | Open MySQL shell |
| `make check` | Check WordPress status and get installation URL |
| `make help` | Show help message |

## How It Works

### Booking Flow (User)

1. **Select Date** - User picks a date from the calendar
2. **Select Time** - Available time slots are shown
3. **Enter Details** - User fills in name, email, phone, and optional message
4. **Submit** - Booking is submitted with "Pending Approval" status
5. **Receive Email** - User gets confirmation email

### Approval Flow (Admin)

1. **Notification** - Admin receives email with booking details
2. **Review** - Admin reviews booking in WordPress admin
3. **Approve/Reject** - Admin approves or rejects the booking
4. **User Notified** - User receives approval or rejection email

## Database Credentials

- **Database:** `wordpress`
- **Username:** `wordpress`
- **Password:** `wordpress`
- **Root Password:** `rootpassword`

## Plugin Structure

```
wp-booking-plugin/
├── wp-booking-plugin.php     # Main plugin file
├── includes/                   # Core functionality
│   ├── class-wp-booking.php
│   ├── class-wp-booking-activator.php
│   ├── class-wp-booking-deactivator.php
│   ├── class-wp-booking-loader.php
│   ├── class-wp-booking-i18n.php
│   ├── class-wp-booking-post-type.php
│   └── class-wp-booking-email.php
├── admin/                      # Admin functionality
│   ├── class-wp-booking-admin.php
│   └── class-wp-booking-settings.php
├── public/                     # Public functionality
│   ├── class-wp-booking-public.php
│   └── class-wp-booking-form.php
├── assets/                     # CSS and JS files
│   ├── css/
│   │   ├── admin.css
│   │   └── public.css
│   └── js/
│       ├── admin.js
│       └── public.js
└── languages/                  # Translation files
```

## Custom Post Type

Bookings are stored as `wp_booking` custom post type with the following meta fields:

- `_booking_name` - Customer name
- `_booking_email` - Customer email
- `_booking_phone` - Customer phone
- `_booking_date` - Booking date (Y-m-d)
- `_booking_time` - Booking time (H:i)
- `_booking_message` - Customer message/notes

## Custom Post Statuses

- `pending_approval` - Awaiting admin approval
- `approved` - Approved by admin
- `rejected` - Rejected by admin

## Email Templates

The plugin sends HTML emails for:

1. **Booking Confirmation** - Sent to user when booking is submitted
2. **Admin Notification** - Sent to admin when new booking is received
3. **Approval Email** - Sent to user when booking is approved
4. **Rejection Email** - Sent to user when booking is rejected

## Customization

### Change Time Slot Duration

Go to **Bookings → Settings** and select from:
- 15 minutes
- 30 minutes (default)
- 45 minutes
- 1 hour
- 1.5 hours
- 2 hours

### Modify Available Days

In settings, check/uncheck days of the week for availability.

### Adjust Booking Buffer

Set minimum hours in advance that bookings must be made (default: 24 hours).

## Development

### Making Changes to the Plugin

The plugin files are mounted from `./wp-booking-plugin/` to the WordPress container. Any changes you make locally will be reflected immediately.

### Viewing Logs

```bash
# All logs
make logs

# WordPress only
make logs-wp

# Database only
make logs-db
```

### Accessing Database

```bash
# Via phpMyAdmin
Open http://localhost:8081

# Via MySQL CLI
make db-shell
```

### Accessing WordPress Container

```bash
make shell
```

## Troubleshooting

### Plugin Not Showing

1. Check if Docker containers are running: `make status`
2. Verify plugin is activated: `make activate-plugin`
3. Check WordPress admin → Plugins

### Booking Form Not Displaying

1. Make sure you've added the shortcode: `[wp_booking_form]`
2. Check browser console for JavaScript errors
3. Verify settings are configured in **Bookings → Settings**

### Emails Not Sending

1. WordPress in Docker uses PHP's `mail()` function which may not work
2. For production, install an SMTP plugin like WP Mail SMTP
3. For development, check logs: `make logs-wp`

### Time Slots Not Showing

1. Verify date is not in the past
2. Check booking buffer setting
3. Ensure selected day is in available days
4. Check start/end times in settings

## Production Deployment

Before deploying to production:

1. Change all default passwords
2. Configure proper SMTP for email delivery
3. Enable SSL/HTTPS
4. Set proper file permissions
5. Configure backups
6. Update plugin author information

## WordPress Coding Standards

This plugin follows WordPress coding standards:
- Proper sanitization and validation
- Nonce verification for security
- Translation-ready (i18n)
- WordPress hooks and filters
- Custom post types and meta
- AJAX best practices

## License

GPL v2 or later

## Support

For issues and questions, check the WordPress admin or review the code structure.
