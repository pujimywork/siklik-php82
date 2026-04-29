-- =========================================================================
-- Extract semua kolom dari semua tabel di schema SIKLIK
-- =========================================================================
-- Cara pakai:
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/_dev/extract_siklik_columns.sql
--
-- Output: database/sql/_dev/columns_siklik.txt (~1500-2500 baris)
-- =========================================================================

SET LINESIZE 200
SET PAGESIZE 50000
SET FEEDBACK OFF
SET HEADING ON
SET TRIMSPOOL ON
SET WRAP OFF

COL table_name      FORMAT A30
COL column_name     FORMAT A30
COL data_type       FORMAT A14
COL data_length     FORMAT 9999
COL nullable        FORMAT A3
COL data_default    FORMAT A20

SPOOL database/sql/_dev/columns_siklik.txt

PROMPT =========================================================================
PROMPT  ALL COLUMNS — schema SIKLIK
PROMPT  Kolom: table_name | column_name | data_type | data_length | nullable
PROMPT =========================================================================

SELECT
    table_name,
    column_name,
    data_type,
    data_length,
    nullable
FROM   all_tab_columns
WHERE  owner = 'SIKLIK'
ORDER  BY table_name, column_id;

SPOOL OFF
EXIT
