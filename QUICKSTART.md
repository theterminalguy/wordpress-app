# Quick Start Guide

## Getting Started in 5 Minutes

### 1. Start the Environment

```bash
make up
```

Wait 30 seconds for services to initialize.

### 2. Access WordPress

Open http://localhost:8080 in your browser and complete the WordPress installation:
- Choose a site title
- Create an admin username and password
- Enter your email
- Click "Install WordPress"

### 3. Activate the Plugin

1. Log in to WordPress admin
2. Go to **Plugins** → **Installed Plugins**
3. Find **WP Booking Plugin** and click **Activate**

### 4. Configure Settings

1. In WordPress admin, go to **Bookings** → **Settings**
2. Configure your availability:
   - **Time Slot Duration**: 30 minutes (recommended)
   - **Start Time**: 09:00
   - **End Time**: 17:00
   - **Available Days**: Check Monday through Friday
   - **Minimum Booking Notice**: 24 hours
   - **Admin Email**: Your email for notifications
3. Click **Save Changes**

### 5. Create a Booking Page

1. Go to **Pages** → **Add New**
2. Title: "Book an Appointment"
3. Add the shortcode: `[wp_booking_form]`
4. Click **Publish**
5. Click **View Page** to see your booking form!

## Testing the Booking Flow

### As a Customer:

1. Visit your booking page
2. Select a date from the calendar
3. Choose an available time slot
4. Fill in your details:
   - Name
   - Email
   - Phone
   - Optional message
5. Click "Submit Booking"
6. Check your email for confirmation

### As an Admin:

1. Check your email for new booking notification
2. Go to **Bookings** → **All Bookings** in WordPress admin
3. Click on a booking to view details
4. Click **Approve Booking** or **Reject Booking**
5. The customer will receive an email with your decision

## Accessing Services

- **WordPress**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Server: `db`
  - Username: `wordpress`
  - Password: `wordpress`

## Common Commands

```bash
# View logs
make logs

# Stop services
make down

# Restart services
make restart

# Remove everything and start fresh
make clean
make up
```

## Next Steps

- Customize the email templates in `/wp-booking-plugin/includes/class-wp-booking-email.php`
- Modify the styling in `/wp-booking-plugin/assets/css/public.css`
- Adjust time slots and availability in **Bookings → Settings**
- Add the booking form to multiple pages using the `[wp_booking_form]` shortcode

## Troubleshooting

**Can't see the booking form?**
- Make sure the plugin is activated
- Verify you added the shortcode: `[wp_booking_form]`
- Check browser console for JavaScript errors

**No time slots showing?**
- Verify settings are configured in **Bookings → Settings**
- Make sure the selected date is not in the past
- Check that the day is in your available days
- Ensure you're outside the minimum booking notice period

**Emails not being sent?**
- Docker WordPress uses PHP's mail() which may not work
- For production, install an SMTP plugin like "WP Mail SMTP"
- For development, check emails are being queued in the logs

## Support

For issues or questions, check the main README.md file or review the code structure.

---

**Enjoy your new booking system! 🎉**
