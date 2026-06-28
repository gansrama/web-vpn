-- SQL Script to clean up duplicate employees
-- This script will:
-- 1. Identify employees with duplicate nomor_ktp
-- 2. Keep the oldest record (by created_at) for each duplicate
-- 3. Delete the newer duplicates
-- 4. Update any requests that reference deleted employees to point to the kept employee

-- Step 1: Find and display duplicates (for review)
SELECT nomor_ktp, COUNT(*) as count 
FROM employees 
GROUP BY nomor_ktp 
HAVING COUNT(*) > 1;

-- Step 2: Create a temporary table to track which employees to keep
CREATE TEMPORARY TABLE employees_to_keep AS
SELECT MIN(id) as keep_id, nomor_ktp
FROM employees
GROUP BY nomor_ktp
HAVING COUNT(*) > 1;

-- Step 3: Update teleworking_requests to point to the kept employee
UPDATE teleworking_requests tr
INNER JOIN employees e ON tr.employee_id = e.id
INNER JOIN employees_to_keep etk ON e.nomor_ktp = etk.nomor_ktp
SET tr.employee_id = etk.keep_id
WHERE e.id != etk.keep_id;

-- Step 4: Update akses_logic_requests to point to the kept employee
UPDATE akses_logic_requests alr
INNER JOIN employees e ON alr.employee_id = e.id
INNER JOIN employees_to_keep etk ON e.nomor_ktp = etk.nomor_ktp
SET alr.employee_id = etk.keep_id
WHERE e.id != etk.keep_id;

-- Step 5: Delete duplicate employees (keep only the oldest)
DELETE FROM employees
WHERE id NOT IN (SELECT keep_id FROM employees_to_keep)
AND nomor_ktp IN (SELECT nomor_ktp FROM employees_to_keep);

-- Step 6: Drop temporary table
DROP TEMPORARY TABLE employees_to_keep;

-- Step 7: Verify cleanup
SELECT nomor_ktp, COUNT(*) as count 
FROM employees 
GROUP BY nomor_ktp 
HAVING COUNT(*) > 1;
