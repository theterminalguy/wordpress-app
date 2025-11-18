=== WP Booking Plugin ===
Contributors: yourname
Tags: booking, appointments, calendar, scheduling, calendly
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Calendly-like booking system for WordPress with admin approval workflow.

== Description ==

WP Booking Plugin is a simple yet powerful booking system that allows your visitors to book appointments directly from your website. All bookings require admin approval, giving you full control over your schedule.

**Features:**

* Easy-to-use booking form with calendar picker
* Admin panel to configure available time slots
* Email notifications for bookings and approvals
* Admin approval workflow (approve/reject bookings)
* Responsive design for mobile devices
* Translation ready (i18n support)
* Clean, modern interface

**How It Works:**

1. Add the `[wp_booking_form]` shortcode to any page
2. Visitors select a date and available time slot
3. Visitors fill in their contact details
4. Admin receives email notification
5. Admin approves or rejects the booking
6. Visitor receives confirmation or rejection email

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/wp-booking-plugin/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Bookings → Settings to configure your time slots
4. Add the shortcode `[wp_booking_form]` to any page or post

== Frequently Asked Questions ==

= How do I display the booking form? =

Add the shortcode `[wp_booking_form]` to any page or post where you want the booking form to appear.

= How do I configure available time slots? =

Go to Bookings → Settings in your WordPress admin. You can set:
- Time slot duration
- Start and end times
- Available days of the week
- Minimum booking notice period

= Can I customize the email templates? =

The email templates are currently built-in. You can modify them by editing the `/includes/class-wp-booking-email.php` file.

= How do I approve/reject bookings? =

Go to Bookings in your WordPress admin. Click on a booking to view details, then use the "Approve" or "Reject" buttons.

= Can customers book the same time slot? =

No, each time slot can only be booked once. Once approved, that slot is no longer available.

= What email notifications are sent? =

Four types of emails are sent:
1. Booking confirmation to customer (when submitted)
2. New booking notification to admin
3. Approval email to customer
4. Rejection email to customer

== Screenshots ==

1. Booking form - Date selection
2. Booking form - Time slot selection
3. Booking form - Contact details
4. Admin booking list
5. Admin settings page
6. Booking details in admin

== Changelog ==

= 1.0.0 =
* Initial release
* Date and time slot selection
* Admin approval workflow
* Email notifications
* Responsive design
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of WP Booking Plugin.

== Usage ==

After activating the plugin:

1. **Configure Settings**
   - Go to Bookings → Settings
   - Set your available hours (e.g., 9:00 AM - 5:00 PM)
   - Choose time slot duration (15, 30, 45, 60, 90, or 120 minutes)
   - Select which days are available for bookings
   - Set minimum booking notice (hours in advance)
   - Enter admin email for notifications

2. **Add Booking Form**
   - Create or edit a page
   - Add the shortcode: `[wp_booking_form]`
   - Publish the page

3. **Manage Bookings**
   - Go to Bookings in admin menu
   - View all pending, approved, and rejected bookings
   - Click on a booking to view details
   - Approve or reject bookings

== Support ==

For support and feature requests, please visit the plugin support forum.

== Privacy Policy ==

This plugin stores booking information including:
- Customer name
- Customer email
- Customer phone number
- Booking date and time
- Optional message/notes

This information is stored in your WordPress database and is only used for managing bookings and sending notifications.
