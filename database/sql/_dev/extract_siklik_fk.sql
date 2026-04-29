-- =========================================================================
-- Extract Foreign Key relationships dari schema SIKLIK
-- =========================================================================
-- Cara pakai (di terminal, di folder project siklik-php82):
--
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/_dev/extract_siklik_fk.sql
--
-- Output akan di-save ke: database/sql/_dev/fk_siklik.txt
-- Ganti credential siklik/siklik kalau beda — cek .env (SIKLIK_DB_USERNAME / PASSWORD).
-- =========================================================================

-- Format output supaya rapi
SET LINESIZE 200
SET PAGESIZE 5000
SET FEEDBACK OFF
SET HEADING ON
SET TRIMSPOOL ON
SET WRAP OFF

COL table_name      FORMAT A30
COL column_name     FORMAT A25
COL constraint_name FORMAT A35
COL r_table         FORMAT A30
COL r_column        FORMAT A25

-- Mulai capture output
SPOOL database/sql/_dev/fk_siklik.txt

PROMPT =========================================================================
PROMPT  FOREIGN KEY RELATIONSHIPS — schema SIKLIK
PROMPT =========================================================================

SELECT
    a.table_name,
    acc.column_name,
    a.constraint_name,
    b.table_name      AS r_table,
    rcc.column_name   AS r_column
FROM   all_constraints  a
JOIN   all_cons_columns acc
       ON  a.owner            = acc.owner
       AND a.constraint_name  = acc.constraint_name
LEFT JOIN all_constraints  b
       ON  a.r_owner          = b.owner
       AND a.r_constraint_name = b.constraint_name
LEFT JOIN all_cons_columns rcc
       ON  b.owner            = rcc.owner
       AND b.constraint_name  = rcc.constraint_name
       AND acc.position       = rcc.position
WHERE  a.owner           = 'SIKLIK'
AND    a.constraint_type = 'R'
ORDER  BY a.table_name, acc.position;

PROMPT
PROMPT =========================================================================
PROMPT  PRIMARY KEYS — schema SIKLIK
PROMPT =========================================================================

SELECT
    a.table_name,
    acc.column_name,
    a.constraint_name
FROM   all_constraints  a
JOIN   all_cons_columns acc
       ON  a.owner            = acc.owner
       AND a.constraint_name  = acc.constraint_name
WHERE  a.owner           = 'SIKLIK'
AND    a.constraint_type = 'P'
ORDER  BY a.table_name, acc.position;

SPOOL OFF
EXIT
