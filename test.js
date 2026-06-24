import http from 'k6/http';
import { check, sleep, fail } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// ─── Custom Metrics ────────────────────────────────────────────
const errorRate   = new Rate('errors');
const reqDuration = new Trend('req_duration');

// ─── Base Config ───────────────────────────────────────────────
const BASE_URL = __ENV.BASE_URL || 'http://localhost';

// Credentials untuk login (override via environment variable)
const USER_EMAIL    = __ENV.USER_EMAIL    || 'test@example.com';
const USER_PASSWORD = __ENV.USER_PASSWORD || 'password';

// ─── Stress-Test Stages (GET /api/bookings) ────────────────────
//  1. Ramp-up   →  50 VUs dalam 30 detik
//  2. Sustain   →  50 VUs selama 1 menit
//  3. Spike     → 100 VUs dalam 10 detik
//  4. Sustain   → 100 VUs selama 30 detik
//  5. Ramp-down →   0 VUs dalam 20 detik
export const options = {
  stages: [
    { duration: '30s',  target: 50  },   // ramp-up
    { duration: '1m',   target: 50  },   // sustained load
    { duration: '10s',  target: 100 },   // spike
    { duration: '30s',  target: 100 },   // sustained spike
    { duration: '20s',  target: 0   },   // ramp-down
  ],
  thresholds: {
    http_req_duration: ['p(95)<500', 'p(99)<1000'],  // 95% < 500ms, 99% < 1s
    http_req_failed:   ['rate<0.05'],                 // error rate < 5%
    errors:            ['rate<0.05'],                 // custom error rate < 5%
  },
};

// ─── Setup: Login sekali untuk ambil JWT Token ─────────────────
// Login BUKAN bagian dari stress test, hanya untuk dapat token.
// Stress test yang sebenarnya ada di default function (GET bookings).
export function setup() {
  console.log(`Logging in sebagai ${USER_EMAIL} ke ${BASE_URL}...`);

  const loginRes = http.post(
    `${BASE_URL}/api/auth/login`,
    JSON.stringify({
      email:    USER_EMAIL,
      password: USER_PASSWORD,
    }),
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
      },
    }
  );

  if (loginRes.status !== 200) {
    console.error(`Login response: ${loginRes.body}`);
    fail(`LOGIN GAGAL! Status: ${loginRes.status}. Pastikan user ${USER_EMAIL} sudah terdaftar dan server aktif.`);
  }

  const body = loginRes.json();

  // Token dari API berbentuk object: { access_token, token_type, expires_in }
  const accessToken = body.token && body.token.access_token;

  if (!accessToken) {
    console.error(`Login response body: ${JSON.stringify(body)}`);
    fail('LOGIN GAGAL! Response tidak mengandung access_token.');
  }

  console.log(`Login berhasil! access_token didapat (${accessToken.substring(0, 30)}...)`);
  console.log('Memulai stress test GET /api/bookings...');

  return { accessToken: accessToken };
}

// ─── Stress Test: GET /api/bookings ────────────────────────────
// INI adalah bagian stress test utama.
// Setiap VU akan berulang kali hit GET /api/bookings dengan JWT token.
export default function (data) {
  const params = {
    headers: {
      'Accept':        'application/json',
      'Content-Type':  'application/json',
      'Authorization': `Bearer ${data.accessToken}`,
    },
    tags: { name: 'GET_list_bookings' },
  };

  // ── Request utama: GET list bookings ──
  const res = http.get(`${BASE_URL}/api/bookings`, params);

  // Track custom metric
  reqDuration.add(res.timings.duration);

  // Checks khusus endpoint bookings
  const passed = check(res, {
    'status is 200':              (r) => r.status === 200,
    'not 401 unauthorized':       (r) => r.status !== 401,
    'response time < 500ms':      (r) => r.timings.duration < 500,
    'response body is not empty': (r) => r.body && r.body.length > 0,
    'content-type is JSON':       (r) => {
      const ct = r.headers['Content-Type'] || '';
      return ct.includes('application/json');
    },
  });

  // Catat error jika check gagal
  errorRate.add(!passed);

  // Jeda singkat antar request (simulasi user behavior)
  sleep(Math.random() * 1 + 0.5); // 0.5 – 1.5 detik
}
