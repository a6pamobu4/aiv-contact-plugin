(function () {
	'use strict';

	var forms = document.querySelectorAll('[data-aiv-contact-form]');
	var settings = window.aivContactForm || {};

	if (!forms.length || !settings.restUrl) {
		return;
	}

	forms.forEach(function (form) {
		var status = form.querySelector('[data-aiv-contact-status]');
		var submit = form.querySelector('.aiv-contact-submit');

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			if (!submit) {
				return;
			}

			var formData = new FormData(form);
			var payload = {};

			formData.forEach(function (value, key) {
				payload[key] = value;
			});

			if (settings.nonce) {
				payload._wpnonce = settings.nonce;
			}

			submit.disabled = true;
			setStatus(status, '');

			fetch(settings.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': settings.nonce || ''
				},
				body: JSON.stringify(payload),
				credentials: 'same-origin'
			})
				.then(function (response) {
					return response.json().then(function (data) {
						if (!response.ok) {
							throw data;
						}

						return data;
					});
				})
				.then(function (data) {
					setStatus(status, data.message || 'Thank you. Your request has been sent.', false);
					form.reset();
				})
				.catch(function (error) {
					setStatus(status, error.message || 'The request could not be sent. Please try again.', true);
				})
				.finally(function () {
					submit.disabled = false;
				});
		});
	});

	function setStatus(element, message, isError) {
		if (!element) {
			return;
		}

		element.textContent = message;
		element.classList.toggle('aiv-contact-status-error', Boolean(isError));
	}
}());
