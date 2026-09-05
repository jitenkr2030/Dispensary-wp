/**
 * Dispensary WP UI components.
 */

(function (window, document) {
	'use strict';

	/**
	 * Open modal.
	 */
	document.addEventListener('click', function (event) {

		var openButton = event.target.closest(
			'[data-dispensary-modal-open]'
		);

		if (openButton) {

			var modalId = openButton.getAttribute(
				'data-dispensary-modal-open'
			);

			var modal = document.getElementById(modalId);

			if (modal) {
				modal.classList.add('is-open');
				document.body.classList.add(
					'dispensary-wp-modal-open'
				);
			}
		}

		/**
		 * Close modal.
		 */
		var closeButton = event.target.closest(
			'[data-dispensary-modal-close]'
		);

		if (closeButton) {

			var closeModal = closeButton.closest(
				'.dispensary-wp-modal'
			);

			if (closeModal) {
				closeModal.classList.remove('is-open');
				document.body.classList.remove(
					'dispensary-wp-modal-open'
				);
			}
		}
	});

	/**
	 * Close modal when clicking overlay.
	 */
	document.addEventListener('click', function (event) {

		if (
			event.target.classList.contains(
				'dispensary-wp-modal'
			)
		) {
			event.target.classList.remove('is-open');
			document.body.classList.remove(
				'dispensary-wp-modal-open'
			);
		}
	});

	/**
	 * Escape key closes modal.
	 */
	document.addEventListener('keydown', function (event) {

		if ('Escape' !== event.key) {
			return;
		}

		var openModal = document.querySelector(
			'.dispensary-wp-modal.is-open'
		);

		if (openModal) {
			openModal.classList.remove('is-open');
			document.body.classList.remove(
				'dispensary-wp-modal-open'
			);
		}
	});

})(window, document);
