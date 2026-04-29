-- =============================================================================
-- File   : 12_create_tktxn_sowhs.sql
-- Tujuan : Pasang fitur Stock Opname Warehouse di siklik:
--            (1) CREATE TABLE TKTXN_SOWHS  (catat mutasi opname)
--            (2) RE-CREATE VIEW TKVIEW_IOSTOCKWHS  (tambah UNION ALL SO block)
--          Idempotent — aman dirun ulang.
--
-- Pakai oleh:
--   resources/views/pages/transaksi/gudang/kartu-stock/⚡kartu-stock.blade.php
--   (method simpanOpname → INSERT TKTXN_SOWHS)
--
-- Logic opname:
--   selisih = saldo_akhir_dicatat - stock_fisik
--   selisih > 0  → fisik kurang → INSERT (so_d=0, so_k=selisih)       [keluar]
--   selisih < 0  → fisik lebih  → INSERT (so_d=|selisih|, so_k=0)     [masuk]
--   selisih = 0  → no-op
--
-- Cara pakai:
--   sqlplus user/pass@db @database/sql/12_create_tktxn_sowhs.sql
--   (atau copy-paste ke SQL Developer / Toad / DBeaver)
-- =============================================================================

SET SERVEROUTPUT ON SIZE UNLIMITED;
SET DEFINE OFF;
SET FEEDBACK ON;
SET ECHO OFF;

-- =============================================================================
-- STEP 0 — PRECONDITION CHECK
--   Tabel master & transaksi yg jadi reference harus sudah ada.
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 0/4: Precondition check                              ║
PROMPT ╚════════════════════════════════════════════════════════════╝

DECLARE
    v_missing  VARCHAR2(500) := '';
    v_count    NUMBER;
    PROCEDURE check_table(p_name VARCHAR2) IS
    BEGIN
        SELECT COUNT(*) INTO v_count FROM USER_TABLES WHERE TABLE_NAME = p_name;
        IF v_count = 0 THEN
            v_missing := v_missing || p_name || ', ';
        END IF;
    END;
BEGIN
    check_table('TKMST_PRODUCTS');
    check_table('TKMST_KASIRS');
    check_table('TKTXN_RCVHDRS');
    check_table('TKTXN_RCVDTLS');
    check_table('TKTXN_SLSHDRS');
    check_table('TKTXN_SLSDTLS');
    check_table('RSTXN_RJHDRS');
    check_table('RSTXN_RJOBATS');

    IF LENGTH(v_missing) > 0 THEN
        RAISE_APPLICATION_ERROR(-20001,
            'Precondition gagal — tabel berikut belum ada: ' || RTRIM(v_missing, ', '));
    END IF;
    DBMS_OUTPUT.PUT_LINE('  ✓ Semua tabel reference sudah ada.');
END;
/

-- =============================================================================
-- STEP 1 — DROP & CREATE TABLE TKTXN_SOWHS
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 1/4: Create TKTXN_SOWHS                              ║
PROMPT ╚════════════════════════════════════════════════════════════╝

DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count FROM USER_TABLES WHERE TABLE_NAME = 'TKTXN_SOWHS';
    IF v_count > 0 THEN
        EXECUTE IMMEDIATE 'DROP TABLE TKTXN_SOWHS CASCADE CONSTRAINTS';
        DBMS_OUTPUT.PUT_LINE('  ⚠ TKTXN_SOWHS lama di-drop.');
    END IF;
END;
/

CREATE TABLE TKTXN_SOWHS (
    SO_NO       NUMBER          NOT NULL,
    PRODUCT_ID  VARCHAR2(20)    NOT NULL,
    SO_DATE     DATE            DEFAULT SYSDATE,
    SO_D        NUMBER          DEFAULT 0,
    SO_K        NUMBER          DEFAULT 0,
    KASIR_ID    VARCHAR2(20),
    SO_DESC     VARCHAR2(100)   DEFAULT 'SO',
    CONSTRAINT PK_TKTXN_SOWHS PRIMARY KEY (SO_NO)
);

ALTER TABLE TKTXN_SOWHS
    ADD CONSTRAINT FK_TKTXN_SOWHS_PRODUCT
    FOREIGN KEY (PRODUCT_ID) REFERENCES TKMST_PRODUCTS(PRODUCT_ID);

ALTER TABLE TKTXN_SOWHS
    ADD CONSTRAINT FK_TKTXN_SOWHS_KASIR
    FOREIGN KEY (KASIR_ID) REFERENCES TKMST_KASIRS(KASIR_ID);

CREATE INDEX IDX_TKTXN_SOWHS_PRODUCT_DATE
    ON TKTXN_SOWHS(PRODUCT_ID, SO_DATE);

COMMENT ON TABLE  TKTXN_SOWHS IS
    'Stock Opname Warehouse — catat selisih hasil opname per produk. INSERT-only.';
COMMENT ON COLUMN TKTXN_SOWHS.SO_NO      IS 'PK auto-increment via NVL(MAX(so_no),0)+1 di app';
COMMENT ON COLUMN TKTXN_SOWHS.PRODUCT_ID IS 'FK → TKMST_PRODUCTS';
COMMENT ON COLUMN TKTXN_SOWHS.SO_DATE    IS 'Tanggal opname (default SYSDATE)';
COMMENT ON COLUMN TKTXN_SOWHS.SO_D       IS 'Debit/Masuk — fisik LEBIH dari catatan (selisih<0 → so_d=|selisih|)';
COMMENT ON COLUMN TKTXN_SOWHS.SO_K       IS 'Kredit/Keluar — fisik KURANG dari catatan (selisih>0 → so_k=selisih)';
COMMENT ON COLUMN TKTXN_SOWHS.KASIR_ID   IS 'FK → TKMST_KASIRS (kasir yg lakukan opname)';
COMMENT ON COLUMN TKTXN_SOWHS.SO_DESC    IS 'Default ''SO''';

-- =============================================================================
-- STEP 2 — RE-CREATE VIEW TKVIEW_IOSTOCKWHS
--   Existing 3 block (RCV, SLS, RJ) dipertahankan persis, tambah block SO.
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 2/4: Re-create VIEW TKVIEW_IOSTOCKWHS               ║
PROMPT ╚════════════════════════════════════════════════════════════╝

CREATE OR REPLACE FORCE VIEW TKVIEW_IOSTOCKWHS
    ("PRODUCT_ID", "TXN_STATUS", "QTY_D", "QTY_K", "TXN_DATE", "TXN_NO", "PRODUCT_NAME") AS
(
    SELECT b.product_id, 'RCV', SUM(qty), 0, rcv_date, a.rcv_no, product_name
    FROM TKTXN_RCVHDRS a, TKTXN_RCVDTLS b, TKMST_PRODUCTS c
    WHERE a.rcv_no = b.rcv_no
        AND rcv_status NOT IN ('A','F')
        AND b.product_id = c.product_id
    GROUP BY b.product_id, 'RCV', 0, rcv_date, a.rcv_no, product_name

    UNION ALL

    SELECT b.product_id, 'SLS', 0, SUM(qty), sls_date, a.sls_no, product_name
    FROM TKTXN_SLSDTLS b, TKTXN_SLSHDRS a, TKMST_PRODUCTS c
    WHERE a.sls_no = b.sls_no
        AND b.product_id = c.product_id
        AND sls_status NOT IN ('A','F')
    GROUP BY b.product_id, 'SLS', 0, sls_date, a.sls_no, product_name

    UNION ALL

    SELECT b.product_id, 'RJ', 0, SUM(qty), rj_date, a.rj_no, product_name
    FROM RSTXN_RJOBATS b, RSTXN_RJHDRS a, TKMST_PRODUCTS c
    WHERE a.rj_no = b.rj_no
        AND b.product_id = c.product_id
        AND rj_status NOT IN ('A','F')
    GROUP BY b.product_id, 'RJ', 0, rj_date, a.rj_no, product_name

    UNION ALL

    -- Opname (TKTXN_SOWHS): SO_D=masuk, SO_K=keluar — INSERT-only
    SELECT s.product_id, 'SO', NVL(s.so_d, 0), NVL(s.so_k, 0),
           s.so_date, s.so_no, c.product_name
    FROM TKTXN_SOWHS s, TKMST_PRODUCTS c
    WHERE s.product_id = c.product_id
);

COMMIT;

-- =============================================================================
-- STEP 3 — VERIFIKASI
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 3/4: Verifikasi                                      ║
PROMPT ╚════════════════════════════════════════════════════════════╝

-- 3a. Cek tabel
SELECT 'TABLE'           AS OBJECT_TYPE,
       TABLE_NAME        AS OBJECT_NAME,
       'CREATED'         AS STATUS
FROM USER_TABLES
WHERE TABLE_NAME = 'TKTXN_SOWHS';

-- 3b. Cek kolom
SELECT COLUMN_ID,
       COLUMN_NAME,
       DATA_TYPE
       || CASE
              WHEN DATA_TYPE IN ('VARCHAR2','CHAR') THEN '(' || DATA_LENGTH || ')'
              ELSE ''
          END                       AS DATA_TYPE,
       NULLABLE,
       DATA_DEFAULT
FROM USER_TAB_COLUMNS
WHERE TABLE_NAME = 'TKTXN_SOWHS'
ORDER BY COLUMN_ID;

-- 3c. Cek constraint
SELECT CONSTRAINT_NAME,
       CONSTRAINT_TYPE,    -- P=PK, R=FK, U=Unique, C=Check
       STATUS
FROM USER_CONSTRAINTS
WHERE TABLE_NAME = 'TKTXN_SOWHS'
ORDER BY CONSTRAINT_TYPE, CONSTRAINT_NAME;

-- 3d. Cek view valid
SELECT VIEW_NAME, STATUS
FROM USER_VIEWS u
JOIN USER_OBJECTS o ON o.OBJECT_NAME = u.VIEW_NAME AND o.OBJECT_TYPE = 'VIEW'
WHERE u.VIEW_NAME = 'TKVIEW_IOSTOCKWHS';

-- 3e. Sanity test: query view (should return 0 rows kalau belum ada opname,
--     tapi view-nya harus compile & jalan tanpa error)
SELECT COUNT(*) AS TOTAL_OPNAME_ROWS
FROM TKVIEW_IOSTOCKWHS
WHERE TXN_STATUS = 'SO';

-- =============================================================================
-- STEP 4 — DONE
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 4/4: Selesai                                         ║
PROMPT ║                                                            ║
PROMPT ║  ✓ TKTXN_SOWHS table created                               ║
PROMPT ║  ✓ FK_TKTXN_SOWHS_PRODUCT, FK_TKTXN_SOWHS_KASIR added      ║
PROMPT ║  ✓ IDX_TKTXN_SOWHS_PRODUCT_DATE indexed                    ║
PROMPT ║  ✓ TKVIEW_IOSTOCKWHS re-created (now includes SO block)    ║
PROMPT ║                                                            ║
PROMPT ║  Next: app /gudang/kartu-stock → simpan opname →           ║
PROMPT ║        row baru muncul di history dgn badge ungu (SO).     ║
PROMPT ╚════════════════════════════════════════════════════════════╝
