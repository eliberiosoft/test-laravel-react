SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @user_model = 'App\\Models\\User';

SELECT fn_next_available_email('jpiguavel', 'mail.com') AS correo_ocupado_siguiente,
       fn_next_available_email('nuevousuario', 'mail.com') AS correo_libre;

START TRANSACTION;

PREPARE login_user FROM
'SELECT id, username, email, status, failed_login_attempts
   FROM users
  WHERE (username = ? OR email = ?)
    AND deleted_at IS NULL
    FOR UPDATE';
SET @login = 'Admin001';
EXECUTE login_user USING @login, @login;
DEALLOCATE PREPARE login_user;

COMMIT;

PREPARE user_menu FROM
'WITH user_permissions AS (
      SELECT rp.permission_id
        FROM model_has_roles mr
        JOIN role_has_permissions rp ON rp.role_id = mr.role_id
       WHERE mr.model_type = ? AND mr.model_id = ?
      UNION
      SELECT permission_id
        FROM model_has_permissions
       WHERE model_type = ? AND model_id = ?
  ),
  visible AS (
      SELECT *
        FROM menu_items
       WHERE is_active = 1
         AND (permission_id IS NULL OR permission_id IN (SELECT permission_id FROM user_permissions))
  )
  SELECT v.id, v.parent_id, v.`key`, v.label, v.route, v.icon, v.sort_order
    FROM visible v
   WHERE v.route IS NOT NULL
      OR EXISTS (SELECT 1 FROM visible c WHERE c.parent_id = v.id)
   ORDER BY v.sort_order, v.id';
SET @admin_id = (SELECT id FROM users WHERE username = 'Admin001');
SET @user_id = (SELECT id FROM users WHERE username = 'Jpiguave01');
EXECUTE user_menu USING @user_model, @admin_id, @user_model, @admin_id;
EXECUTE user_menu USING @user_model, @user_id, @user_model, @user_id;
DEALLOCATE PREPARE user_menu;

PREPARE list_users FROM
'SELECT u.id, u.username, u.email, u.status,
        p.first_name, p.middle_name, p.first_surname, p.second_surname, p.identification
   FROM users u
   JOIN persons p ON p.id = u.person_id AND p.deleted_at IS NULL
  WHERE u.deleted_at IS NULL
    AND u.status = ?
    AND EXISTS (
          SELECT 1
            FROM model_has_roles mr
            JOIN roles r ON r.id = mr.role_id
           WHERE mr.model_type = ?
             AND mr.model_id = u.id
             AND r.name = ?)
    AND (u.username LIKE ? OR u.email LIKE ? OR p.identification LIKE ?
         OR p.first_name LIKE ? OR p.first_surname LIKE ? OR p.second_surname LIKE ?)
  ORDER BY u.created_at DESC, u.id DESC
  LIMIT ? OFFSET ?';
SET @status = 'active', @role = 'user', @search = '%piguave%', @limit = 15, @offset = 0;
EXECUTE list_users USING @status, @user_model, @role, @search, @search, @search, @search, @search, @search, @limit, @offset;
DEALLOCATE PREPARE list_users;

SELECT status, COUNT(*) AS total
  FROM users
 WHERE deleted_at IS NULL
 GROUP BY status;

SELECT COALESCE(SUM(ended_at IS NULL AND token_id IS NOT NULL AND expires_at > NOW()), 0) AS sesiones_activas,
       COALESCE(SUM(started_at >= NOW() - INTERVAL 1 DAY), 0) AS sesiones_ultimas_24_horas
  FROM user_sessions;

SELECT COUNT(*) AS fallidos_ultimos_7_dias,
       COALESCE(SUM(attempted_at >= NOW() - INTERVAL 1 DAY), 0) AS fallidos_ultimas_24_horas,
       COALESCE(SUM(user_id IS NULL), 0) AS fallidos_cuentas_inexistentes
  FROM login_attempts
 WHERE result IN ('invalid_credentials', 'account_blocked', 'account_inactive', 'active_session')
   AND attempted_at >= NOW() - INTERVAL 7 DAY;

PREPARE previous_session FROM
'SELECT started_at, ended_at, end_reason, ip_address
   FROM user_sessions
  WHERE user_id = ?
  ORDER BY started_at DESC, id DESC
  LIMIT 1 OFFSET 1';
EXECUTE previous_session USING @admin_id;
DEALLOCATE PREPARE previous_session;

ANALYZE TABLE users, login_attempts;

EXPLAIN SELECT email
  FROM users
 WHERE email LIKE 'jpiguavel%@mail.com'
   AND email REGEXP '^jpiguavel[0-9]+@mail\\.com$';

EXPLAIN SELECT email
  FROM users IGNORE INDEX (users_email_unique)
 WHERE email LIKE 'jpiguavel%@mail.com'
   AND email REGEXP '^jpiguavel[0-9]+@mail\\.com$';

EXPLAIN SELECT COUNT(*)
  FROM login_attempts
 WHERE result IN ('invalid_credentials', 'account_blocked', 'account_inactive', 'active_session')
   AND attempted_at >= NOW() - INTERVAL 7 DAY;

EXPLAIN SELECT COUNT(*)
  FROM login_attempts IGNORE INDEX (login_attempts_result_attempted_at_index)
 WHERE result IN ('invalid_credentials', 'account_blocked', 'account_inactive', 'active_session')
   AND attempted_at >= NOW() - INTERVAL 7 DAY;
