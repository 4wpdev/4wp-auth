# REST API Documentation

REST API endpoints for external integration (React, Vue, mobile apps, etc.)

## Endpoints

### Get Authorization URL

**GET** `/wp-json/forwp-auth/v1/auth/{provider}`

Returns OAuth authorization URL for the specified provider.

**Parameters:**
- `provider` (required) - Provider ID (`gmail`, `facebook`, etc.)

**Response (Success):**
```json
{
  "auth_url": "https://accounts.google.com/o/oauth2/v2/auth?client_id=..."
}
```

**Response (Error):**
```json
{
  "code": "provider_disabled",
  "message": "Provider is not enabled",
  "data": {
    "status": 403
  }
}
```

### OAuth Callback

**GET** `/wp-json/forwp-auth/v1/callback/{provider}?code=...&state=...`

Automatically handled by the plugin. Redirects user after successful authentication.

## Usage Examples

### JavaScript

```javascript
fetch('/wp-json/forwp-auth/v1/auth/gmail')
  .then(response => response.json())
  .then(data => {
    if (data.auth_url) {
      window.location.href = data.auth_url;
    }
  });
```

### React Hook

```jsx
const loginWithGmail = async () => {
  const response = await fetch('/wp-json/forwp-auth/v1/auth/gmail');
  const data = await response.json();
  if (data.auth_url) {
    window.location.href = data.auth_url;
  }
};
```

### Check Authentication Status

```javascript
fetch('/wp-json/wp/v2/users/me', {
  credentials: 'include'
})
  .then(response => response.json())
  .then(user => {
    if (user.id) {
      console.log('Logged in as:', user.name);
    }
  });
```
