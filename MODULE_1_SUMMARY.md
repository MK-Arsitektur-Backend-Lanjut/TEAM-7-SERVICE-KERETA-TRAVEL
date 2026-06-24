# 📋 Summary Modul 1 — Service Kereta Travel (Team 7)

**Branch:** `TIM7_MODUL1_HISYAM`  
**Framework:** Laravel 13  
**Infrastruktur:** Docker (compose.yaml)  
**Tanggal Pengerjaan:** 14–16 April 2026  

---

## 🏗️ Arsitektur & Design Pattern

### Repository Pattern
Modul ini menggunakan **Repository Pattern** untuk memisahkan logika akses data dari controller. Alur kerjanya:

```
Controller → Interface → Repository → Model → Database
```

- **Interface** mendefinisikan kontrak method yang wajib diimplementasi.
- **Repository** mengimplementasi interface tersebut dengan logika query Eloquent.
- **Controller** hanya bergantung pada interface (Dependency Injection via constructor).
- **Service Provider** (`AppServiceProvider`) melakukan binding interface ke implementasi repository.

### Dependency Injection
Semua repository di-inject melalui constructor controller dan di-binding di `AppServiceProvider`:

```php
$this->app->bind(TrainRepositoryInterface::class, TrainRepository::class);
$this->app->bind(StationRepositoryInterface::class, StationRepository::class);
$this->app->bind(RouteRepositoryInterface::class, RouteRepository::class);
```

---

## 🗄️ Database — Migration

### 1. Tabel `trains` (`2026_04_14_152802`)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto-increment |
| `name` | string | Nama kereta |
| `code` | string (unique) | Kode kereta, misal `ABA-01` |
| `type` | enum | `ekonomi`, `bisnis`, `eksekutif` |
| `total_seats` | unsigned int | Jumlah kursi |
| `is_active` | boolean | Default `true` |
| `timestamps` | datetime | `created_at`, `updated_at` |

### 2. Tabel `stations` (`2026_04_15_095221`)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto-increment |
| `name` | string | Nama stasiun |
| `code` | string(10, unique) | Kode stasiun, misal `GMR` |
| `city` | string | Kota |
| `province` | string | Provinsi |
| `is_active` | boolean | Default `true` |
| `timestamps` | datetime | `created_at`, `updated_at` |

### 3. Tabel `routes` (`2026_04_15_095226`)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | Auto-increment |
| `train_id` | FK → `trains.id` | Cascade on delete |
| `origin_station_id` | FK → `stations.id` | Stasiun asal, cascade on delete |
| `destination_station_id` | FK → `stations.id` | Stasiun tujuan, cascade on delete |
| `departure_time` | datetime | Waktu berangkat |
| `arrival_time` | datetime | Waktu tiba |
| `duration_minutes` | unsigned int | Durasi perjalanan (menit) |
| `distance_km` | decimal(8,2) | Jarak tempuh (km) |
| `price` | decimal(12,2) | Harga tiket |
| `is_active` | boolean | Default `true` |
| `timestamps` | datetime | `created_at`, `updated_at` |

### Relasi Antar Tabel

```
trains ──< routes >── stations
           │              │
           ├─ train_id ──→│ (belongs to 1 train)
           ├─ origin_station_id ──→│ (belongs to 1 station)
           └─ destination_station_id ──→│ (belongs to 1 station)
```

---

## 📦 Model — Eloquent

### `Train` (`app/Models/Train.php`)
- **Fillable:** `name`, `code`, `type`, `total_seats`, `is_active`
- **Casts:** `is_active` → boolean, `total_seats` → integer
- **Relasi:**
  - `routes()` → HasMany ke `Route`

### `Station` (`app/Models/Station.php`)
- **Fillable:** `name`, `code`, `city`, `province`, `is_active`
- **Casts:** `is_active` → boolean
- **Relasi:**
  - `departingRoutes()` → HasMany ke `Route` (via `origin_station_id`)
  - `arrivingRoutes()` → HasMany ke `Route` (via `destination_station_id`)

### `Route` (`app/Models/Route.php`)
- **Fillable:** `train_id`, `origin_station_id`, `destination_station_id`, `departure_time`, `arrival_time`, `duration_minutes`, `distance_km`, `price`, `is_active`
- **Casts:** `departure_time` → datetime, `arrival_time` → datetime, `is_active` → boolean, `duration_minutes` → integer, `distance_km` → decimal:2, `price` → decimal:2
- **Relasi:**
  - `train()` → BelongsTo ke `Train`
  - `originStation()` → BelongsTo ke `Station` (via `origin_station_id`)
  - `destinationStation()` → BelongsTo ke `Station` (via `destination_station_id`)
- **Accessor:**
  - `getDurationFormattedAttribute()` → format durasi ke `"X jam Y menit"`

---

## 📝 Interface — Kontrak Repository

### `TrainRepositoryInterface` (`app/Interfaces/TrainRepositoryInterface.php`)
| Method | Return | Keterangan |
|---|---|---|
| `getAllTrains()` | `Collection<Train>` | Ambil semua kereta |
| `getTrainById(int $id)` | `Train` | Ambil 1 kereta by ID |
| `createTrain(array $data)` | `Train` | Buat kereta baru |
| `updateTrain(int $id, array $data)` | `Train` | Update kereta |
| `deleteTrain(int $id)` | `bool` | Hapus kereta |

### `StationRepositoryInterface` (`app/Interfaces/StationRepositoryInterface.php`)
| Method | Return | Keterangan |
|---|---|---|
| `getAllStations(array $filters)` | `Collection<Station>` | Ambil semua stasiun (dengan filter opsional) |
| `getStationById(int $id)` | `Station` | Ambil 1 stasiun by ID |
| `createStation(array $data)` | `Station` | Buat stasiun baru |
| `updateStation(int $id, array $data)` | `Station` | Update stasiun |
| `deleteStation(int $id)` | `bool` | Hapus stasiun |

### `RouteRepositoryInterface` (`app/Interfaces/RouteRepositoryInterface.php`)
| Method | Return | Keterangan |
|---|---|---|
| `getAllRoutes(array $filters)` | `Collection<Route>` | Ambil semua rute (dengan filter opsional) |
| `getRouteById(int $id)` | `Route` | Ambil 1 rute by ID |
| `getRoutesByStation(int $stationId)` | `Collection<Route>` | Ambil rute yang berangkat/tiba di stasiun tertentu |
| `createRoute(array $data)` | `Route` | Buat rute baru |
| `updateRoute(int $id, array $data)` | `Route` | Update rute |
| `deleteRoute(int $id)` | `bool` | Hapus rute |
| `estimateTravelTime(int $originId, int $destinationId)` | `?int` | Estimasi waktu tempuh (menit), null jika tidak ada |

---

## 🔧 Repository — Implementasi

### `TrainRepository` (`app/Repositories/TrainRepository.php`)
- CRUD standar menggunakan Eloquent (`Train::all()`, `findOrFail`, `create`, `update`, `delete`)
- `updateTrain()` mengembalikan data fresh setelah update via `$train->fresh()`

### `StationRepository` (`app/Repositories/StationRepository.php`)
- CRUD standar + **filter** pada `getAllStations()`:
  - Filter `city` → query `LIKE` (partial match)
  - Filter `province` → query `LIKE` (partial match)
  - Filter `is_active` → boolean filter

### `RouteRepository` (`app/Repositories/RouteRepository.php`)
- CRUD dengan **eager loading** relasi (`train`, `originStation`, `destinationStation`)
- **Filter** pada `getAllRoutes()`:
  - `origin_station_id` → exact match
  - `destination_station_id` → exact match
  - `train_id` → exact match
  - `is_active` → boolean filter
  - `max_price` → filter harga ≤ nilai
- `getRoutesByStation()` → mencari rute aktif yang berangkat DARI atau tiba DI stasiun tertentu
- `estimateTravelTime()` → mencari rute aktif dengan durasi tercepat antara 2 stasiun

---

## 🎮 Controller — API Logic

### `TrainController` (`app/Http/Controllers/TrainController.php`)
| Method | Route | Fungsi | Validasi |
|---|---|---|---|
| `index()` | `GET /trains` | List semua kereta | — |
| `show($id)` | `GET /trains/{id}` | Detail 1 kereta | — |
| `store()` | `POST /trains` | Buat kereta baru | `name` required, `code` required+unique, `type` required (enum), `total_seats` required+min:1 |
| `update($id)` | `PUT /trains/{id}` | Update kereta | Semua field `sometimes` |
| `destroy($id)` | `DELETE /trains/{id}` | Hapus kereta | — |

### `StationController` (`app/Http/Controllers/StationController.php`)
| Method | Route | Fungsi | Validasi |
|---|---|---|---|
| `index()` | `GET /stations` | List stasiun (filter: city, province, is_active) | — |
| `show($id)` | `GET /stations/{id}` | Detail 1 stasiun | — |
| `store()` | `POST /stations` | Buat stasiun baru | `name` required, `code` required+unique+max:10, `city` required, `province` required |
| `update($id)` | `PUT /stations/{id}` | Update stasiun | Semua field `sometimes`, code unique kecuali diri sendiri |
| `destroy($id)` | `DELETE /stations/{id}` | Hapus stasiun | — |
| `routes($id)` | `GET /stations/{id}/routes` | List semua rute dari/ke stasiun ini | — |

### `RouteController` (`app/Http/Controllers/RouteController.php`)
| Method | Route | Fungsi | Validasi |
|---|---|---|---|
| `index()` | `GET /routes` | List rute (filter: origin, destination, train, is_active, max_price) | — |
| `show($id)` | `GET /routes/{id}` | Detail 1 rute | — |
| `store()` | `POST /routes` | Buat rute baru | `train_id` required+exists, `origin_station_id` required+exists+different, `departure_time` required, `arrival_time` required, `duration_minutes` required+min:1, `distance_km` required+min:0, `price` required+min:0 |
| `update($id)` | `PUT /routes/{id}` | Update rute | Semua field `sometimes` |
| `destroy($id)` | `DELETE /routes/{id}` | Hapus rute | — |
| `estimateTime()` | `GET /routes/estimate-time` | Estimasi waktu tempuh (query: `origin_id`, `destination_id`) | `origin_id` required+exists, `destination_id` required+exists+different |

---

## 🌐 API Routes (`routes/api.php`)

Semua endpoint berada di prefix `/api/v1/`:

```
GET     /api/v1/trains                              → TrainController@index
POST    /api/v1/trains                              → TrainController@store
GET     /api/v1/trains/{train}                      → TrainController@show
PUT     /api/v1/trains/{train}                      → TrainController@update
DELETE  /api/v1/trains/{train}                      → TrainController@destroy

GET     /api/v1/stations                            → StationController@index
POST    /api/v1/stations                            → StationController@store
GET     /api/v1/stations/{station}                  → StationController@show
PUT     /api/v1/stations/{station}                  → StationController@update
DELETE  /api/v1/stations/{station}                  → StationController@destroy
GET     /api/v1/stations/{station}/routes           → StationController@routes

GET     /api/v1/routes/estimate-time                → RouteController@estimateTime
GET     /api/v1/routes                              → RouteController@index
POST    /api/v1/routes                              → RouteController@store
GET     /api/v1/routes/{route}                      → RouteController@show
PUT     /api/v1/routes/{route}                      → RouteController@update
DELETE  /api/v1/routes/{route}                      → RouteController@destroy
```

**Total: 17 endpoint**

---

## 🌱 Database Seeder

### `TrainSeeder` — 20 data kereta
Data kereta real Indonesia, contoh:
- Argo Bromo Anggrek (Eksekutif, 450 kursi)
- Argo Parahyangan (Eksekutif, 400 kursi)
- Gajayana, Bima, Taksaka (Eksekutif)
- Fajar Utama YK, Senja Utama YK, Mataram (Bisnis)
- Bogowonto, Gajah Wong, Jayakarta, Kertajaya, dll (Ekonomi)

### `StationSeeder` — 38 data stasiun (+ 12 random)
Data stasiun real Indonesia, contoh:
- Gambir (GMR) — Jakarta Pusat, DKI Jakarta
- Bandung (BD) — Bandung, Jawa Barat
- Yogyakarta (YK) — Yogyakarta, DI Yogyakarta
- Surabaya Gubeng (SGU) — Surabaya, Jawa Timur
- + 12 stasiun random tambahan via Faker

### `RouteSeeder` — 10.000 data rute
- Di-generate secara random (origin ≠ destination)
- Departure time: 1–30 hari ke depan
- Durasi: 60–720 menit (1–12 jam)
- Jarak: 50–1000 km
- Harga: Rp 50.000 – Rp 800.000
- Batch insert per 1.000 record untuk performa

### Urutan Seed (`DatabaseSeeder`)
```
1. TrainSeeder
2. StationSeeder
3. RouteSeeder
```

---

## 📂 Struktur File yang Dikerjakan

```
app/
├── Http/Controllers/
│   ├── TrainController.php          ← CRUD + validasi kereta
│   ├── StationController.php        ← CRUD + validasi + routes stasiun
│   └── RouteController.php          ← CRUD + filter + estimasi waktu
├── Interfaces/
│   ├── TrainRepositoryInterface.php ← Kontrak repository kereta
│   ├── StationRepositoryInterface.php ← Kontrak repository stasiun
│   └── RouteRepositoryInterface.php ← Kontrak repository rute
├── Models/
│   ├── Train.php                    ← Model + relasi HasMany
│   ├── Station.php                  ← Model + relasi HasMany (departing/arriving)
│   └── Route.php                    ← Model + relasi BelongsTo + accessor durasi
├── Providers/
│   └── AppServiceProvider.php       ← Binding interface → repository
└── Repositories/
    ├── TrainRepository.php          ← Implementasi CRUD kereta
    ├── StationRepository.php        ← Implementasi CRUD + filter stasiun
    └── RouteRepository.php          ← Implementasi CRUD + filter + estimate rute

database/
├── migrations/
│   ├── 2026_04_14_152802_create_trains_table.php
│   ├── 2026_04_15_095221_create_stations_table.php
│   └── 2026_04_15_095226_create_routes_table.php
└── seeders/
    ├── DatabaseSeeder.php
    ├── TrainSeeder.php              ← 20 data kereta real
    ├── StationSeeder.php            ← 50 data stasiun (38 real + 12 random)
    └── RouteSeeder.php              ← 10.000 data rute random

routes/
└── api.php                          ← Definisi 17 API endpoint

team7_api.postman_collection.json    ← Koleksi Postman untuk testing
```

---

## ✅ Fitur yang Sudah Selesai

- [x] Setup Docker (compose.yaml)
- [x] Migration 3 tabel (trains, stations, routes) dengan foreign key
- [x] Model Eloquent dengan relasi dan casting
- [x] Repository Pattern (Interface + Implementasi)
- [x] Dependency Injection via AppServiceProvider
- [x] CRUD lengkap untuk Train (5 endpoint)
- [x] CRUD lengkap untuk Station (6 endpoint) + filter city/province/is_active
- [x] CRUD lengkap untuk Route (6 endpoint) + filter origin/destination/train/price/is_active
- [x] Fitur Estimasi Waktu Tempuh antar stasiun
- [x] Seeder data realistis (20 kereta, 50 stasiun, 10.000 rute)
- [x] Postman Collection untuk testing API
- [x] Request Validation pada semua endpoint POST/PUT
