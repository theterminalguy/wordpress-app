/**
 * WP Booking Plugin - Admin JavaScript
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Add any admin-specific JavaScript here

		// Confirm before rejecting a booking
		$('a[href*="action=reject_booking"]').on('click', function(e) {
			if (!confirm('Are you sure you want to reject this booking?')) {
				e.preventDefault();
				return false;
			}
		});

		// Show success/error notices with fade out
		setTimeout(function() {
			$('.notice.is-dismissible').fadeOut();
		}, 5000);
	});

})(jQuery);
