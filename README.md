# AIV Contact

AIV Contact is a reusable WordPress contact form plugin for AIV client websites. It provides a shortcode-rendered form, a REST API submission endpoint, server-side validation, basic anti-spam checks, and plain text email delivery.

## Shortcode

Add the form to content with:

```text
[aiv_contact_form]
```

The shortcode enqueues its JavaScript and CSS only when the form is rendered.

## REST Endpoint

Submissions are sent to:

```text
POST /wp-json/aiv-contact/v1/submit
```

The endpoint accepts JSON or form data and returns JSON success or error responses.

## Recipient Email

By default, submissions are sent to the site admin email from `get_option( 'admin_email' )`.

Override the recipient with a constant:

```php
define( 'AIV_CONTACT_RECIPIENT_EMAIL', 'hello@example.com' );
```

Or with a filter:

```php
add_filter(
	'aiv_contact_recipient_email',
	function ( string $recipient, array $data ): string {
		return 'hello@example.com';
	},
	10,
	2
);
```

## Anti-Spam Protections

- WordPress REST nonce validation.
- Hidden honeypot field.
- Hidden `started_at` timestamp with fast-submission rejection.
- Basic IP rate limiting with transients.
- Server-side sanitization and validation for every field.

Submissions are not stored in the database in v1.

## Local Development

Install PHP tooling:

```bash
composer install
```

Run PHP linting:

```bash
composer run lint:php
```

Apply PHPCS autofixes intentionally:

```bash
composer run fix:php
```

Install frontend tooling:

```bash
npm install
```

Build CSS from SCSS:

```bash
npm run build
```

Watch SCSS during development:

```bash
npm run start
```

Check JavaScript syntax:

```bash
node --check assets/js/contact-form.js
```
