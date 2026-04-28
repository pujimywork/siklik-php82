--------------------------------------------------------------------------------
-- Mark migrations as already run di MIGRATIONS table Oracle siklik.
--
-- Tujuan: setelah ini dijalankan, `php artisan migrate` di Oracle siklik
-- akan SKIP semua migration di bawah karena Laravel pikir sudah pernah jalan.
--
-- Kenapa perlu: tabel-tabel Laravel system + spatie + master rsmst/tkmst
-- siklik SUDAH ADA di database Oracle siklik. Kalau migrate dijalankan
-- tanpa script ini, Laravel akan coba CREATE TABLE yang sudah ada → ORA-00955
-- (name is already used by an existing object) → migrate fail.
--
-- Jalanin sekali (idempotent — re-run safe karena ada check NOT EXISTS):
--   sqlplus siklik/siklik@//127.0.0.1:1521/orcl @database/sql/mark_migrations_as_run_oracle.sql
--
-- Migration yang DI-MARK (15-1 = 14 file):
--   v Laravel system: users, cache, jobs migrations (tabel sebagian ada,
--     sisanya akan dibikin manual via laravel_system_tables_oracle.sql)
--   v Spatie permission tables (sudah ada di siklik)
--   v Semua master kita: rsmst_educations, rsmst_jobs, rsmst_klaimtypes,
--     rsmst_entrytypes, rsmst_accdocs, rsmst_actemps, rsmst_actparamedics,
--     tkmst_categories, tkmst_uoms, tkmst_kasirs (semua tabel sudah ada
--     di Oracle siklik dari awal)
--
-- Migration yang TIDAK dimark (sengaja):
--   x rsmst_snomed_codes — tabel ini TIDAK ADA di siklik. Kalau mau pakai
--     fitur Snomed bawaan sirus, biarkan migration ini di-run normal nanti
--     oleh `php artisan migrate`. Atau hapus filenya kalau ngga butuh.
--
-- PRASYARAT:
--   - Tabel MIGRATIONS sudah ada di Oracle siklik (sudah ada per inspection).
--   - Pastikan dulu `laravel_system_tables_oracle.sql` jalan supaya
--     SESSIONS / CACHE / CACHE_LOCKS / JOBS / JOB_BATCHES tersedia.
--------------------------------------------------------------------------------

DECLARE
    v_max_id NUMBER := 0;
    v_count  NUMBER := 0;
    v_inserted NUMBER := 0;

    -- Daftar nama migration yang mau dimark sebagai sudah dijalankan.
    -- Format: nama file tanpa ekstensi .php (sama persis dengan kolom MIGRATION).
    TYPE name_array IS TABLE OF VARCHAR2(255);
    migs name_array := name_array(
        '0001_01_01_000000_create_users_table',
        '0001_01_01_000001_create_cache_table',
        '0001_01_01_000002_create_jobs_table',
        '2026_01_27_091529_create_permission_tables',
        '2026_04_28_133318_create_rsmst_educations_table',
        '2026_04_28_140001_create_rsmst_jobs_table',
        '2026_04_28_140002_create_rsmst_klaimtypes_table',
        '2026_04_28_140003_create_rsmst_entrytypes_table',
        '2026_04_28_140004_create_rsmst_accdocs_table',
        '2026_04_28_140005_create_rsmst_actemps_table',
        '2026_04_28_140006_create_rsmst_actparamedics_table',
        '2026_04_28_140007_create_tkmst_categories_table',
        '2026_04_28_140008_create_tkmst_uoms_table',
        '2026_04_28_140009_create_tkmst_kasirs_table'
    );
BEGIN
    -- Ambil max id saat ini supaya insert pakai id berikutnya
    SELECT NVL(MAX(id), 0) INTO v_max_id FROM migrations;

    DBMS_OUTPUT.PUT_LINE('Starting migration tracking. Current max id = ' || v_max_id);

    FOR i IN 1 .. migs.COUNT LOOP
        SELECT COUNT(*) INTO v_count FROM migrations WHERE migration = migs(i);

        IF v_count = 0 THEN
            v_max_id := v_max_id + 1;
            INSERT INTO migrations (id, migration, batch)
            VALUES (v_max_id, migs(i), 1);
            v_inserted := v_inserted + 1;
            DBMS_OUTPUT.PUT_LINE('  [INSERT] id=' || v_max_id || '  ' || migs(i));
        ELSE
            DBMS_OUTPUT.PUT_LINE('  [SKIP]   already exists: ' || migs(i));
        END IF;
    END LOOP;

    DBMS_OUTPUT.PUT_LINE('Done. Total inserted: ' || v_inserted || ' / ' || migs.COUNT);
    COMMIT;
END;
/

-- ============================================================
-- VERIFY: list semua migration yang tercatat (urutan ascending)
-- ============================================================
SELECT id, migration, batch
FROM migrations
ORDER BY id;
