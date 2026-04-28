-- ============================================================
-- Alter: LBMST_CLABITEMS — Tambah kolom LOINC + range anak (Kid)
-- ============================================================
--
-- Schema asli siklik untuk LBMST_CLABITEMS punya range nilai normal:
--   LOW_LIMIT_M, HIGH_LIMIT_M  (Male / Pria)
--   LOW_LIMIT_F, HIGH_LIMIT_F  (Female / Wanita)
-- Sirus master-clabitem juga butuh range anak:
--   LOW_LIMIT_K, HIGH_LIMIT_K  (Kid / Anak)
-- + 2 kolom LOINC untuk integrasi SatuSehat:
--   LOINC_CODE, LOINC_DISPLAY
--
-- Total 4 kolom baru.

ALTER TABLE lbmst_clabitems ADD loinc_code    VARCHAR2(20);
ALTER TABLE lbmst_clabitems ADD loinc_display VARCHAR2(200);
ALTER TABLE lbmst_clabitems ADD low_limit_k   NUMBER(15,2);
ALTER TABLE lbmst_clabitems ADD high_limit_k  NUMBER(15,2);

COMMENT ON COLUMN lbmst_clabitems.loinc_code    IS 'Kode LOINC — paket header untuk ServiceRequest/DiagnosticReport, item anak untuk Observation';
COMMENT ON COLUMN lbmst_clabitems.loinc_display IS 'Nama resmi LOINC (contoh: Hemoglobin [Mass/volume] in Blood)';
COMMENT ON COLUMN lbmst_clabitems.low_limit_k   IS 'Batas bawah nilai normal untuk anak (Kid)';
COMMENT ON COLUMN lbmst_clabitems.high_limit_k  IS 'Batas atas nilai normal untuk anak (Kid)';

-- Index untuk lookup by LOINC code
CREATE INDEX idx_clabitem_loinc ON lbmst_clabitems (loinc_code);
