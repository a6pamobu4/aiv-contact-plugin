# AIV Contact

AIV Contact is a reusable WordPress contact form plugin for AIV client websites. It provides configurable admin-created forms, shortcode rendering, REST API submission, server-side validation, basic anti-spam checks, and plain text email delivery.

Submissions are sent by email only. They are not stored in the database in this version.

## Creating a Form

In WordPress admin, open **AIV Forms** and add a new form.

Each form supports these settings:

- Form slug
- Recipients
- Email subject
- Success message
- Submit button text

Each form also has a repeatable field builder. Field settings include:

- Label
- Name
- Type
- Required
- Placeholder
- Options
- Width

Supported field types:

- `text`
- `tel`
- `email`
- `textarea`
- `select`
- `radio-buttons`
- `checkbox`
- `hidden`

For `select` and `radio-buttons`, enter one option per line in the options textarea.

## Shortcodes

Render the default form:

```text
[aiv_contact_form]
```

Render a form by post ID:

```text
[aiv_contact_form id="123"]
```

Render a form by slug:

```text
[aiv_contact_form slug="main"]
```

If no form exists, administrators see a setup message. Public visitors do not see an error.

## Default Form

On plugin activation, AIV Contact creates a default form only if a form with slug `main` does not already exist.

The default form includes name, phone, contact method, optional email, comment, and consent fields.

## REST Endpoint

Submissions are sent to:

```text
POST /wp-json/aiv-contact/v1/submit
```

The endpoint accepts JSON or form data. It requires `form_id`, validates the nonce, loads that form configuration, validates submitted fields against the saved field schema, sends email, and returns JSON success or error responses.

## Recipients

Each form has a recipients setting. Use comma-separated email addresses:

```text
hello@example.com, sales@example.com
```

Every recipient is validated before sending. If recipients are empty or invalid, the plugin falls back to `get_option( 'admin_email' )`.

You can still override recipients globally with a constant:

```php
define( 'AIV_CONTACT_RECIPIENT_EMAIL', 'hello@example.com' );
```

Or with a filter:

```php
add_filter(
	'aiv_contact_recipient_email',
	function ( string $recipients, array $data, array $form ): string {
		return 'hello@example.com';
	},
	10,
	3
);
```

## Email

Emails are plain text. The email body is built dynamically from submitted field labels and values.

The form-specific subject supports this placeholder:

```text
{site_name}
```

The plugin does not set the `From` header to the visitor email. If a submitted `email` field contains a valid email address, it is used only as `Reply-To`.

For production websites, use a proper SMTP/mail delivery plugin or server-level transactional email setup. Local Mailpit testing is useful for development, but production delivery should not depend on default local mail behavior.

## Anti-Spam Protections

- WordPress REST nonce validation.
- Hidden honeypot field.
- Hidden `started_at` timestamp with fast-submission rejection.
- Basic IP rate limiting with transients.
- Server-side sanitization and validation for every configured field.
- Unknown submitted fields are rejected.

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

Check JavaScript syntax:

```bash
node --check assets/js/contact-form.js
node --check assets/js/admin-form-builder.js
```
