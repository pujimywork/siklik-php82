--------------------------------------------------------------------------------
-- FIX: kolom "key" di CACHE & CACHE_LOCKS salah case.
--
-- Masalah:
--   File DDL pertama bikin kolom dengan nama "key" (lowercase, quoted) →
--   Oracle simpan persis sebagai 'key'.
--   Tapi Laravel yajra/oci8 driver query pakai "KEY" (uppercase, quoted) →
--   Oracle treat sebagai column name 'KEY'.
--   Karena Oracle treats quoted identifiers as case-sensitive, 'key' != 'KEY'
--   → ORA-00904: "KEY": invalid identifier.
--
-- Fix: DROP table CACHE & CACHE_LOCKS lalu CREATE ulang dengan "KEY" uppercase.
--
-- Jalanin sekali:
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/fix_cache_key_column_oracle.sql
--------------------------------------------------------------------------------

-- 1. DROP tabel lama (data cache aman dibuang, baru aja dibuat)
DROP TABLE cache_locks PURGE;
DROP TABLE cache       PURGE;


-- 2. CREATE ulang dengan kolom "KEY" uppercase (cocok dengan query Laravel)
CREATE TABLE cache (
    "KEY"        VARCHAR2(255)  NOT NULL,
    value        CLOB           NOT NULL,
    expiration   NUMBER(10)     NOT NULL,
    CONSTRAINT pk_cache PRIMARY KEY ("KEY")
);


CREATE TABLE cache_locks (
    "KEY"        VARCHAR2(255)  NOT NULL,
    owner        VARCHAR2(255)  NOT NULL,
    expiration   NUMBER(10)     NOT NULL,
    CONSTRAINT pk_cache_locks PRIMARY KEY ("KEY")
);


-- ============================================================
-- VERIFY
-- ============================================================
-- Cek struktur kolom — KEY harus uppercase
SELECT table_name, column_name, data_type, data_length, nullable
FROM user_tab_columns
WHERE table_name IN ('CACHE','CACHE_LOCKS')
ORDER BY table_name, column_id;

COMMIT;
