-- =========================================================================
-- ref_bpjs_table — cache lokal reference BPJS PCare (klinik pratama)
-- =========================================================================
-- Schema mengikuti siklik-lite (existing di Oracle siklik): hanya 2 kolom.
-- Dipakai sebagai cache LOV BPJS yg jarang berubah (alergi, kesadaran,
-- prognosa, poli FKTP, status pulang, dll). Update via menu Master > Ref BPJS.
--
-- Skip kalau tabel sudah ada di Oracle:
--   SELECT 1 FROM all_tables WHERE table_name='REF_BPJS_TABLE' AND owner='SIKLIK';
-- =========================================================================

CREATE TABLE ref_bpjs_table (
  ref_keterangan VARCHAR2(100 CHAR) NOT NULL,
  ref_json       CLOB,
  CONSTRAINT pk_ref_bpjs_table PRIMARY KEY (ref_keterangan)
);

COMMENT ON TABLE  ref_bpjs_table IS 'Cache lokal reference BPJS PCare (di-sync via Master > Ref BPJS).';
COMMENT ON COLUMN ref_bpjs_table.ref_keterangan IS 'Label kategori, mis. "Kesadaran", "Alergi Makanan", "Prognosa", "PoliFktp".';
COMMENT ON COLUMN ref_bpjs_table.ref_json       IS 'CLOB JSON-encoded list dari response BPJS PCare (response.list).';
