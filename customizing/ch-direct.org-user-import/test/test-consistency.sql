/*!40101 SET NAMES utf8 */;
-- Old tables:
SELECT `id`, `username`, `email`, `password`, `is_admin`
FROM `contrexx_access_users`
WHERE `username` IN (
	SELECT DISTINCT `username`
    FROM `contrexx_access_users`
    HAVING COUNT(*)>1
)
-- username is empty for all!
SELECT `id`, `username`, `email`, `password`, `is_admin`
FROM `contrexx_access_users`
WHERE `email` IN (
	SELECT DISTINCT `email`
    FROM `contrexx_access_users`
    HAVING COUNT(*)>1
)
-- id 1, system@comvation.com -- why??

-- New, migrated tables:
SELECT `id`, `username`, `email`, `password`, `is_admin`
FROM `contrexx_access_users`
WHERE `username` IN (
	SELECT DISTINCT `username`
    FROM `contrexx_access_users`
    HAVING COUNT(*)>1
)
-- id 1, info@example.org (empty username)
SELECT `id`, `username`, `email`, `password`, `is_admin`
FROM `contrexx_access_users`
WHERE `email` IN (
	SELECT DISTINCT `email`
    FROM `contrexx_access_users`
    HAVING COUNT(*)>1
)
-- id 1, info@example.org (empty username)
