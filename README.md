# GNNpaste-bin

A modern, secure, and lightweight PHP-based Pastebin application with AES-128 encryption and a premium glassmorphic UI.

## Features

- **Encrypted Storage**: All pastes are encrypted using AES-128-CTR before being saved to the disk.
- **Expiration System**: Pastes can be set to expire after a certain period (1 day, 7 days, 1 month, 6 months, or never).
- **Flood Protection**: Built-in rate limiting to prevent spam and abuse.
- **Admin Panel**: A secure admin panel to manage, view, and delete uploaded pastes.
- **Google reCAPTCHA**: Protection against bot submissions on the admin login.
- **Responsive Design**: Beautiful UI built with Tailwind CSS and premium aesthetics.
- **Syntax Highlighting**: Supports multiple languages using `highlight.js`.

## Installation

1. Upload all files to your PHP-enabled web server.
2. Ensure the `/file/` directory is writable by the web server.
3. Configure your environment variables in `.htaccess` or your server configuration:
   - `ADMIN_USERNAME`: Username for the admin panel.
   - `ADMIN_PASSWORD`: Password for the admin panel.
   - `RECAPTCHA_SITE_KEY`: Your Google reCAPTCHA site key.
   - `RECAPTCHA_SECRET_KEY`: Your Google reCAPTCHA secret key.
   - `ENCRYPTION_KEY`: A strong key used for AES encryption.

## Maintenance

To automatically delete expired pastes, set up a cron job to run `maintenance.php`:

```bash
0 * * * * php /path/to/your/maintenance.php
```

## Security

- Use a strong `ENCRYPTION_KEY`.
- Ensure `.htaccess` is working to protect `.data` and `.meta` files from direct access.
- Keep your PHP version up to date.

## License

MIT License - feel free to use and modify for your own projects.
