# Postman Testing Guide - Kereta Travel API

## Setup Postman

### 1. Import Collection & Environment

1. **Buka Postman** → klik tombol **Import** (kiri atas)
2. Pilih tab **File** → browse file `Postman_Collection.json` dari project root
3. Lakukan lagi untuk import `Postman_Environment.json`

### 2. Aktifkan Environment

1. Klik dropdown **No Environment** (kanan atas)
2. Pilih **"Kereta Travel API - Local"**

---

## Workflow Testing

### Step 1: Login
1. Buka folder **Auth** → request **Login**
2. Ubah email/password sesuai seed data:
   ```json
   {
     "email": "test@example.com",
     "password": "password"
   }
   ```
3. Klik **Send**
4. Response akan auto-save `access_token` ke environment (lihat Tests tab)

### Step 2: Test Protected Endpoints
Setelah login, semua endpoint di **Profile** & **Bookings** sudah punya token otomatis dari environment.

---

## Endpoint Reference

### Public (Tidak perlu token)
- `POST /api/auth/register` - Register user baru
- `POST /api/auth/login` - Login & dapatkan token

### Protected (Perlu Bearer token)

#### Auth
- `GET /api/auth/me` - Get current user info

#### Profile
- `GET /api/profile` - Get profile user
- `PUT /api/profile` - Update profile (name/email/password)

#### Bookings
- `GET /api/bookings?per_page=15` - List bookings (paged)
- `POST /api/bookings` - Create booking baru
- `GET /api/bookings/{id}` - Get detail booking

---

## Tips

### Auto-Save Token
Saat login, script otomatis di **Tests** tab menyimpan token:
```javascript
const json = pm.response.json();
if (json.token && json.token.access_token) {
    pm.environment.set('access_token', json.token.access_token);
}
```

### Custom per_page
Ubah parameter query di **Params** tab:
```
Key: per_page
Value: 50
```

### Test Data Seed
Default seed data:
- **Email**: `test@example.com`
- **Password**: `password`
- **Bookings**: 10000 records (sesuai update BookingSeeder)

---

## Troubleshooting

### ❌ 401 Unauthorized
- Pastikan sudah login dulu (run **Login** request)
- Cek environment variable `access_token` terisi
- Token bisa expired, login ulang

### ❌ 422 Validation Error
- Periksa format JSON di body
- Pastikan field required terisi (lihat error message)

### ❌ ECONNREFUSED
- Pastikan server running: `./vendor/bin/sail artisan serve`
- Atau via Docker: `docker compose up -d`
- Cek `base_url` di environment (default: `http://localhost`)
