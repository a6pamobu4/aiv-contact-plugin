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

		initConditionalFields(form);
		initPhoneMasks(form);

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			if (!submit) {
				return;
			}

			updatePhoneMasks(form);

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
					updateConditionalFields(form);
					updatePhoneMasks(form);
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

	function initPhoneMasks(form) {
		var phoneFields = form.querySelectorAll('[data-aiv-contact-phone]');

		if (!phoneFields.length) {
			return;
		}

		updatePhoneMasks(form);

		phoneFields.forEach(function (field) {
			field.addEventListener('input', function () {
				formatPhoneField(field);
			});

			field.addEventListener('focus', function () {
				formatPhoneField(field);
			});
		});
	}

	function updatePhoneMasks(form) {
		form.querySelectorAll('[data-aiv-contact-phone]').forEach(function (field) {
			formatPhoneField(field);
		});
	}

	function formatPhoneField(field) {
		var digits = field.value.replace(/\D/g, '');

		if (digits.charAt(0) === '7' || digits.charAt(0) === '8') {
			digits = digits.slice(1);
		}

		digits = digits.slice(0, 10);
		field.value = buildPhoneValue(digits);

		if (document.activeElement === field) {
			field.setSelectionRange(field.value.length, field.value.length);
		}
	}

	function buildPhoneValue(digits) {
		var value = '+7';

		if (!digits.length) {
			return value + ' ';
		}

		value += ' (' + digits.slice(0, 3);

		if (digits.length >= 3) {
			value += ')';
		}

		if (digits.length > 3) {
			value += ' ' + digits.slice(3, 6);
		}

		if (digits.length > 6) {
			value += '-' + digits.slice(6, 8);
		}

		if (digits.length > 8) {
			value += '-' + digits.slice(8, 10);
		}

		return value;
	}

	function initConditionalFields(form) {
		var conditionalFields = form.querySelectorAll('[data-aiv-contact-condition-field]');

		if (!conditionalFields.length) {
			return;
		}

		updateConditionalFields(form);

		form.addEventListener('change', function () {
			updateConditionalFields(form);
		});
	}

	function updateConditionalFields(form) {
		var conditionalFields = form.querySelectorAll('[data-aiv-contact-condition-field]');

		conditionalFields.forEach(function (field) {
			var conditionField = field.getAttribute('data-aiv-contact-condition-field') || '';
			var conditionValue = field.getAttribute('data-aiv-contact-condition-value') || '';
			var isActive = getFieldValue(form, conditionField) === conditionValue;

			field.classList.toggle('aiv-contact-field-hidden', !isActive);
			field.setAttribute('aria-hidden', isActive ? 'false' : 'true');

			field.querySelectorAll('input, select, textarea').forEach(function (control) {
				control.disabled = !isActive;
			});
		});
	}

	function getFieldValue(form, name) {
		var fields = form.elements[name];

		if (!fields) {
			return '';
		}

		if (typeof fields.length === 'number' && fields.tagName === undefined) {
			var checked = Array.prototype.filter.call(fields, function (field) {
				return field.checked;
			})[0];

			return checked ? checked.value : '';
		}

		return fields.value || '';
	}
}());
