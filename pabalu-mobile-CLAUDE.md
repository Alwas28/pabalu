# CLAUDE.md — Pabalu Mobile (React Native / Expo)

Baca file ini sebelum mengerjakan apapun. Ini adalah konteks lengkap project.

---

## GAMBARAN PROJECT

**Pabalu** adalah sistem manajemen UMKM berbasis web (Laravel) yang sedang dikembangkan
versi mobile-nya untuk kasir. Project mobile ini adalah klien dari Pabalu Web API.

- **Web (backend)** : Laravel 13, PHP 8.3, MySQL — di folder `../pabalu` (atau server)
- **Mobile (ini)**  : React Native + Expo, TypeScript
- **Auth**          : Laravel Sanctum — Bearer Token
- **API Base URL**  : Sesuaikan di `src/config/api.ts` (lihat bagian Konfigurasi)

---

## STACK MOBILE

```
Framework  : React Native + Expo (managed workflow)
Bahasa     : TypeScript
HTTP       : axios
Navigasi   : @react-navigation/native + @react-navigation/stack
Storage    : @react-native-async-storage/async-storage  (simpan token + preferensi tema)
UI Icons   : @expo/vector-icons (sudah include di Expo)
```

---

---

## SISTEM TEMA (WAJIB DIIMPLEMENTASI)

Aplikasi harus mendukung **dark/light mode** dan **10 pilihan warna aksen**.
Preferensi disimpan di AsyncStorage dan diterapkan global lewat ThemeContext.

### Palette Warna Aksen (sama persis dengan web Pabalu)

```typescript
// src/config/theme.ts

export type AccentId =
  | 'amber' | 'emerald' | 'blue' | 'violet' | 'rose'
  | 'cyan'  | 'lime'    | 'pink' | 'orange' | 'sky';

export interface AccentColor {
  id: AccentId;
  label: string;
  primary: string;   // warna utama (tombol, aktif, highlight)
  secondary: string; // warna gradien / pasangan
  light: string;     // versi transparan untuk background lembut
}

export const ACCENT_PALETTE: AccentColor[] = [
  { id: 'amber',   label: 'Amber',  primary: '#f59e0b', secondary: '#ef4444', light: 'rgba(245,158,11,0.14)' },
  { id: 'emerald', label: 'Hijau',  primary: '#10b981', secondary: '#06b6d4', light: 'rgba(16,185,129,0.14)'  },
  { id: 'blue',    label: 'Biru',   primary: '#4f6ef7', secondary: '#7c3aed', light: 'rgba(79,110,247,0.14)'  },
  { id: 'violet',  label: 'Ungu',   primary: '#8b5cf6', secondary: '#ec4899', light: 'rgba(139,92,246,0.14)'  },
  { id: 'rose',    label: 'Merah',  primary: '#f43f5e', secondary: '#f97316', light: 'rgba(244,63,94,0.14)'   },
  { id: 'cyan',    label: 'Cyan',   primary: '#06b6d4', secondary: '#3b82f6', light: 'rgba(6,182,212,0.14)'   },
  { id: 'lime',    label: 'Lime',   primary: '#84cc16', secondary: '#10b981', light: 'rgba(132,204,22,0.14)'  },
  { id: 'pink',    label: 'Pink',   primary: '#ec4899', secondary: '#8b5cf6', light: 'rgba(236,72,153,0.14)'  },
  { id: 'orange',  label: 'Oranye', primary: '#f97316', secondary: '#f59e0b', light: 'rgba(249,115,22,0.14)'  },
  { id: 'sky',     label: 'Sky',    primary: '#38bdf8', secondary: '#6366f1', light: 'rgba(56,189,248,0.14)'  },
];

export const DEFAULT_ACCENT: AccentId = 'amber'; // default sama dengan web
```

### Warna Background per Mode (sama dengan web Pabalu)

```typescript
export interface ThemeColors {
  // Background
  bg: string;        // halaman utama
  surface: string;   // card, modal, sidebar
  surface2: string;  // input, row table, badge
  border: string;    // garis pemisah

  // Text
  text: string;      // teks utama
  muted: string;     // teks sekunder
  sub: string;       // teks tersier, placeholder

  // Status (sama di dark/light)
  success: string;
  warning: string;
  danger: string;
  info: string;
}

export const DARK_COLORS: ThemeColors = {
  bg:       '#0f1117',
  surface:  '#161b27',
  surface2: '#1c2336',
  border:   '#252d42',
  text:     '#e2e8f0',
  muted:    '#64748b',
  sub:      '#94a3b8',
  success:  '#22c55e',
  warning:  '#f59e0b',
  danger:   '#ef4444',
  info:     '#3b82f6',
};

export const LIGHT_COLORS: ThemeColors = {
  bg:       '#f1f5f9',
  surface:  '#ffffff',
  surface2: '#f8fafc',
  border:   '#e2e8f0',
  text:     '#1e293b',
  muted:    '#94a3b8',
  sub:      '#64748b',
  success:  '#16a34a',
  warning:  '#d97706',
  danger:   '#dc2626',
  info:     '#2563eb',
};
```

### ThemeContext (`src/context/ThemeContext.tsx`)

```typescript
import React, { createContext, useContext, useEffect, useState } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useColorScheme } from 'react-native';
import {
  ACCENT_PALETTE, DARK_COLORS, LIGHT_COLORS,
  AccentId, AccentColor, ThemeColors, DEFAULT_ACCENT
} from '../config/theme';

type Mode = 'dark' | 'light' | 'system';

interface ThemeContextType {
  mode: Mode;
  isDark: boolean;
  colors: ThemeColors;
  accent: AccentColor;
  setMode: (m: Mode) => void;
  setAccent: (id: AccentId) => void;
}

const ThemeContext = createContext<ThemeContextType>({} as ThemeContextType);

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  const systemScheme = useColorScheme();
  const [mode, setModeState] = useState<Mode>('dark');
  const [accentId, setAccentId] = useState<AccentId>(DEFAULT_ACCENT);

  // Load preferensi tersimpan saat app buka
  useEffect(() => {
    AsyncStorage.multiGet(['pb-theme', 'pb-accent']).then(([theme, accent]) => {
      if (theme[1]) setModeState(theme[1] as Mode);
      if (accent[1]) setAccentId(accent[1] as AccentId);
    });
  }, []);

  const isDark = mode === 'system' ? systemScheme === 'dark' : mode === 'dark';
  const colors = isDark ? DARK_COLORS : LIGHT_COLORS;
  const accent = ACCENT_PALETTE.find(a => a.id === accentId) ?? ACCENT_PALETTE[0];

  const setMode = async (m: Mode) => {
    setModeState(m);
    await AsyncStorage.setItem('pb-theme', m);
  };

  const setAccent = async (id: AccentId) => {
    setAccentId(id);
    await AsyncStorage.setItem('pb-accent', id);
  };

  return (
    <ThemeContext.Provider value={{ mode, isDark, colors, accent, setMode, setAccent }}>
      {children}
    </ThemeContext.Provider>
  );
}

export const useTheme = () => useContext(ThemeContext);
```

### Cara Pakai di Komponen

```typescript
import { useTheme } from '../context/ThemeContext';

export default function MyScreen() {
  const { colors, accent, isDark } = useTheme();

  return (
    <View style={{ flex: 1, backgroundColor: colors.bg }}>

      {/* Card */}
      <View style={{
        backgroundColor: colors.surface,
        borderColor: colors.border,
        borderWidth: 1,
        borderRadius: 12,
        padding: 16,
      }}>
        <Text style={{ color: colors.text, fontWeight: '600' }}>Judul</Text>
        <Text style={{ color: colors.muted }}>Keterangan</Text>
      </View>

      {/* Tombol dengan warna aksen */}
      <TouchableOpacity style={{
        backgroundColor: accent.primary,
        borderRadius: 10,
        padding: 14,
      }}>
        <Text style={{ color: '#fff', fontWeight: '700' }}>Simpan</Text>
      </TouchableOpacity>

      {/* Badge / chip aksen lembut */}
      <View style={{ backgroundColor: accent.light, borderRadius: 8, padding: 8 }}>
        <Text style={{ color: accent.primary }}>Aktif</Text>
      </View>

    </View>
  );
}
```

### Halaman Pengaturan Tema (`src/screens/settings/ThemeSettingScreen.tsx`)

Halaman ini wajib ada — user bisa mengubah:
1. **Mode** : Dark / Light / Ikuti Sistem (3 tombol toggle)
2. **Warna Aksen** : Grid 10 swatch bulat (seperti di web Pabalu)

```typescript
// Contoh render swatch aksen
import { ACCENT_PALETTE } from '../../config/theme';
const { accent, setAccent, colors } = useTheme();

<View style={{ flexDirection: 'row', flexWrap: 'wrap', gap: 12 }}>
  {ACCENT_PALETTE.map(a => (
    <TouchableOpacity
      key={a.id}
      onPress={() => setAccent(a.id)}
      style={{
        width: 40, height: 40, borderRadius: 20,
        background: a.primary,  // pakai LinearGradient untuk lebih menarik
        borderWidth: accent.id === a.id ? 3 : 0,
        borderColor: '#fff',
        alignItems: 'center', justifyContent: 'center',
      }}
    >
      {accent.id === a.id && (
        <Ionicons name="checkmark" size={18} color="#fff" />
      )}
    </TouchableOpacity>
  ))}
</View>
```

### Wrap App.tsx dengan ThemeProvider

```typescript
// App.tsx
import { ThemeProvider } from './src/context/ThemeContext';

export default function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <AppNavigator />
      </AuthProvider>
    </ThemeProvider>
  );
}
```

### Storage Keys yang Digunakan

```
pb-theme   → 'dark' | 'light' | 'system'   (default: 'dark')
pb-accent  → AccentId string                (default: 'amber')
token      → Bearer token Sanctum
user       → JSON string data user + features
```

---

## STRUKTUR FOLDER YANG DIGUNAKAN

```
pabalu-mobile/
├── src/
│   ├── config/
│   │   ├── api.ts          ← Base URL dan axios instance
│   │   └── theme.ts        ← Palette warna, DARK_COLORS, LIGHT_COLORS, ACCENT_PALETTE
│   ├── context/
│   │   ├── AuthContext.tsx  ← Global state: token, user, outlet
│   │   └── ThemeContext.tsx ← isDark, colors, accent, setMode, setAccent
│   ├── services/
│   │   ├── auth.ts         ← Login, logout, me
│   │   ├── transaction.ts  ← POS transaksi
│   │   ├── stock.ts        ← Stok & opening
│   │   ├── expense.ts      ← Pengeluaran
│   │   ├── order.ts        ← Antrian order
│   │   ├── closing.ts      ← Closing harian
│   │   ├── product.ts      ← Produk (POS + kelola)
│   │   ├── user.ts         ← Kelola user
│   │   └── report.ts       ← Laporan
│   ├── screens/
│   │   ├── auth/
│   │   │   └── LoginScreen.tsx
│   │   ├── settings/
│   │   │   └── ThemeSettingScreen.tsx  ← Pilih dark/light + swatch 10 warna
│   │   ├── pos/
│   │   │   ├── POSScreen.tsx
│   │   │   └── ReceiptScreen.tsx
│   │   ├── stock/
│   │   │   ├── OpeningStokScreen.tsx
│   │   │   ├── TambahStokScreen.tsx
│   │   │   └── WasteScreen.tsx
│   │   ├── expense/
│   │   │   └── ExpenseScreen.tsx
│   │   ├── order/
│   │   │   └── OrderQueueScreen.tsx
│   │   ├── closing/
│   │   │   └── ClosingScreen.tsx
│   │   ├── report/
│   │   │   ├── SalesReportScreen.tsx
│   │   │   └── ProfitLossScreen.tsx
│   │   └── manage/
│   │       ├── ProductManageScreen.tsx
│   │       └── UserManageScreen.tsx
│   ├── navigation/
│   │   └── AppNavigator.tsx ← Stack + Tab navigator
│   └── types/
│       └── index.ts         ← TypeScript interfaces
├── App.tsx
└── CLAUDE.md (file ini)
```

---

## KONFIGURASI API (`src/config/api.ts`)

```typescript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Ganti dengan URL server production atau IP lokal saat development
// Development lokal: gunakan IP komputer (bukan localhost) misal http://192.168.1.5:8000
export const BASE_URL = 'https://yourdomain.com/api';

const api = axios.create({
  baseURL: BASE_URL,
  headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
  timeout: 15000,
});

// Otomatis attach Bearer token dari storage
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Handle 401 → logout otomatis
api.interceptors.response.use(
  (res) => res,
  async (err) => {
    if (err.response?.status === 401) {
      await AsyncStorage.removeItem('token');
      // Navigasi ke login ditangani oleh AuthContext
    }
    return Promise.reject(err);
  }
);

export default api;
```

---

## AUTENTIKASI

### Login
```
POST /api/auth/login
Body: { email, password, device_name? }

Response 200:
{
  "token": "1|abc...",
  "user": {
    "id": 1,
    "name": "Budi",
    "email": "...",
    "role": "kasir",          // string tunggal: kasir | owner | admin
    "outlet_id": 2,           // null jika owner (punya banyak outlet)
    "account_type": "regular",
    "permissions": [...],
    "features": {
      "pos": true,
      "opening_stok": true,
      "tambah_stok": true,
      "waste": true,
      "antrian_order": true,
      "pengeluaran": true,
      "closing": true,
      "riwayat_transaksi": true,
      "laporan": false,            // true untuk owner/admin
      "kelola_produk": false,      // true untuk owner/admin
      "kelola_user": false,        // true untuk owner/admin
      "kelola_outlet": false,      // true untuk admin saja
      "lihat_semua_transaksi": false,
      "lihat_semua_expense": false,
      "lihat_expense_laporan": false,
      "multi_outlet": false        // true untuk owner/admin (bisa pilih outlet)
    }
  }
}
```
Simpan token ke AsyncStorage dengan key `"token"`.
Simpan data user ke AsyncStorage dengan key `"user"`.

### Cara pakai features di mobile
```typescript
// Baca features dari user yang tersimpan
const user = JSON.parse(await AsyncStorage.getItem('user'));
const f = user.features;

// Contoh: tampilkan menu hanya jika punya akses
if (f.laporan)       showMenu('Laporan');
if (f.kelola_produk) showMenu('Kelola Produk');
if (f.kelola_user)   showMenu('Kelola User');
if (f.multi_outlet)  showOutletPicker();  // kasir: langsung pakai outlet_id miliknya
```

### Perbedaan fitur per role
```
KASIR  → features dasar semua true, laporan/kelola_produk/kelola_user = false
OWNER  → semua true kecuali kelola_outlet
ADMIN  → semua true
```

### Logout
```
POST /api/auth/logout
Header: Authorization: Bearer {token}
Response: { "message": "Logout berhasil." }
```

### Cek user aktif
```
GET /api/auth/me
Response: { "id", "name", "email", "roles", "outlet_id", "outlet_nama", "permissions" }
```

---

## SEMUA ENDPOINT API

### OUTLET
```
GET  /api/outlets
  → Daftar outlet yang bisa diakses user login
  → Kasir: 1 outlet | Owner: semua outletnya
  Response: [{ id, nama, alamat, telepon }]
```

### PRODUK (untuk POS)
```
GET  /api/products?outlet_id={id}
  → Produk aktif + stok realtime hari ini
  Response: { products: [{ id, nama, harga, stok, category_id, category, foto }], categories: [] }
```

### TRANSAKSI
```
GET  /api/transactions/config?outlet_id={id}
  → Metode bayar aktif + Midtrans client key
  Response: { active_methods: [], midtrans_client_key, midtrans_snap_url }

POST /api/transactions/snap-token
  Body: { outlet_id, items: [{ product_id, nama, harga, qty, subtotal }] }
  Response: { snap_token, order_id }

POST /api/transactions
  Body: {
    outlet_id,           // auto-fill untuk kasir
    metode_bayar,        // tunai | qris | transfer | gateway
    bayar,               // wajib jika tunai
    items: [{ product_id, nama, harga, qty, subtotal }],
    keterangan?,
    payment_ref?         // wajib jika gateway (order_id dari snap-token)
  }
  Response 201: { success, id, nomor, total, bayar, kembalian, metode, tanggal }

GET  /api/transactions/{id}
  → Detail transaksi untuk struk cetak
  Response: { id, nomor_transaksi, tanggal, outlet, kasir, metode_bayar, total, bayar, kembalian, items }

GET  /api/transactions?outlet_id={id}&tanggal={date}
  → Riwayat transaksi hari ini
  Response: [{ id, nomor_transaksi, outlet, total, metode_bayar, status, items_count }]
```

### STOK
```
GET  /api/stock?outlet_id={id}
  → Stok saat ini per produk
  Response: [{ id, nama, category, stok }]

GET  /api/stock/opening?outlet_id={id}
  → Pre-fill form opening stok
  Response: { tanggal, products: [{ id, nama, category, qty_opening, stok_sekarang }] }

POST /api/stock/opening
  Body: {
    outlet_id,           // auto-fill untuk kasir
    tanggal,
    items: [{ product_id, qty, keterangan? }]
  }
  Response 201: { message }
  CATATAN: qty=0 akan dilewati. updateOrCreate — aman dipanggil ulang.

POST /api/stock/in
  Body: { outlet_id, product_id, tanggal, qty, keterangan? }
  Response 201: { message, id }

POST /api/stock/waste
  Body: { outlet_id, product_id, tanggal, qty, keterangan? }
  Response 201: { message, id }

GET  /api/stock/history?outlet_id={id}&type={}&product_id={}&date_from={}&date_to={}
  → Riwayat pergerakan stok (max 100)
  type: opening | in | waste
  Response: [{ id, type, product, qty, tanggal, keterangan, user }]
```

### PENGELUARAN
```
GET  /api/expenses?outlet_id={id}&tanggal={date}&kategori={}
  Response: { tanggal, total, expenses: [{ id, tanggal, kategori, keterangan, jumlah, user }], kategori_list }

POST /api/expenses
  Body: { outlet_id, tanggal, kategori, keterangan?, jumlah }
  kategori: operasional | bahan_baku | gaji | utilitas | promosi | peralatan | lainnya
  Response 201: { message, id }

PUT  /api/expenses/{id}
  Body: { tanggal, kategori, keterangan?, jumlah }
  Response: { message }

DELETE /api/expenses/{id}
  Response: { message }
```

### ANTRIAN ORDER
```
GET  /api/orders?outlet_id={id}&status={}
  status: active (default) | all | pending | processing | ready | completed | cancelled
  Response: {
    stats: { pending, processing, ready },
    orders: [{ id, order_number, customer_name, customer_phone, catatan, subtotal,
               order_status, status_label, next_status, next_label, created_at, items }]
  }

GET  /api/orders/poll?outlet_id={id}&since={iso_timestamp}
  → Polling ringan cek order baru (jalankan setiap 15 detik)
  Response: { new_count, pending, now }
  CARA PAKAI: simpan "now", kirim sebagai "since" di poll berikutnya.

POST /api/orders/{id}/advance
  → Majukan status: pending→processing→ready→completed
  Response: { message, order }

POST /api/orders/{id}/cancel
  Response: { message }
```

### CLOSING HARIAN
```
GET  /api/closing?outlet_id={id}&tanggal={date}
  Response: {
    tanggal, omzet, total_transaksi, total_expense, laba_kotor,
    per_metode: { tunai: { jumlah, total }, qris: {...} },
    expense_per_kategori: { operasional: 50000, ... },
    stock_summary: [{ product_id, nama, category, opening, in, waste, sold, akhir }]
  }
```

### KELOLA PRODUK
```
GET  /api/manage/products?outlet_id={id}&q={}
  Response: { products: [{ id, kode, nama, harga, satuan, is_active, category_id, category, foto, deskripsi }], categories }

POST /api/manage/products          (Content-Type: multipart/form-data jika ada foto)
  Body: { outlet_id, nama, harga_jual, satuan, category_id?, kode?, deskripsi?, is_active?, gambar? }
  Response 201: { message, id }

PUT  /api/manage/products/{id}     (multipart/form-data jika ada foto)
  Body: { nama, harga_jual, satuan, category_id?, kode?, is_active?, gambar?, hapus_gambar? }
  Response: { message }

DELETE /api/manage/products/{id}
  Response: { message }
```

### KELOLA USER
```
GET  /api/users?q={}&role={}
  Response: { users: [{ id, name, email, outlet_id, roles, is_active, jabatan, no_hp }], available_roles }

POST /api/users
  Body: { name, email, password, role?, outlet_id?, jabatan?, no_hp? }
  Response 201: { message, id }

PUT  /api/users/{id}
  Body: { name, email, password?, role?, outlet_id?, jabatan?, no_hp? }
  Response: { message }

DELETE /api/users/{id}
  Response: { message }
```

### LAPORAN
```
GET  /api/reports/sales?outlet_id={id}&date_from={}&date_to={}
  Response: {
    date_from, date_to, total_omzet, total_transaksi,
    per_hari: [{ tanggal, jumlah, omzet }],
    per_produk: [{ nama, total_qty, total_subtotal }]
  }

GET  /api/reports/profit-loss?outlet_id={id}&date_from={}&date_to={}
  Response: {
    date_from, date_to, total_omzet, total_expense, total_laba,
    per_hari: [{ tanggal, omzet, expense, laba }],
    expense_per_kategori: [{ kategori, total }]
  }
```

---

## ROLE & LOGIKA PENTING

### Kasir
- `outlet_id` sudah terikat — **tidak perlu dikirim**, API auto-fill
- Di laporan hanya melihat transaksi miliknya sendiri
- Tidak bisa kelola user

### Owner
- Wajib kirim `outlet_id` di setiap request
- Bisa kelola produk, user (kasir saja), pengeluaran di outlet miliknya

### Cek role & akses di mobile
```typescript
const user = JSON.parse(await AsyncStorage.getItem('user') ?? '{}');

// Cek role
const isKasir = user.role === 'kasir';
const isOwner = user.role === 'owner';
const isAdmin = user.role === 'admin';

// Cek fitur (GUNAKAN INI untuk show/hide UI)
const canSeeReport    = user.features?.laporan ?? false;
const canManageProduct= user.features?.kelola_produk ?? false;
const canManageUser   = user.features?.kelola_user ?? false;
const hasMultiOutlet  = user.features?.multi_outlet ?? false;

// Outlet: kasir sudah punya outlet_id tetap, owner harus pilih
const outletId = isKasir ? user.outlet_id : selectedOutletId;
```

---

## LOGIKA STOK

```
stok_akhir = opening + in - waste - sold
"sold" = otomatis dari transaksi paid, tidak perlu input manual
```

---

## ERROR HANDLING STANDAR

```typescript
try {
  const res = await api.post('/transactions', data);
  // sukses
} catch (err: any) {
  if (err.response?.status === 422) {
    // Validation error
    const errors = err.response.data.errors;  // object
    const message = err.response.data.message;
  } else if (err.response?.status === 403) {
    // Akses ditolak
  } else if (err.response?.status === 401) {
    // Token expired → redirect login
  }
}
```

---

## DEPENDENCIES YANG PERLU DIINSTALL

```bash
npx expo install axios
npx expo install @react-native-async-storage/async-storage
npx expo install @react-navigation/native @react-navigation/stack
npx expo install react-native-screens react-native-safe-area-context
npx expo install @react-navigation/bottom-tabs
```

---

## CATATAN DEVELOPMENT

1. **Localhost tidak bisa diakses dari HP/emulator** — gunakan IP lokal komputer:
   `http://192.168.x.x:8000/api`
   Jalankan `ipconfig` di Windows untuk cari IP.

2. **CORS sudah dikonfigurasi** di Laravel — mobile bisa akses langsung.

3. **Upload foto** harus pakai `multipart/form-data`, bukan JSON:
   ```typescript
   const form = new FormData();
   form.append('gambar', { uri, name: 'foto.jpg', type: 'image/jpeg' } as any);
   await api.post('/manage/products', form, {
     headers: { 'Content-Type': 'multipart/form-data' }
   });
   ```

4. **Midtrans di mobile** — Snap.js adalah SDK browser, TIDAK bisa dipakai langsung di RN.
   Gunakan salah satu dari dua cara:
   - **WebView**: `POST /api/transactions/snap-token` → dapat `snap_redirect_url` → buka di `expo-web-browser`
   - **QRIS Native**: `POST /api/transactions/qris-charge` → dapat `qr_string` / `qr_image_url` → tampilkan QR di app
   Setelah bayar, polling `GET /api/transactions/payment-status?order_id=&outlet_id=` setiap 3 detik.
   Jika `is_paid: true` → simpan ke `POST /api/transactions` dengan `metode_bayar: 'gateway'`.

5. **Polling order** — gunakan `setInterval` + `clearInterval` saat komponen unmount:
   ```typescript
   useEffect(() => {
     const interval = setInterval(() => pollOrders(), 15000);
     return () => clearInterval(interval);
   }, []);
   ```
