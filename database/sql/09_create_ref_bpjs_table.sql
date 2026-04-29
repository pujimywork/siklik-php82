-- =========================================================================
-- ref_bpjs_table — cache lokal reference BPJS PCare
-- =========================================================================
-- Pola contek dari siklik-lite. Di-update manual via menu Master > Ref BPJS
-- supaya LOV (alergi, kesadaran, prognosa, dll) nggak hit BPJS API setiap
-- kali butuh.
--
-- ref_keterangan = label kategori (mis. 'Alergi Makanan', 'Kesadaran').
-- ref_json       = CLOB raw response 'list' BPJS (di-encode JSON).
-- =========================================================================

CREATE TABLE ref_bpjs_table (
  ref_keterangan VARCHAR2(100 CHAR) NOT NULL,
  ref_json       CLOB,
  updated_at     TIMESTAMP DEFAULT SYSTIMESTAMP,
  CONSTRAINT pk_ref_bpjs_table PRIMARY KEY (ref_keterangan)
);

COMMENT ON TABLE  ref_bpjs_table IS 'Cache lokal reference BPJS PCare (di-sync via Master > Ref BPJS).';
COMMENT ON COLUMN ref_bpjs_table.ref_keterangan IS 'Label kategori, mis. "Kesadaran", "Alergi Makanan", "Prognosa", "Poli FKTP".';
COMMENT ON COLUMN ref_bpjs_table.ref_json       IS 'CLOB JSON-encoded list dari response BPJS PCare (response.list).';
COMMENT ON COLUMN ref_bpjs_table.updated_at     IS 'Timestamp terakhir di-sync.';
