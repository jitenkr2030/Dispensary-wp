/**
 * Dispensary WP common JavaScript.
 */

(function (window, document) {
	'use strict';

	window.DispensaryWPAssets = window.DispensaryWPAssets || {};

	window.DispensaryWPAssets.escapeHtml = function (value) {
		var div = document.createElement('div');

		div.textContent = value == null ? '' : String(value);

		return div.innerHTML;
	};

	window.DispensaryWPAssets.debounce = function (callback, delay) {
		var timeout;

		return function () {
			var context = this;
			var args = arguments;

			clearTimeout(timeout);

			timeout = setTimeout(function () {
				callback.apply(context, args);
			}, delay);
		};
	};

	window.DispensaryWPAssets.setLoading = function (element, loading) {
		if (!element) {
			return;
		}

		if (loading) {
			element.classList.add('dispensary-wp-loading');
			element.setAttribute('aria-busy', 'true');
		} else {
			element.classList.remove('dispensary-wp-loading');
			element.removeAttribute('aria-busy');
		}
	};

})(window, document);
