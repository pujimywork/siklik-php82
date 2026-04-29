# SQL Setup — Siklik Oracle Database

Folder ini berisi SQL script yang dijalankan **manual** ke Oracle siklik
buat menyiapkan database supaya kompatibel dengan Laravel + fitur SatuSehat.

Jalankan **berurutan** sesuai prefix nomor (`01_`, `02_`, dst).

---

## Prasyarat

- Database Oracle siklik sudah ada (host, port, service name, user/password sudah diset di `.env` siklik-php82).
- 103 tabel core siklik (RSMST_*, RSTXN_*, TKMST_*, TKACC_*, dll) sudah ada di schema `siklik`.
- Tabel Laravel sistem yang sudah ada: `USERS`, `MIGRATIONS`, `FAILED_JOBS`, `PASSWORD_RESET_TOKENS`, `PERSONAL_ACCESS_TOKENS`, plus 5 tabel Spatie permission.

---

## Urutan eksekusi

### 🔧 Mandatory — Laravel system

Jalanin pertama kali di server baru. **Wajib** supaya app bisa boot tanpa error.

| # | File | Fungsi | Idempotent? |
|---|------|--------|-------------|
| **01** | `01_create_laravel_system_tables.sql` | Create 5 tabel sistem yang missing: `SESSIONS`, `CACHE`, `CACHE_LOCKS`, `JOBS`, `JOB_BATCHES` (+ sequence & trigger untuk `JOBS.id`) | ❌ jalan sekali aja (akan error kalau tabel sudah ada) |
| **02** | `02_mark_migrations_as_run.sql` | Insert 14 entry ke `MIGRATIONS` table biar `php artisan migrate` skip tabel yang sudah ada di Oracle siklik | ✅ aman re-run |

### 🌐 Optional — fitur SatuSehat (LOINC + SNOMED)

Jalanin kalau mau aktifkan fitur SatuSehat (kirim data klinis ke Kemenkes via FHIR). Skip kalau belum butuh.

| # | File | Fungsi |
|---|------|--------|
| **03** | `03_create_rsmst_snomed_codes.sql` | Create tabel `rsmst_snomed_codes` — cache SNOMED CT dari FHIR server (tx.fhir.org) |
| **04** | `04_create_rsmst_loinc_codes.sql` | Create tabel `rsmst_loinc_codes` — cache LOINC untuk LOV lab |
| **05** | `05_alter_lbmst_clabitems_add_loinc.sql` | Tambah kolom `loinc_code`, `loinc_display`, `low_limit_k`, `high_limit_k` ke `lbmst_clabitems` |
| **05b** | `05b_alter_lbmst_clabitems_add_kid_range.sql` | Patch: tambah kid range (kalau sudah run versi lama dari 05) |
| **06** | `06_alter_rsmst_radiologis_add_loinc.sql` | Tambah kolom `loinc_code` ke `rsmst_radiologis` |
| **07** | `07_seed_rsmst_loinc_codes_radiologi.sql` | Seed data LOINC untuk radiologi |
| **08** | `08_update_lbmst_clabitems_loinc.sql` | Mapping kode LOINC ke item lab existing |

> **Catatan:** Untuk fitur SatuSehat aktif, butuh juga setup credentials di `.env` (`SATUSEHAT_*`) dan migration Laravel `2026_04_15_100000_create_rsmst_snomed_codes_table.php` perlu dijalankan **atau** dimark via `02_*` script (sekarang belum dimark — biar nggak conflict dengan `03_*`).

### 🩺 Mandatory — fitur klinik pratama

Jalanin sekuensial untuk menyiapkan modul klinik pratama (BPJS PCare cache, mapping kasir, stock opname).

| # | File | Fungsi | Idempotent? |
|---|------|--------|-------------|
| **09** | `09_create_ref_bpjs_table.sql` | Create `REF_BPJS_TABLE` — cache lokal reference BPJS PCare (alergi, kesadaran, prognosa, poli FKTP, status pulang) | ❌ run sekali |
| **10** | `10_alter_users_add_emp_id.sql` | ALTER USERS tambah kolom `EMP_ID` (nullable) — mapping user Laravel ke entity employee | ⚠ jangan dirun kalau langsung pakai 11 (kolom langsung dibikin sebagai `KASIR_ID`) |
| **11** | `11_rename_users_emp_id_to_kasir_id.sql` | RENAME `USERS.EMP_ID` → `USERS.KASIR_ID` agar match `TKMST_KASIRS.kasir_id`. Idempotent — handle 4 kasus (kolom belum ada, masih emp_id, sudah kasir_id, dst) | ✅ aman re-run |
| **12** | `12_create_tktxn_sowhs.sql` | Create `TKTXN_SOWHS` (stock opname warehouse) + RE-CREATE view `TKVIEW_IOSTOCKWHS` (tambah UNION ALL block SO). Pakai untuk fitur Kartu Stock + Opname | ✅ aman re-run |

> **Tip rapi**: untuk server baru bisa langsung skip `10_*` dan jalanin `11_*` saja — file 11 idempotent, akan create `KASIR_ID` langsung kalau kolom belum ada. File 10 dipertahankan untuk audit trail history.

---

## Cara jalanin

### Pakai sqlplus
```bash
cd /path/to/siklik-php82

# 🔧 Mandatory — Laravel system
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/01_create_laravel_system_tables.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/02_mark_migrations_as_run.sql

# 🌐 Optional — SatuSehat (LOINC + SNOMED)
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/03_create_rsmst_snomed_codes.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/04_create_rsmst_loinc_codes.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/05_alter_lbmst_clabitems_add_loinc.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/05b_alter_lbmst_clabitems_add_kid_range.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/06_alter_rsmst_radiologis_add_loinc.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/07_seed_rsmst_loinc_codes_radiologi.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/08_update_lbmst_clabitems_loinc.sql

# 🩺 Mandatory — klinik pratama
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/09_create_ref_bpjs_table.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/10_alter_users_add_emp_id.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/11_rename_users_emp_id_to_kasir_id.sql
sqlplus siklik/siklik@//<host>:1521/<service> @database/sql/12_create_tktxn_sowhs.sql
```

### One-liner (semua sekaligus)
Kalau yakin urutan & kondisi: tinggal loop semua file di folder
```bash
for f in database/sql/[0-9]*.sql; do
    echo "▶ Running $f"
    sqlplus -S siklik/siklik@//<host>:1521/<service> @"$f" || { echo "❌ Failed at $f"; exit 1; }
done
```

### Pakai DBeaver / SQL Developer
Buka file → execute. Pastikan mode "Execute SQL Script" (pakai `;` & `/` separator).

### Verify setelah jalan

```sql
-- 01-02 Laravel system
SELECT table_name FROM user_tables
WHERE table_name IN ('SESSIONS','CACHE','CACHE_LOCKS','JOBS','JOB_BATCHES')
ORDER BY table_name;
SELECT COUNT(*) FROM migrations;  -- harus minimal 14

-- 03-08 SatuSehat (kalau dijalankan)
SELECT table_name FROM user_tables
WHERE table_name IN ('RSMST_SNOMED_CODES','RSMST_LOINC_CODES');
SELECT column_name FROM user_tab_columns
WHERE table_name = 'LBMST_CLABITEMS'
    AND column_name IN ('LOINC_CODE','LOINC_DISPLAY','LOW_LIMIT_K','HIGH_LIMIT_K');

-- 09-12 Klinik pratama
SELECT table_name FROM user_tables
WHERE table_name IN ('REF_BPJS_TABLE','TKTXN_SOWHS');
SELECT column_name FROM user_tab_columns
WHERE table_name = 'USERS' AND column_name = 'KASIR_ID';   -- harus ada
SELECT view_name, status FROM user_views WHERE view_name = 'TKVIEW_IOSTOCKWHS';
SELECT COUNT(*) FROM tkview_iostockwhs WHERE txn_status = 'SO';  -- view harus compile
```

---

## Folder `_dev/` — bukan untuk server install

Subfolder `_dev/` berisi script introspection / debug untuk dev. **Jangan**
dijalankan ke server produksi sebagai bagian dari deployment.

| File | Fungsi |
|------|--------|
| `_dev/describe_master_tables.sql` | Dump struktur kolom + PK + FK + index dari semua master tables (`*MST_*`) supaya bisa diff dgn implementasi siklik-php82 |

---

## Catatan teknis

- **Reserved word `KEY`**: Kolom `KEY` di `CACHE` dan `CACHE_LOCKS` di-create dengan `"KEY"` (uppercase quoted) supaya match query yajra/oci8 driver. Lowercase `"key"` akan trigger ORA-00904.
- **Auto-increment**: `JOBS.id` pakai sequence `jobs_seq` + trigger `jobs_bi` (Oracle 11g style). Untuk Oracle 12c+ bisa pakai `GENERATED BY DEFAULT AS IDENTITY`.
- **Timestamp**: Kolom `last_activity`, `expiration`, `created_at`, dll di tabel sistem **bukan** Oracle DATE — itu UNIX epoch integer, jadi pakai `NUMBER(10)`.
