# Base de datos

Motor elegido: **MySQL 8.4** (InnoDB, `utf8mb4_unicode_ci`, aislamiento `READ COMMITTED`).

## Modelo

El diseño de referencia de la prueba se adaptó apoyándose en los paquetes instalados:

| Diseño de la prueba | Implementación | Motivo |
|---|---|---|
| `Persona` | `persons` (nombres y apellidos separados, `identification` única, borrado lógico) | Generar el correo sin partir cadenas de texto. |
| `usuarios` | `users` (`person_id` único, `username` y `email` únicos, `status`, `failed_login_attempts`, `locked_at`, borrado lógico) | "Una persona, una cuenta" garantizado por índice único. |
| `usuarios.SessionActive` | Se deriva de `user_sessions` | Un indicador guardado aparte puede desincronizarse. |
| `Rol`, `rol_usuarios` | `roles`, `model_has_roles` (spatie/laravel-permission) | Paquete estándar, cache de permisos y Gate integrados. |
| `RolOpciones`, `rol_rolOpciones` | `menu_items` (árbol) vinculado a `permissions` | El permiso que muestra una opción es el mismo que protege el endpoint. |
| `Sessions` | `user_sessions` (inicio, vencimiento, cierre y motivo) + `personal_access_tokens` (Sanctum) | El token es la sesión activa; la tabla conserva el historial aunque el token se borre. |
| — | `login_attempts` | Auditoría de cada intento con su resultado, IP y fecha. |
| — | `activity_log` (spatie/laravel-activitylog) | Cambios de estado y acciones administrativas, sin contraseñas. |
| — | `password_reset_tokens` | Tokens de recuperación guardados como hash, de un solo uso y con vencimiento. |
| — | `user_imports`, `user_import_rows` | Estado de cada carga masiva (encolada, en proceso, completada, fallida) con sus contadores y el resultado por fila. |

## Restricciones

- Únicas: `persons.identification`, `users.person_id`, `users.username`, `users.email`, `menu_items.key`.
- Claves foráneas con `restrict` (persona con usuario), `cascade` (sesiones del usuario) y `set null` (token revocado, intentos de usuarios eliminados).
- `CHECK`:
  - `chk_users_status`: `status IN ('active', 'blocked', 'inactive')`.
  - `chk_persons_identification`: `identification REGEXP '^[0-9]{10}$'`.

Las reglas de negocio más complejas (cuatro dígitos repetidos, formato del usuario y de la contraseña) se validan en la aplicación (`app/Rules`).

## Scripts SQL

`docs/sql/` tiene el esquema, la función, los datos de prueba y las consultas principales en SQL plano, generados desde las migraciones y los seeders. El detalle y cómo cargarlos están en el README, en [Scripts SQL](../README.md#scripts-sql).

## Función almacenada

`fn_next_available_email(p_local_part, p_domain)` (migración `2026_11_27_190000_create_fn_next_available_email_function.php`):

1. Si `local@dominio` no existe, lo devuelve.
2. Si existe, busca los correos `local<dígitos>@dominio` y devuelve el sufijo más alto más uno (`jpiguavel`, `jpiguavel3` → `jpiguavel4`). `jpiguavela@` no cuenta.

### Unicidad con solicitudes concurrentes

La función por sí sola no evita la carrera: dos transacciones pueden obtener el mismo correo. La unicidad la garantiza el índice `users_email_unique`; si el `INSERT` choca, `CreateUserAction` reintenta con `retry()` y vuelve a consultar la función.

Con `REPEATABLE READ` (el aislamiento por defecto de MySQL) el reintento leería la misma foto de los datos y repetiría el correo. Por eso la conexión usa `READ COMMITTED`: cada lectura ve lo ya confirmado por otras transacciones. Lo verifica `ConcurrentUserCreationTest` con 5 procesos en paralelo.

## Índices justificados con `EXPLAIN`

Datos de desarrollo después de `ANALYZE TABLE`.

### 1. `users_email_unique` en la búsqueda de sufijos

```sql
EXPLAIN SELECT email FROM users
WHERE email LIKE 'jpiguavel%@mail.com'
  AND email REGEXP '^jpiguavel[0-9]+@mail\\.com$';
```

| type | key | rows | Extra |
|---|---|---|---|
| range | users_email_unique | 2 | Using where; Using index |

Forzando `IGNORE INDEX (users_email_unique)`:

| type | key | rows | Extra |
|---|---|---|---|
| ALL | NULL | 5 | Using where |

El `LIKE` con prefijo fijo (`'jpiguavel%'`) permite recorrer solo un rango del índice y resolver la consulta sin leer la tabla (`Using index`). Sin el índice, MySQL recorre toda la tabla, y el costo crece con la cantidad de usuarios en cada alta.

### 2. `login_attempts (result, attempted_at)` en el dashboard

```sql
EXPLAIN SELECT count(*) FROM login_attempts
WHERE result IN ('invalid_credentials', 'account_blocked', 'account_inactive', 'active_session')
  AND attempted_at >= NOW() - INTERVAL 7 DAY;
```

| type | key | rows | Extra |
|---|---|---|---|
| range | login_attempts_result_attempted_at_index | 22 | Using where; Using index |

Forzando `IGNORE INDEX`:

| type | key | rows | Extra |
|---|---|---|---|
| ALL | NULL | 38 | Using where |

El índice compuesto empieza por la columna de igualdad (`result IN (...)`) y sigue con la de rango (`attempted_at >=`). La consulta usa el índice como cobertura y no crece con el historial completo: `login_attempts` es la tabla que más crece.

## Transacciones, borrado lógico y consultas parametrizadas

- Cada caso de uso que escribe en varias tablas corre en una sola transacción (`DB::transaction`, con hasta 3 reintentos por deadlock). Los casos con concurrencia bloquean la fila del usuario con `SELECT … FOR UPDATE` (login, desbloqueo, actualización).
- `users` y `persons` usan borrado lógico. Los valores únicos de un registro borrado quedan reservados a propósito: "una persona, una cuenta", y la carga masiva no puede volver a crear a alguien eliminado.
- Todas las consultas pasan por Eloquent/Query Builder o por `DB::selectOne` con parámetros (`?`). No se concatena entrada del usuario en SQL. La búsqueda escapa `%` y `_` antes del `LIKE`.

## Migración a otro motor

| Tema | MySQL (actual) | PostgreSQL | SQL Server |
|---|---|---|---|
| Función de correo | `CREATE FUNCTION … READS SQL DATA`, `REGEXP`, `CONCAT` | `CREATE FUNCTION … LANGUAGE plpgsql STABLE`, operador `~`, `\|\|` | `CREATE FUNCTION … RETURNS NVARCHAR`, `LIKE` con `[0-9]` (sin regex nativo) o CLR |
| Error dentro de una transacción | Un `INSERT` duplicado deshace solo esa sentencia; el reintento sigue en la misma transacción | El primer error aborta la transacción: hay que envolver cada intento en un `SAVEPOINT` | Depende de `XACT_ABORT`; usar `SAVE TRANSACTION` |
| Aislamiento | `READ COMMITTED` configurado en la conexión | `READ COMMITTED` es el valor por defecto | `READ COMMITTED` (activar `READ_COMMITTED_SNAPSHOT` para evitar bloqueos de lectura) |
| Bloqueo de fila | `SELECT … FOR UPDATE` | Igual | `WITH (UPDLOCK, ROWLOCK)` (Laravel lo traduce con `lockForUpdate()`) |
| `CHECK` con expresión regular | `REGEXP` | `~ '^[0-9]{10}$'` | `NOT LIKE '%[^0-9]%' AND LEN(...) = 10` |
| Collation sin distinción de mayúsculas | `utf8mb4_unicode_ci` | `citext` o comparar con `lower()` + índice funcional | Collation `*_CI_AS` |
| Booleanos / textos | `tinyint(1)`, `varchar` | `boolean`, `varchar` / `text` | `bit`, `nvarchar` |
| Autoincremento | `bigint unsigned auto_increment` | `bigserial` / `identity` | `bigint identity` |
| Requisito de despliegue | `--log-bin-trust-function-creators=1` para crear la función con binlog activo | — | — |

Las migraciones de esquema de Laravel son portables. Lo específico de MySQL vive en dos migraciones con SQL nativo (la función y los `CHECK`), que habría que reescribir por motor.
