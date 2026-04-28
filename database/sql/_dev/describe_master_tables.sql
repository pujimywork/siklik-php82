--------------------------------------------------------------------------------
-- DESCRIBE master tables — structured introspection.
--
-- Tujuan: dump struktur semua tabel master (RSMST/LBMST/TKMST/DIMST/IMMST)
-- supaya bisa dibandingin dgn implementasi siklik-php82 (model, migration,
-- form Livewire, validation rules).
--
-- Output: list kolom per tabel — name, type, length/precision, nullable,
-- + primary key + total kolom.
--
-- Cara jalanin (sqlplus):
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl
--   SQL> SET PAGESIZE 50000
--   SQL> SET LINESIZE 220
--   SQL> SET TRIMSPOOL ON
--   SQL> SET FEEDBACK OFF
--   SQL> SPOOL master_desc.txt
--   SQL> @database/sql/_dev/describe_master_tables.sql
--   SQL> SPOOL OFF
--
-- Lalu paste isi `master_desc.txt` balik ke chat.
--------------------------------------------------------------------------------

SET PAGESIZE 50000
SET LINESIZE 220
SET TRIMSPOOL ON
SET FEEDBACK OFF
SET HEADING ON

COLUMN table_name      FORMAT A30
COLUMN column_id       FORMAT 999
COLUMN column_name     FORMAT A35
COLUMN column_type     FORMAT A22
COLUMN null_flag       FORMAT A8
COLUMN constraint_info FORMAT A20

-- ============================================================
-- 1. List semua master tables yang di-introspec (untuk overview)
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT MASTER TABLES OVERVIEW
PROMPT ============================================================
SELECT
    table_name,
    num_rows           AS approx_rows,
    last_analyzed
FROM user_tables
WHERE table_name LIKE '%MST_%'
ORDER BY table_name;


-- ============================================================
-- 2. Detail kolom per master table
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT COLUMN DETAILS (master tables only)
PROMPT ============================================================
SELECT
    c.table_name,
    c.column_id,
    c.column_name,
    CASE c.data_type
        WHEN 'NUMBER'    THEN 'NUMBER(' ||
                                NVL(TO_CHAR(c.data_precision), '*') ||
                                CASE WHEN c.data_scale IS NOT NULL AND c.data_scale > 0
                                     THEN ',' || c.data_scale ELSE '' END || ')'
        WHEN 'VARCHAR2'  THEN 'VARCHAR2(' || c.data_length || ')'
        WHEN 'CHAR'      THEN 'CHAR(' || c.data_length || ')'
        WHEN 'NVARCHAR2' THEN 'NVARCHAR2(' || c.char_length || ')'
        ELSE c.data_type
    END                       AS column_type,
    CASE WHEN c.nullable = 'N' THEN 'NOT NULL' ELSE 'NULL' END AS null_flag
FROM user_tab_columns c
WHERE c.table_name LIKE '%MST_%'
ORDER BY c.table_name, c.column_id;


-- ============================================================
-- 3. Primary keys untuk master tables
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT PRIMARY KEYS (master tables only)
PROMPT ============================================================
SELECT
    cons.table_name,
    cons.constraint_name,
    cols.column_name,
    cols.position
FROM user_constraints cons
JOIN user_cons_columns cols
  ON cons.owner = cols.owner
 AND cons.constraint_name = cols.constraint_name
WHERE cons.constraint_type = 'P'
  AND cons.table_name LIKE '%MST_%'
ORDER BY cons.table_name, cols.position;


-- ============================================================
-- 4. Foreign keys untuk master tables (relasi antar master)
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT FOREIGN KEYS (master tables only)
PROMPT ============================================================
SELECT
    cons.table_name        AS child_table,
    cols.column_name       AS child_column,
    rcons.table_name       AS parent_table,
    rcols.column_name      AS parent_column,
    cons.constraint_name
FROM user_constraints cons
JOIN user_cons_columns cols
  ON cons.constraint_name = cols.constraint_name
JOIN user_constraints rcons
  ON cons.r_constraint_name = rcons.constraint_name
JOIN user_cons_columns rcols
  ON rcons.constraint_name = rcols.constraint_name
 AND cols.position = rcols.position
WHERE cons.constraint_type = 'R'
  AND cons.table_name LIKE '%MST_%'
ORDER BY cons.table_name, cols.position;


-- ============================================================
-- 5. Index per master tables (selain PK — useful untuk LOV / search)
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT NON-PK INDEXES (master tables only)
PROMPT ============================================================
SELECT
    idx.table_name,
    idx.index_name,
    idx.uniqueness,
    cols.column_name,
    cols.column_position
FROM user_indexes idx
JOIN user_ind_columns cols
  ON idx.index_name = cols.index_name
WHERE idx.table_name LIKE '%MST_%'
  AND idx.index_name NOT IN (
        SELECT constraint_name
        FROM user_constraints
        WHERE constraint_type = 'P'
          AND table_name LIKE '%MST_%'
      )
ORDER BY idx.table_name, idx.index_name, cols.column_position;


-- ============================================================
-- 6. Comments (kalau ada — bisa kasih hint domain)
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT TABLE & COLUMN COMMENTS (master tables only)
PROMPT ============================================================
SELECT
    tc.table_name,
    NULL              AS column_name,
    tc.comments
FROM user_tab_comments tc
WHERE tc.table_name LIKE '%MST_%'
  AND tc.comments IS NOT NULL
UNION ALL
SELECT
    cc.table_name,
    cc.column_name,
    cc.comments
FROM user_col_comments cc
WHERE cc.table_name LIKE '%MST_%'
  AND cc.comments IS NOT NULL
ORDER BY 1, 2 NULLS FIRST;


-- ============================================================
-- 7. Summary count per prefix
-- ============================================================
PROMPT
PROMPT ============================================================
PROMPT SUMMARY (count per prefix)
PROMPT ============================================================
SELECT
    SUBSTR(table_name, 1, INSTR(table_name, '_') - 1) AS prefix,
    COUNT(*)                                          AS total_tables
FROM user_tables
WHERE table_name LIKE '%MST_%'
GROUP BY SUBSTR(table_name, 1, INSTR(table_name, '_') - 1)
ORDER BY prefix;
