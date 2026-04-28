-- ============================================================
-- Alter: LBMST_CLABITEMS — tambah Kid (anak) range
-- ============================================================
--
-- One-time patch untuk DB yang sudah jalanin versi LAMA dari
-- 05_alter_lbmst_clabitems_add_loinc.sql (yang cuma punya
-- loinc_code + loinc_display, tanpa kolom kid range).
--
-- Master-clabitem (sirus) butuh LOW_LIMIT_K & HIGH_LIMIT_K untuk
-- range nilai normal anak (selain Pria/M dan Wanita/F).
--
-- Skip kalau pakai versi BARU dari 05 (yang sudah include kid range).
--
-- Cara jalanin:
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/05b_alter_lbmst_clabitems_add_kid_range.sql

ALTER TABLE lbmst_clabitems ADD low_limit_k  NUMBER(15,2);
ALTER TABLE lbmst_clabitems ADD high_limit_k NUMBER(15,2);

COMMENT ON COLUMN lbmst_clabitems.low_limit_k  IS 'Batas bawah nilai normal untuk anak (Kid)';
COMMENT ON COLUMN lbmst_clabitems.high_limit_k IS 'Batas atas nilai normal untuk anak (Kid)';
