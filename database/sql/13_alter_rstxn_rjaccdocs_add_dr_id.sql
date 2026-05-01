-- =============================================================================
-- File   : 13_alter_rstxn_rjaccdocs_add_dr_id.sql
-- Tujuan : Tambah kolom DR_ID ke RSTXN_RJACCDOCS supaya bisa simpan dokter
--          yang melakukan jasa medik per detail RJ.
--          Idempotent — aman dirun ulang.
--
-- Pakai oleh:
--   App siklik insert ke RSTXN_RJACCDOCS dgn kolom (RJHN_DTL, RJ_NO, DR_ID,
--   ACCDOC_ID, ACCDOC_PRICE) — sebelumnya error ORA-00904 karena DR_ID belum
--   ada di tabel.
--
-- Cara pakai:
--   sqlplus user/pass@db @database/sql/13_alter_rstxn_rjaccdocs_add_dr_id.sql
-- =============================================================================

SET SERVEROUTPUT ON SIZE UNLIMITED;
SET DEFINE OFF;
SET FEEDBACK ON;
SET ECHO OFF;

-- =============================================================================
-- STEP 0 — PRECONDITION CHECK
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 0/3: Precondition check                              ║
PROMPT ╚════════════════════════════════════════════════════════════╝

DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count FROM USER_TABLES WHERE TABLE_NAME = 'RSTXN_RJACCDOCS';
    IF v_count = 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'Tabel RSTXN_RJACCDOCS belum ada.');
    END IF;

    SELECT COUNT(*) INTO v_count FROM USER_TABLES WHERE TABLE_NAME = 'RSMST_DOCTORS';
    IF v_count = 0 THEN
        RAISE_APPLICATION_ERROR(-20002, 'Tabel RSMST_DOCTORS (FK target) belum ada.');
    END IF;

    DBMS_OUTPUT.PUT_LINE('  ✓ RSTXN_RJACCDOCS & RSMST_DOCTORS sudah ada.');
END;
/

-- =============================================================================
-- STEP 1 — ADD COLUMN DR_ID (kalau belum ada)
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 1/3: Add kolom DR_ID                                 ║
PROMPT ╚════════════════════════════════════════════════════════════╝

DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count
    FROM USER_TAB_COLUMNS
    WHERE TABLE_NAME = 'RSTXN_RJACCDOCS' AND COLUMN_NAME = 'DR_ID';

    IF v_count = 0 THEN
        EXECUTE IMMEDIATE 'ALTER TABLE RSTXN_RJACCDOCS ADD (DR_ID VARCHAR2(9))';
        DBMS_OUTPUT.PUT_LINE('  ✓ Kolom DR_ID ditambahkan (VARCHAR2(9)).');
    ELSE
        DBMS_OUTPUT.PUT_LINE('  ⚠ Kolom DR_ID sudah ada — skip.');
    END IF;
END;
/

-- =============================================================================
-- STEP 2 — ADD FOREIGN KEY ke RSMST_DOCTORS.DR_ID (kalau belum ada)
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 2/3: Add FK constraint                               ║
PROMPT ╚════════════════════════════════════════════════════════════╝

DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO v_count
    FROM USER_CONSTRAINTS
    WHERE TABLE_NAME      = 'RSTXN_RJACCDOCS'
      AND CONSTRAINT_NAME = 'FK_RSTXN_RJACCDOCS_DR';

    IF v_count = 0 THEN
        EXECUTE IMMEDIATE
            'ALTER TABLE RSTXN_RJACCDOCS '
         || 'ADD CONSTRAINT FK_RSTXN_RJACCDOCS_DR '
         || 'FOREIGN KEY (DR_ID) REFERENCES RSMST_DOCTORS(DR_ID)';
        DBMS_OUTPUT.PUT_LINE('  ✓ FK FK_RSTXN_RJACCDOCS_DR ditambahkan.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('  ⚠ FK FK_RSTXN_RJACCDOCS_DR sudah ada — skip.');
    END IF;
END;
/

COMMENT ON COLUMN RSTXN_RJACCDOCS.DR_ID IS
    'FK → RSMST_DOCTORS.DR_ID — dokter yg lakukan jasa medik di detail RJ';

COMMIT;

-- =============================================================================
-- STEP 3 — VERIFIKASI
-- =============================================================================
PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  STEP 3/3: Verifikasi                                      ║
PROMPT ╚════════════════════════════════════════════════════════════╝

SELECT COLUMN_ID, COLUMN_NAME, DATA_TYPE || '(' || DATA_LENGTH || ')' AS DATA_TYPE,
       NULLABLE
FROM USER_TAB_COLUMNS
WHERE TABLE_NAME = 'RSTXN_RJACCDOCS'
ORDER BY COLUMN_ID;

SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE, STATUS
FROM USER_CONSTRAINTS
WHERE TABLE_NAME = 'RSTXN_RJACCDOCS'
ORDER BY CONSTRAINT_TYPE, CONSTRAINT_NAME;

PROMPT
PROMPT ╔════════════════════════════════════════════════════════════╗
PROMPT ║  ✓ Selesai — RSTXN_RJACCDOCS sekarang punya kolom DR_ID    ║
PROMPT ║    + FK ke RSMST_DOCTORS                                   ║
PROMPT ╚════════════════════════════════════════════════════════════╝
