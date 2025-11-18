/**
 * WP Booking Plugin - Public JavaScript
 */

(function($) {
	'use strict';

	let selectedDate = null;
	let selectedTime = null;
	let datePicker = null;

	$(document).ready(function() {
		initDatePicker();
		initEventHandlers();
	});

	/**
	 * Initialize Flatpickr date picker
	 */
	function initDatePicker() {
		const settings = wpBooking.settings;
		const daysAvailable = settings.days_available || [1, 2, 3, 4, 5];
		const bookingBuffer = parseInt(settings.booking_buffer) || 24;

		// Calculate minimum date based on booking buffer
		const minDate = new Date();
		minDate.setHours(minDate.getHours() + bookingBuffer);

		datePicker = flatpickr('#booking-date', {
			minDate: minDate,
			dateFormat: 'Y-m-d',
			disable: [
				function(date) {
					// Disable days not in available days
					return !daysAvailable.includes(date.getDay());
				}
			],
			onChange: function(selectedDates, dateStr, instance) {
				if (dateStr) {
					selectedDate = dateStr;
					loadAvailableSlots(dateStr);
				}
			}
		});
	}

	/**
	 * Initialize event handlers
	 */
	function initEventHandlers() {
		// Time slot selection
		$(document).on('click', '.time-slot:not(.disabled)', function() {
			$('.time-slot').removeClass('selected');
			$(this).addClass('selected');
			selectedTime = $(this).data('time');
			$('#booking-time').val(selectedTime);

			// Automatically move to next step after short delay
			setTimeout(function() {
				showStep('details');
			}, 300);
		});

		// Navigation buttons
		$('#back-to-date').on('click', function() {
			showStep('date');
		});

		$('#back-to-time').on('click', function() {
			showStep('time');
		});

		// Form submission
		$('#wp-booking-form').on('submit', function(e) {
			e.preventDefault();
			submitBooking();
		});

		// New booking button
		$('#new-booking').on('click', function() {
			resetForm();
		});

		// Retry booking button
		$('#retry-booking').on('click', function() {
			$('.booking-error').hide();
			showStep('details');
		});
	}

	/**
	 * Load available time slots for selected date
	 */
	function loadAvailableSlots(date) {
		$('#time-slots-container').html('<div class="booking-loading"><div class="spinner"></div></div>');
		showStep('time');

		$.ajax({
			url: wpBooking.ajaxUrl,
			type: 'POST',
			data: {
				action: 'get_available_slots',
				nonce: wpBooking.nonce,
				date: date
			},
			success: function(response) {
				if (response.success && response.data.slots.length > 0) {
					renderTimeSlots(response.data.slots);
				} else {
					showNoSlotsMessage();
				}
			},
			error: function() {
				showError(wpBooking.i18n.errorMessage);
			}
		});
	}

	/**
	 * Render time slots
	 */
	function renderTimeSlots(slots) {
		let html = '';

		slots.forEach(function(slot) {
			html += '<div class="time-slot" data-time="' + slot + '">' + slot + '</div>';
		});

		$('#time-slots-container').html(html);
	}

	/**
	 * Show no slots available message
	 */
	function showNoSlotsMessage() {
		$('#time-slots-container').html(
			'<div class="no-slots-message">' + wpBooking.i18n.noSlotsMessage + '</div>' +
			'<button type="button" class="btn btn-secondary" id="back-to-date" style="width: 100%;">' +
			'Choose Another Date' +
			'</button>'
		);
	}

	/**
	 * Show specific form step
	 */
	function showStep(step) {
		$('.booking-step').hide();
		$('#step-' + step).show();
	}

	/**
	 * Submit booking
	 */
	function submitBooking() {
		// Get form data
		const formData = {
			action: 'submit_booking',
			nonce: wpBooking.nonce,
			name: $('#booking-name').val(),
			email: $('#booking-email').val(),
			phone: $('#booking-phone').val(),
			date: selectedDate,
			time: selectedTime,
			message: $('#booking-message').val()
		};

		// Validate
		if (!formData.name || !formData.email || !formData.phone || !formData.date || !formData.time) {
			showError('Please fill in all required fields.');
			return;
		}

		// Show loading
		$('.booking-step').hide();
		$('.booking-loading').show();

		// Submit via AJAX
		$.ajax({
			url: wpBooking.ajaxUrl,
			type: 'POST',
			data: formData,
			success: function(response) {
				$('.booking-loading').hide();

				if (response.success) {
					showSuccess();
				} else {
					showError(response.data.message || wpBooking.i18n.errorMessage);
				}
			},
			error: function() {
				$('.booking-loading').hide();
				showError(wpBooking.i18n.errorMessage);
			}
		});
	}

	/**
	 * Show success message
	 */
	function showSuccess() {
		$('.booking-success').show();
	}

	/**
	 * Show error message
	 */
	function showError(message) {
		$('.booking-error .error-message').text(message);
		$('.booking-error').show();
	}

	/**
	 * Reset form to initial state
	 */
	function resetForm() {
		// Reset form fields
		$('#wp-booking-form')[0].reset();

		// Clear selections
		selectedDate = null;
		selectedTime = null;

		// Clear flatpickr
		if (datePicker) {
			datePicker.clear();
		}

		// Hide all messages
		$('.booking-success, .booking-error, .booking-loading').hide();

		// Show first step
		showStep('date');
	}

})(jQuery);
