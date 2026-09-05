(function ($) {
	'use strict';

	$(document).ready(function () {

		$('.dispensary-wp-dismiss').on('click', function () {
			$(this).closest('.notice').fadeOut();
		});

		$('.dispensary-wp-confirm').on('click', function (event) {
			var message = $(this).data('confirm');

			if (message && !window.confirm(message)) {
				event.preventDefault();
			}
		});

	});

})(jQuery);
