-- =========================================================================
-- ALTER USERS — tambah kolom EMP_ID
-- =========================================================================
-- USERS table siklik tdk punya kolom EMP_ID (yg punya cuma DIMST_USERS).
-- Kolom ini dibutuhkan untuk integrasi user Laravel ke entity employee
-- (mis. resolve kasir_id, dokter, perawat dari profil user).
--
-- Kolom dibuat NULLABLE — user existing yg belum di-mapping ke employee
-- tetap valid. Aplikasi handle null check di runtime (mis. saat post
-- transaksi minta kasir, kalau emp_id kosong → toast minta admin set).
--
-- Cara pakai:
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/10_alter_users_add_emp_id.sql
--
-- Aman di-jalankan dua kali — script cek dulu apakah kolom sudah ada.
-- =========================================================================

SET SERVEROUTPUT ON

DECLARE
    col_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO col_count
    FROM   user_tab_columns
    WHERE  table_name = 'USERS' AND column_name = 'EMP_ID';

    IF col_count = 0 THEN
        EXECUTE IMMEDIATE 'ALTER TABLE users ADD (emp_id VARCHAR2(25))';
        DBMS_OUTPUT.PUT_LINE('OK — kolom EMP_ID ditambahkan ke USERS.');
    ELSE
        DBMS_OUTPUT.PUT_LINE('SKIP — kolom EMP_ID sudah ada.');
    END IF;

    -- Sekalian set comment supaya nyambung dgn DIMST_USERS.EMP_ID
    EXECUTE IMMEDIATE q'[COMMENT ON COLUMN users.emp_id IS 'FK ke entity employee (DIMST_USERS.emp_id atau TKMST_KASIRS.kasir_id), nullable.']';
END;
/

EXIT
