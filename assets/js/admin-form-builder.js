(function () {
	'use strict';

	var builders = document.querySelectorAll('[data-aiv-contact-field-builder]');

	if (!builders.length) {
		return;
	}

	builders.forEach(function (builder) {
		var list = builder.querySelector('[data-aiv-contact-field-list]');
		var template = builder.querySelector('[data-aiv-contact-field-template]');
		var addButton = builder.querySelector('[data-aiv-contact-add-field]');

		if (!list || !template || !addButton) {
			return;
		}

		addButton.addEventListener('click', function () {
			var index = String(Date.now());
			var wrapper = document.createElement('div');

			wrapper.innerHTML = template.innerHTML.replace(/__index__/g, index).trim();

			if (wrapper.firstElementChild) {
				list.appendChild(wrapper.firstElementChild);
			}
		});

		list.addEventListener('click', function (event) {
			var target = event.target;

			if (!(target instanceof Element) || !target.matches('[data-aiv-contact-remove-field]')) {
				return;
			}

			var item = target.closest('[data-aiv-contact-field-item]');

			if (item) {
				item.remove();
			}
		});
	});
}());
