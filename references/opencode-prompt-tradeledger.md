# Prompt untuk Opencode — Build "TradeLedger" Trading Portfolio Manager

Cara pakai: paste seluruh isi file ini ke Opencode sebagai instruksi awal (initial prompt / project brief). Kerjakan per-PHASE secara berurutan, jangan loncat — setiap phase punya "Definition of Done" yang harus dicek sebelum lanjut ke phase berikutnya. Kalau context window terbatas, paste satu phase per prompt terpisah, tapi tetap sertakan bagian "Project Context" di setiap prompt baru.

---

## Project Context (sertakan di setiap prompt)

Kamu membantu membangun **TradeLedger**, aplikasi personal portfolio trading management untuk mencatat, memantau, dan mengontrol aktivitas trading harian (fokus market XAUUSD / forex, framing Smart Money Concepts). Aplikasi ini dipakai personal, single-user tapi harus mendukung multi akun (Real Account dan Demo Account) yang datanya terisolasi penuh satu sama lain.

**Tech stack wajib:**
- Backend: Laravel 12 (PHP)
- Database: MySQL via Laragon (local dev)
- Frontend interactivity: Livewire 3 + Alpine.js (bukan Inertia/React kecuali diminta ulang)
- Styling: Tailwind CSS
- Chart: ApexCharts
- Font: Space Grotesk (display/heading), Inter (body), JetBrains Mono (semua angka finansial)
- Auth: Laravel Breeze (Livewire stack) sebagai basis, dikustomisasi untuk multi-akun

**Gaya visual wajib (jangan menyimpang):**
- Tema dasar hitam-putih monokrom, dengan mode Dark (default) dan Light, toggle-able dan tersimpan di preferensi user
- Efek **glassmorphism**: card dengan `backdrop-blur`, background semi-transparan (`bg-white/5` dark, `bg-white/50` light), border tipis `border-white/10`
- Warna hijau (`#20e3a2`) dipakai HANYA untuk sinyal profit/positif, merah (`#ff5470`) HANYA untuk loss/negatif, kuning-emas (`#e8c468`) untuk target/warning. Tidak ada warna lain di luar palet ini.
- Tidak ada gradient dekoratif berlebihan, tidak ada shadow tebal — flat glass look
- Layout harus fully responsive: sidebar nav collapse jadi bottom nav / hamburger di mobile, tabel bisa horizontal-scroll di layar kecil
- Referensi desain: ada file mockup HTML statis (`trading-dashboard-mockup.html`) yang sudah dibuat sebelumnya — pakai itu sebagai acuan warna, font, dan komponen (glass card, ring progress target, status chip, stat card). Kalau file itu tersedia di project, baca dan ekstrak token warnanya dulu sebelum mulai styling.

**Struktur data acuan** (berdasarkan Excel trading tracker existing user): setiap baris harian punya Status (Profit/Loss/Day Off), Daily %, Day (nama hari), Tanggal, Balance, Target 5%, Running 5%, Target Closing 5%, Status 5%, Target 10%, Running 10%, Target Closing 10%, Status 10%, Profit, Loss, dan ada bagian terpisah untuk Deposit dan Withdrawal (WD). Ada juga ringkasan bulanan: Total Profits, Total Loss, P/L Nett.

Jangan generate kode dulu sebelum PHASE 0 selesai dikonfirmasi.

---

## PHASE 0 — Setup Project & Struktur Dasar

Tugas:
1. Buat project Laravel 12 baru, konfigurasi `.env` untuk MySQL Laragon (host `127.0.0.1`, port `3306`, database `tradeledger`).
2. Install dan konfigurasi: Laravel Breeze (Livewire stack), Livewire 3, Alpine.js, Tailwind CSS.
3. Setup font custom (Space Grotesk, Inter, JetBrains Mono) via Google Fonts di `resources/css/app.css` atau layout utama.
4. Konfigurasi Tailwind `tailwind.config.js`: extend `colors` dengan token `ink`, `paper`, `profit`, `loss`, `target` sesuai palet di atas. Extend `fontFamily` dengan 3 font di atas. Aktifkan `darkMode: 'class'`.
5. Buat struktur folder dasar: `app/Livewire/`, `app/Models/`, `database/migrations/`, `resources/views/livewire/`.
6. Buat file `resources/views/components/layouts/app.blade.php` sebagai base layout dengan: toggle dark/light mode via Alpine (`x-data` + `localStorage`), sidebar placeholder, slot konten utama.

**Definition of Done:** `php artisan serve` jalan tanpa error, halaman kosong dengan layout dasar tampil, toggle dark/light berfungsi dan warna background berubah (ink vs paper).

---

## PHASE 1 — Database Schema & Model

Tugas:
1. Buat migration untuk tabel berikut (sesuaikan nama kolom snake_case Laravel):
   - `accounts` — id, user_id (FK), name, type (enum: `real`, `demo`), initial_balance (decimal 12,2), current_balance (decimal 12,2), currency (default `USD`), is_active (boolean), timestamps
   - `daily_logs` — id, account_id (FK), log_date (date), day_name (string, bisa di-generate otomatis dari log_date, tidak perlu disimpan manual), status (enum: `profit`, `loss`, `day_off`, `pending`), balance (decimal 12,2), daily_percent (decimal 5,2, nullable), profit_amount (decimal 12,2, default 0), loss_amount (decimal 12,2, default 0), notes (text, nullable), timestamps
   - `targets` — id, account_id (FK), daily_log_id (FK nullable), target_type (enum: `5pct`, `10pct`), target_amount (decimal 12,2), running_amount (decimal 12,2), target_closing (decimal 12,2), status (decimal 12,2, nullable — nilai selisih), timestamps
   - `transactions` — id, account_id (FK), type (enum: `deposit`, `withdrawal`), amount (decimal 12,2), transaction_date (date), notes (text, nullable), timestamps
   - Tambahkan kolom `theme_preference` (enum `dark`,`light`, default `dark`) dan `active_account_id` (nullable FK) ke tabel `users` bawaan Breeze.
2. Buat Eloquent Model untuk masing-masing tabel lengkap dengan relasi (`User hasMany Account`, `Account hasMany DailyLog`, `DailyLog hasMany Target`, `Account hasMany Transaction`), `$fillable`, dan `$casts` yang sesuai tipe data (decimal, date, enum).
3. Buat factory + seeder yang menghasilkan data dummy realistis mengikuti pola dari Excel (beberapa hari profit, beberapa loss, beberapa day off Sabtu/Minggu, running balance yang konsisten dengan profit/loss).

**Definition of Done:** `php artisan migrate:fresh --seed` sukses tanpa error, cek via `php artisan tinker` bahwa relasi antar model berjalan (`Account::first()->dailyLogs`).

---

## PHASE 2 — Autentikasi & Multi-Akun (Real/Demo)

Tugas:
1. Kustomisasi halaman login Breeze: tambahkan toggle "Real Account" / "Demo Account" di atas form login sesuai desain mockup (pill toggle dengan warna hijau untuk Real, kuning-emas untuk Demo).
2. Setelah login sukses, set session `active_account_type` berdasarkan pilihan toggle, dan arahkan user ke account dengan tipe tersebut (buatkan otomatis jika user belum punya account dengan tipe itu — starter balance $0).
3. Buat komponen switcher akun di dalam dashboard (dropdown di topbar) supaya user bisa pindah Real ↔ Demo tanpa logout, dengan konfirmasi modal sederhana karena data akan berbeda total.
4. Pastikan semua query data (daily logs, targets, transactions) selalu di-scope ke `active_account_id` yang sedang aktif — buat global scope atau middleware untuk ini supaya tidak ada kebocoran data antar akun.

**Definition of Done:** Login dengan toggle Real menampilkan data akun real, switch ke Demo menampilkan data berbeda, refresh halaman tetap mempertahankan akun aktif (via session/db).

---

## PHASE 3 — Dashboard Overview (Livewire Component)

Tugas:
1. Buat Livewire component `DashboardOverview` yang menampilkan:
   - 4 stat card (glass style): Total Profits, Total Loss, P/L Nett, Balance saat ini — dihitung real-time dari data `daily_logs` dan `transactions` akun aktif (bulan berjalan, dengan filter bulan yang bisa diganti)
   - Equity curve chart pakai ApexCharts (area chart, warna hijau, tema gelap/terang mengikuti toggle), data dari `balance` harian
   - Dua ring progress (SVG, seperti mockup) untuk Target 5% dan Target 10% hari ini, warna dan persentase dihitung dari data `targets`
2. Buat Blade view sesuai layout mockup: sidebar kiri (nav: Overview, Daily Log, Target & Rules, Deposit/WD, Analytics, Journal, Pengaturan), topbar (judul halaman, account pill, toggle dark/light), grid stat card, grid chart+ring, semuanya pakai glass card style.
3. Pastikan chart dan ring re-render dengan benar saat toggle dark/light (ApexCharts butuh re-init theme, bukan cuma CSS).

**Definition of Done:** Dashboard menampilkan data asli dari seeder, angka di stat card sesuai kalkulasi database, chart dan ring responsif terhadap perubahan data dan tema.

---

## PHASE 4 — Daily Log (CRUD Utama)

Tugas:
1. Buat Livewire component `DailyLogTable` dengan tabel penuh (Status, Day, Tanggal, Balance, Running 5%, Running 10%, Profit, Loss) — styling mengikuti mockup: status chip berwarna, angka pakai font mono, warna hijau/merah untuk positif/negatif.
2. Tambahkan fitur:
   - Filter by bulan/tahun, filter by status (Profit/Loss/Day Off)
   - Form tambah/edit entry harian (modal atau slide-over pakai Alpine), input: tanggal, status, balance, profit/loss amount, catatan
   - Auto-kalkulasi Running 5%/10% dan status target begitu entry baru disimpan (buat Service class `TargetCalculationService` terpisah dari controller/Livewire component supaya logic testable)
   - Hapus entry dengan konfirmasi
3. Pagination atau infinite scroll untuk data banyak bulan.

**Definition of Done:** User bisa tambah entry harian baru, angka running balance dan target ter-update otomatis di tabel dan di dashboard overview tanpa reload manual (Livewire reactive).

---

## PHASE 5 — Target & Rules Management

Tugas:
1. Halaman untuk set aturan target per akun: persentase target 5%/10% (bisa dikustomisasi, tidak hardcode), target closing, aturan hari libur (default Sabtu-Minggu day off, bisa diubah).
2. Livewire component untuk edit aturan ini, tersimpan di tabel baru `account_rules` (buat migration tambahan jika perlu: account_id, target_5pct, target_10pct, off_days json, timestamps).
3. Pastikan `TargetCalculationService` dari Phase 4 membaca aturan ini, bukan angka hardcode.

**Definition of Done:** Ubah target 5% dari 5% ke 3% di halaman ini langsung mengubah kalkulasi running target di Daily Log dan Dashboard.

---

## PHASE 6 — Deposit / Withdrawal

Tugas:
1. Livewire component untuk mencatat transaksi Deposit dan WD (form + list riwayat), sesuai kolom DEPOSIT/WD di Excel.
2. Balance akun (`accounts.current_balance`) harus ter-update otomatis setiap ada transaksi baru, dan tercermin di stat card dashboard.
3. Tampilkan riwayat transaksi dalam tabel terpisah dengan filter tanggal.

**Definition of Done:** Tambah deposit $50 langsung menaikkan current_balance dan terlihat di dashboard.

---

## PHASE 7 — Analytics

Tugas:
1. Halaman Analytics dengan breakdown: win rate (persentase hari profit vs loss vs day off), rata-rata profit per hari profit, rata-rata loss per hari loss, grafik distribusi profit/loss per bulan (bar chart ApexCharts, hijau untuk profit, merah untuk loss).
2. Filter rentang tanggal custom.

**Definition of Done:** Statistik ter-generate benar dari data `daily_logs` akun aktif, chart interaktif dan sesuai tema dark/light.

---

## PHASE 8 — Journal (Opsional tapi disarankan)

Tugas:
1. Fitur catatan trading harian bebas teks (bisa dikaitkan ke `daily_logs.notes` yang sudah ada, atau tabel `journal_entries` terpisah jika butuh multiple entry per hari dengan rich text).
2. Tampilkan sebagai timeline per tanggal.

**Definition of Done:** User bisa menulis dan membaca kembali catatan trading per tanggal.

---

## PHASE 9 — Responsive & Mobile Polish

Tugas:
1. Uji semua halaman di breakpoint mobile (≤640px), tablet (641–1024px), desktop.
2. Sidebar berubah jadi bottom navigation bar fixed di mobile (ikon saja, label muncul saat aktif), sesuai prinsip di mockup.
3. Tabel Daily Log bisa di-scroll horizontal di mobile tanpa merusak layout, atau opsional: mode card-list di mobile untuk tabel.
4. Cek kontras warna glass card di mode Light tetap terbaca (jangan sampai teks abu-abu pudar tidak terbaca di atas glass putih).
5. Tambahkan PWA manifest dasar (opsional) supaya bisa "Add to Home Screen" di HP.

**Definition of Done:** Semua halaman utama (Dashboard, Daily Log, Target, Deposit/WD, Analytics) nyaman dipakai di layar HP tanpa horizontal overflow yang merusak, dan dark/light mode konsisten di semua breakpoint.

---

## PHASE 10 — Testing & Cleanup

Tugas:
1. Buat feature test dasar untuk: login dengan pilihan akun, isolasi data antar akun (Real tidak bisa lihat data Demo dan sebaliknya), kalkulasi target service.
2. Jalankan `php artisan optimize`, cek tidak ada N+1 query di Dashboard dan Daily Log (pakai `Laravel Debugbar` sementara untuk cek, lalu hapus sebelum production).
3. Review ulang semua warna/font supaya konsisten dengan token di PHASE 0, tidak ada warna liar yang menyimpang dari palet hitam-putih + hijau/merah/kuning-emas.

**Definition of Done:** Test suite hijau semua, tidak ada query berlebih di halaman utama, visual audit selesai.

---

## Catatan tambahan untuk AI agent

- Selalu tanya konfirmasi sebelum mengubah struktur database yang sudah ada di phase sebelumnya.
- Jangan install package tambahan di luar yang disebutkan tanpa menyebutkan alasannya dan minta konfirmasi dulu.
- Tulis kode dengan komentar minimal tapi jelas di bagian logic kalkulasi target (`TargetCalculationService`), karena ini bagian paling krusial dan mudah salah hitung.
- Ikuti konvensi Laravel standar (PSR-12, resource controller pattern jika ada bagian yang butuh controller biasa di luar Livewire).
