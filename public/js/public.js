(function ($) {
	'use strict';

	/**
	 * Order status.
	 */
	$(document).on('submit', '.dispensary-wp-order-status-form', function (event) {
		event.preventDefault();

		var $form = $(this);
		var $result = $form.siblings('.dispensary-wp-order-result');
		var orderNumber = $form.find('[name="order_number"]').val();

		$result.removeClass(
			'dispensary-wp-result-success dispensary-wp-result-error'
		);

		$result.text('Checking...');

		$.ajax({
			url: DispensaryWP.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'dispensary_wp_order_status',
				nonce: DispensaryWP.nonce,
				order_number: orderNumber
			}
		})
			.done(function (response) {
				if (response.success) {
					$result
						.addClass('dispensary-wp-result-success')
						.text(
							'Order status: ' +
							response.data.status
						);
				} else {
					showError($result, response);
				}
			})
			.fail(function () {
				$result
					.addClass('dispensary-wp-result-error')
					.text('Unable to check order status.');
			});
	});

	/**
	 * Delivery status.
	 */
	$(document).on('submit', '.dispensary-wp-delivery-form', function (event) {
		event.preventDefault();

		var $form = $(this);
		var $result = $form.siblings('.dispensary-wp-delivery-result');
		var deliveryNumber = $form.find('[name="delivery_number"]').val();

		$result.removeClass(
			'dispensary-wp-result-success dispensary-wp-result-error'
		);

		$result.text('Checking...');

		$.ajax({
			url: DispensaryWP.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'dispensary_wp_delivery_status',
				nonce: DispensaryWP.nonce,
				delivery_number: deliveryNumber
			}
		})
			.done(function (response) {
				if (response.success) {
					$result
						.addClass('dispensary-wp-result-success')
						.text(
							'Delivery status: ' +
							response.data.status
						);
				} else {
					showError($result, response);
				}
			})
			.fail(function () {
				$result
					.addClass('dispensary-wp-result-error')
					.text('Unable to check delivery status.');
			});
	});

	/**
	 * Product add-to-cart placeholder.
	 */
	$(document).on('click', '.dispensary-wp-add-to-cart', function () {

		var productId = $(this).data('product-id');

		if (!productId) {
			return;
		}

		window.dispatchEvent(
			new CustomEvent(
				'dispensaryWpAddToCart',
				{
					detail: {
						productId: productId
					}
				}
			)
		);
	});

	/**
	 * Show AJAX error.
	 *
	 * @param {jQuery} $element Result element.
	 * @param {Object} response AJAX response.
	 */
	function showError($element, response) {

		var message = 'Request failed.';

		if (
			response &&
			response.data &&
			response.data.message
		) {
			message = response.data.message;
		}

		$element
			.addClass('dispensary-wp-result-error')
			.text(message);
	}

})(jQuery);
