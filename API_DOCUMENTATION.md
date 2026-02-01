OKayI Documentation for n8n Integration

## Authentication

All API requests must include an API key for authentication. You can pass the API key in two ways:

### Option 1: Header (Recommended)
```
X-API-Key: your_api_key_here
```

### Option 2: Query Parameter
```
?api_key=your_api_key_here
```

**Setting up the API Key:**
Add to your `.env` file:
```
API_KEY=your_secure_api_key_here
```

---

## Base URL
```
https://yourdomain.com/api
```

---

## Customer Endpoints

### 1. Get All Customers
**GET** `/api/customers`

**Query Parameters:**
- `limit` (optional, default: 50) - Number of records to return
- `offset` (optional, default: 0) - Starting position
- `search` (optional) - Search by name, email, or phone

**Response:**
```json
{
  "success": true,
  "data": [...],
  "total": 100,
  "limit": 50,
  "offset": 0
}
```

### 2. Get Customer by ID
**GET** `/api/customers/:id`

**Response:**
```json
{
  "success": true,
  "data": {
    "customer": {...},
    "billing_address": {...},
    "shipping_address": {...}
  }
}
```

### 3. Update Customer
**PUT** `/api/customers/:id`

**Request Body:**
```json
{
  "name": "Customer Name",
  "email": "email@example.com",
  "phone": "1234567890",
  ...
}
```

### 4. Link Customer to Zoho
**POST** `/api/customers/:id/zoho-link`

**Request Body:**
```json
{
  "zoho_contact_id": "123456789"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Customer linked to Zoho successfully",
  "data": {...}
}
```

### 5. Sync Customer Address
**POST** `/api/customers/:id/address/sync`

**Request Body:**
```json
{
  "type": "billing",
  "address_data": {
    "address_line1": "123 Main St",
    "address_line2": "Apt 4",
    "city": "Mumbai",
    "state_id": 1,
    "country_id": 1,
    "pincode": "400001"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Address synced successfully",
  "data": {...}
}
```

---

## Vendor Endpoints

### 1. Get All Vendors
**GET** `/api/vendors`

**Query Parameters:**
- `limit` (optional, default: 50)
- `offset` (optional, default: 0)
- `search` (optional)

### 2. Get Vendor by ID
**GET** `/api/vendors/:id`

### 3. Update Vendor
**PUT** `/api/vendors/:id`

### 4. Link Vendor to Zoho
**POST** `/api/vendors/:id/zoho-link`

**Request Body:**
```json
{
  "zoho_contact_id": "987654321"
}
```

### 5. Sync Vendor Address
**POST** `/api/vendors/:id/address/sync`

**Request Body:**
```json
{
  "type": "shipping",
  "address_data": {
    "address_line1": "456 Vendor St",
    "city": "Delhi",
    "state_id": 2,
    "country_id": 1,
    "pincode": "110001"
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "error": "API key is required"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "error": "Invalid API key"
}
```

### 404 Not Found
```json
{
  "success": false,
  "messages": {
    "error": "Customer not found"
  }
}
```

### 400 Bad Request
```json
{
  "success": false,
  "messages": {
    "error": "zoho_contact_id is required"
  }
}
```

---

## n8n Integration Example

### Example: Link Customer to Zoho Contact

**HTTP Request Node Configuration:**
- **Method:** POST
- **URL:** `https://yourdomain.com/api/customers/123/zoho-link`
- **Headers:**
  - `X-API-Key`: `your_api_key`
  - `Content-Type`: `application/json`
- **Body:**
```json
{
  "zoho_contact_id": "{{ $json.zoho_id }}"
}
```

### Example: Sync Address from Zoho to Database

**HTTP Request Node Configuration:**
- **Method:** POST
- **URL:** `https://yourdomain.com/api/customers/{{ $json.customer_id }}/address/sync`
- **Headers:**
  - `X-API-Key`: `your_api_key`
  - `Content-Type`: `application/json`
- **Body:**
```json
{
  "type": "billing",
  "address_data": {
    "address_line1": "{{ $json.address }}",
    "city": "{{ $json.city }}",
    "state_id": "{{ $json.state_id }}",
    "country_id": "{{ $json.country_id }}",
    "pincode": "{{ $json.zip }}"
  }
}
```

---

## Database Fields

The following Zoho-related fields are maintained in the database:

**Customers Table:**
- `zoho_contact_id` - Zoho Books contact ID
- `zoho_sync_at` - Last sync timestamp

**Vendors Table:**
- `zoho_contact_id` - Zoho Books contact ID
- `zoho_sync_at` - Last sync timestamp

These fields are automatically updated when using the API endpoints.
