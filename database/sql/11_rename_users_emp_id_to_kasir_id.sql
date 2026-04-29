-- =========================================================================
-- ALTER USERS — RENAME emp_id → kasir_id (consistency dgn TKMST_KASIRS)
-- =========================================================================
-- Sebelumnya 10_alter_users_add_emp_id.sql menambahkan kolom EMP_ID.
-- Karena klinik pratama cuma kenal master TKMST_KASIRS (bukan IMMST_EMPLOYERS),
-- nama EMP_ID jadi misleading. Rename ke KASIR_ID supaya:
--   USERS.kasir_id  →  TKMST_KASIRS.kasir_id  (semantic match)
--
-- Aman dijalankan dua kali (idempotent):
--   - Kalau kolom emp_id masih ada, di-RENAME jadi kasir_id.
--   - Kalau kasir_id sudah ada (misal manual), skip.
--   - Kalau dua-duanya sudah ada (rename kelewat / partial), data emp_id
--     di-copy ke kasir_id (kalau kasir_id NULL), lalu emp_id di-drop.
--
-- Cara pakai:
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/11_rename_users_emp_id_to_kasir_id.sql
-- =========================================================================

SET SERVEROUTPUT ON

DECLARE
    has_emp   NUMBER := 0;
    has_kasir NUMBER := 0;
BEGIN
    SELECT COUNT(*) INTO has_emp   FROM user_tab_columns WHERE table_name='USERS' AND column_name='EMP_ID';
    SELECT COUNT(*) INTO has_kasir FROM user_tab_columns WHERE table_name='USERS' AND column_name='KASIR_ID';

    IF has_emp = 1 AND has_kasir = 0 THEN
        EXECUTE IMMEDIATE 'ALTER TABLE users RENAME COLUMN emp_id TO kasir_id';
        DBMS_OUTPUT.PUT_LINE('OK — kolom EMP_ID di-rename jadi KASIR_ID.');

    ELSIF has_emp = 1 AND has_kasir = 1 THEN
        -- Edge case: dua-duanya ada → copy data emp_id → kasir_id (kalau null), lalu drop emp_id
        EXECUTE IMMEDIATE 'UPDATE users SET kasir_id = emp_id WHERE kasir_id IS NULL AND emp_id IS NOT NULL';
        EXECUTE IMMEDIATE 'ALTER TABLE users DROP COLUMN emp_id';
        DBMS_OUTPUT.PUT_LINE('OK — data EMP_ID di-copy ke KASIR_ID, kolom EMP_ID di-drop.');

    ELSIF has_emp = 0 AND has_kasir = 0 THEN
        -- Belum ada sama sekali (mis. user lewat 10_alter), tambah kasir_id
        EXECUTE IMMEDIATE 'ALTER TABLE users ADD (kasir_id VARCHAR2(25))';
        DBMS_OUTPUT.PUT_LINE('OK — kolom KASIR_ID ditambahkan ke USERS.');

    ELSE
        DBMS_OUTPUT.PUT_LINE('SKIP — kolom KASIR_ID sudah ada, EMP_ID tidak ada.');
    END IF;

    -- Set comment
    EXECUTE IMMEDIATE q'[COMMENT ON COLUMN users.kasir_id IS 'FK ke TKMST_KASIRS.kasir_id (mapping user Laravel ke kasir). Nullable.']';

    COMMIT;
END;
/

EXIT
