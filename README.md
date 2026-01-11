# 4wp-auth

Social authentication plugin for WordPress

## Description

Self-hosted OAuth 2.0 authentication plugin for WordPress. Allows users to sign in and register using social accounts.

## Version 1.0.1

### Current Features

- **Gmail** OAuth 2.0 authentication
- REST API endpoints for external integration
- Shortcode support: `[forwp_auth_login provider="gmail"]`
- Admin settings page
- Automatic user creation/update
- Secure state parameter for CSRF protection

## Quick Start

1. Activate the plugin
2. Go to **Settings → 4wp Auth**
3. Add your Google OAuth credentials (Client ID & Client Secret)
4. Copy the Redirect URI to Google Cloud Console
5. Use shortcode: `[forwp_auth_login provider="gmail"]`

### Usage Examples

**Shortcode:**
```
[forwp_auth_login provider="gmail"]
```

**HTML:**
```html
<button class="forwp-auth-btn" data-provider="gmail">Sign in with Gmail</button>
```

**PHP:**
```php
<?php echo do_shortcode( '[forwp_auth_login provider="gmail"]' ); ?>
```

## Documentation

- [Roadmap](ROADMAP.md) - Development roadmap
- [API Documentation](API.md) - REST API endpoints for developers

## Requirements

- WordPress 6.0+
- PHP 8.0+

## License

MIT / GPL-2.0-or-later
