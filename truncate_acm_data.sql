-- ============================================================
-- Action Matrix Data Cleanup Script
-- Truncates all observation data, keeps admin/settings data.
-- Database: PostgreSQL
-- Run: psql -U <user> -d <database> -f truncate_acm_data.sql
-- ============================================================

BEGIN;

-- Child tables first (foreign key order)
TRUNCATE TABLE acm_comments_file_attachment RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_comments                 RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_master_file_attachment   RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_sm_comments              RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_pksf_movements           RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_po_movements             RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_discussions              RESTART IDENTITY CASCADE;
TRUNCATE TABLE acm_tracker                  RESTART IDENTITY CASCADE;

-- Parent table last
TRUNCATE TABLE acm_master                   RESTART IDENTITY CASCADE;

COMMIT;

-- Verify: row counts should all be 0
SELECT 'acm_master'                   AS table_name, COUNT(*) AS rows FROM acm_master
UNION ALL
SELECT 'acm_tracker',                            COUNT(*) FROM acm_tracker
UNION ALL
SELECT 'acm_pksf_movements',                     COUNT(*) FROM acm_pksf_movements
UNION ALL
SELECT 'acm_po_movements',                       COUNT(*) FROM acm_po_movements
UNION ALL
SELECT 'acm_discussions',                        COUNT(*) FROM acm_discussions
UNION ALL
SELECT 'acm_comments',                           COUNT(*) FROM acm_comments
UNION ALL
SELECT 'acm_comments_file_attachment',           COUNT(*) FROM acm_comments_file_attachment
UNION ALL
SELECT 'acm_master_file_attachment',             COUNT(*) FROM acm_master_file_attachment
UNION ALL
SELECT 'acm_sm_comments',                        COUNT(*) FROM acm_sm_comments;
