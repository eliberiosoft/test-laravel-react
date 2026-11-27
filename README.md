# Prueba Senior Laravel & React

API REST en Laravel 13 y aplicación web en React + TypeScript para autenticación y administración de usuarios. Todo corre en Docker.

## Contenido

1. [Inicio rápido](#inicio-rápido)
2. [Stack](#stack)
3. [Estructura del repositorio](#estructura-del-repositorio)
4. [Ambiente: levantar, bajar y poblar](#ambiente-levantar-bajar-y-poblar)
5. [Documentación de la API](#documentación-de-la-api)
6. [Comandos frecuentes y pruebas](#comandos-frecuentes-y-pruebas)
7. [Problemas frecuentes](#problemas-frecuentes)
8. [Backend](#backend)
9. [Frontend](#frontend)
10. [Operación en Linux](#operación-en-linux)
11. [Riesgos y limitaciones](#riesgos-y-limitaciones)

## Inicio rápido

**Requisitos:** Docker con Compose v2, `bash`, `curl` y `openssl`. No hace falta tener PHP, Composer ni Node instalados.

```bash
./up.sh --seed       # construye, levanta y carga los datos base
./seed.sh --sample   # opcional: 50 usuarios de ejemplo más
```

Abre http://localhost:5173 e inicia sesión con `Admin001` / `Admin!2026`. Las demás URLs están en [Servicios](#servicios).

## Stack

| Pieza | Versión / herramienta |
|---|---|
| PHP / servidor | PHP 8.5 sobre FrankenPHP (Caddy) |
| Framework | Laravel 13 |
| Base de datos | MySQL 8.4 (`READ COMMITTED`) |
| Cache, sesiones, colas | Redis 7 |
| Correo (desarrollo) | Mailpit |
| Autenticación | Laravel Sanctum (tokens Bearer) |
| Roles y permisos | spatie/laravel-permission |
| Auditoría | spatie/laravel-activitylog |
| Filtros, orden y paginación | spatie/laravel-query-builder |
| Carga masiva | maatwebsite/excel |
| Documentación de la API | dedoc/scramble (OpenAPI 3.1) |
| Frontend | React 19 + TypeScript 7, Vite 8, React Router 8 |
| Datos en el frontend | TanStack Query 5 |
| Estilos | Tailwind CSS 4 (lenguaje visual de Tailwind Plus Application UI), Heroicons, Inter |

## Estructura del repositorio

```
.
├── apps/backend/          Proyecto Laravel (sin .env)
├── apps/frontend/         Aplicación React + TypeScript (Vite)
├── envs/                  Variables de entorno, fuera de los proyectos
│   ├── .env.example       Plantilla sin secretos
│   ├── .env.dev           Entorno local (no se versiona)
│   └── ports.env          Puertos expuestos por Docker
├── infrastructure/        docker-compose, Dockerfile y scripts de MySQL
├── docs/                  database.md, sql/, openapi.json, import-sample.csv, PRUEBA.pdf
├── PRODUCT.md             Contexto de producto: usuarios, principios y alcance
├── DESIGN.md              Sistema de diseño del frontend
├── up.sh                  Levanta el ambiente
├── down.sh                Baja el ambiente
└── seed.sh                Pobla la base de datos
```

Laravel no lee ningún `.env` dentro del proyecto: `envs/.env.dev` se carga en los contenedores con `env_file`.

## Ambiente: levantar, bajar y poblar

### `./up.sh`

1. Verifica Docker y Compose.
2. Lee los puertos de `envs/ports.env` y enlaza `infrastructure/.env` a ese archivo.
3. Si no existe `envs/.env.dev`, lo crea desde `envs/.env.example` y genera el `APP_KEY`.
4. Si existe `apps/backend/.env`, avisa (tendría prioridad sobre `envs/.env.dev`) y ofrece borrarlo.
5. Construye la imagen del backend y levanta los contenedores. Las migraciones corren solas al arrancar el backend.
6. Espera a que respondan el backend (`/up`) y el frontend, y muestra las URLs.

| Opción | Para qué |
|---|---|
| *(sin opciones)* | Levanta o actualiza el ambiente. Conserva los datos |
| `--seed` | Al terminar, carga roles, permisos, menú y usuarios demo |
| `--no-build` | No reconstruye la imagen del backend. Más rápido si no cambiaron el Dockerfile ni las dependencias |
| `--fresh` | Antes de levantar, borra contenedores y volúmenes (pide confirmación; se pierden los datos de MySQL, Redis y Mailpit) |

### `./down.sh`

| Opción | Para qué |
|---|---|
| *(sin opciones)* | Detiene y elimina los contenedores. Los datos se conservan |
| `--volumes` | Borra también los volúmenes: MySQL, Redis, Mailpit, `vendor` y `node_modules` (pide confirmación) |
| `--images` | Borra también la imagen del backend |
| `--all` | Equivale a `--volumes --images` |
| `-y`, `--yes` | No pide confirmación |

### `./seed.sh`

Requiere el backend corriendo.

| Opción | Para qué |
|---|---|
| *(sin opciones)* | Roles, permisos, menú y usuarios demo. Se puede repetir: no duplica nada y deja a los usuarios demo activos y desbloqueados |
| `--sample [N]` | Además crea N usuarios de ejemplo (50 por defecto) con `php artisan users:sample` |
| `--fresh` | Borra todas las tablas (`migrate:fresh`), corre las migraciones y vuelve a poblar (pide confirmación) |
| `-y`, `--yes` | No pide confirmación |

Los usuarios de ejemplo usan la misma Action que el alta manual, así que el correo sale de la regla de negocio y el alta queda auditada.
- Se llaman `Muestra0001`, `Muestra0002`… y su contraseña es `Muestra!2026`.
- De cada 10, uno queda bloqueado y otro inactivo, para probar los filtros, el desbloqueo y el dashboard.
- El comando se niega a correr fuera de `local` y `testing`.

Para la carga masiva hay un archivo de ejemplo en `docs/import-sample.csv`, y la pantalla **Carga masiva** descarga una plantilla.

### Ciclo de trabajo

```bash
./up.sh --seed                    # la primera vez
./down.sh                         # al terminar el día: los datos quedan
./up.sh --no-build                # al volver
./seed.sh --fresh --sample -y     # empezar de nuevo con datos limpios
./down.sh --all -y                # quitar todo, incluida la imagen
```

### Servicios

| Servicio | URL / puerto | Puerto en `envs/ports.env` |
|---|---|---|
| Aplicación web | http://localhost:5173 | `FRONTEND_PORT` |
| API | http://localhost:8000 | `BACKEND_PORT` |
| Documentación de la API | http://localhost:8000/docs/api | `BACKEND_PORT` |
| Mailpit (correos de desarrollo) | http://localhost:8025 · SMTP 1025 | `MAILPIT_UI_PORT`, `MAILPIT_SMTP_PORT` |
| MySQL | localhost:3306 | `MYSQL_PORT` |
| Redis | localhost:6379 | `REDIS_PORT` |

Además corren `queue` (cola `default`: correos), `queue-imports` (cola `imports`: carga masiva) y `scheduler` (`schedule:work`).

Los puertos se propagan solos: el frontend recibe `VITE_API_URL` a partir de `BACKEND_PORT`, y el backend recibe `FRONTEND_URL` a partir de `FRONTEND_PORT` (se usa en CORS y en el enlace de recuperación). Si cambias un puerto en `envs/ports.env`, basta con volver a correr `./up.sh`.

### Credenciales de prueba

Solo existen en los entornos `local` y `testing`.

| Rol | Usuario | Correo | Contraseña |
|---|---|---|---|
| Administrador | `Admin001` | atorresv@mail.com | `Admin!2026` |
| Usuario | `Jpiguave01` | jpiguavel@mail.com | `User!2026` |
| Usuario de ejemplo | `Muestra0001`… | generado | `Muestra!2026` |

## Documentación de la API

Scramble la genera a partir de las rutas, los FormRequest y los Resources, sin anotaciones en el código.

| Formato | Dónde | Para qué |
|---|---|---|
| Navegable | http://localhost:8000/docs/api | Ver cada endpoint con sus parámetros, validaciones y respuestas, y probarlo (*Send API Request*) |
| OpenAPI 3.1 | http://localhost:8000/docs/api.json | Importar en Postman, Insomnia o un generador de clientes |
| Copia versionada | `docs/openapi.json` | Revisarla sin levantar el ambiente |

- Las rutas protegidas declaran `Authorization: Bearer <token>`. Las públicas (login y recuperación de contraseña) figuran sin candado.
- `GET /` devuelve los enlaces a la documentación cuando está publicada.
- **Publicación:** en `local` siempre está visible. En otros entornos solo se publica con `API_DOCS_PUBLIC=true` (gate `viewApiDocs` en `AppServiceProvider`); si no, responde 403.

Para probar un endpoint protegido:
1. Ejecuta `POST /auth/login` desde la documentación.
2. Copia el `token` de la respuesta.
3. En el endpoint que quieras probar, pégalo en la sección *Auth*, tipo Bearer.

Para regenerar la copia versionada:

```bash
cd infrastructure
docker compose exec backend php artisan scramble:export
mv ../apps/backend/storage/app/openapi.json ../docs/openapi.json
```

## Comandos frecuentes y pruebas

Desde `infrastructure/`:

```bash
docker compose exec backend php vendor/bin/phpunit
docker compose exec backend ./vendor/bin/pint
docker compose exec backend php artisan migrate
docker compose exec backend php artisan users:sample 20
docker compose exec backend php artisan route:list --except-vendor
docker compose exec frontend npm run typecheck
docker compose exec frontend npm run lint
docker compose exec frontend npm run build
docker compose logs -f backend frontend queue queue-imports scheduler
```

El contenedor `frontend` instala las dependencias (`npm ci`) y levanta Vite al arrancar, así que no hace falta correr `npm install` ni `npm run dev` en tu máquina.

**Pruebas del backend (80 tests):**
- Corren contra la base MySQL `laravel_testing`, creada por `infrastructure/mysql/init`. `tests/TestCase.php` aborta si la base no termina en `_testing`.
- Cubren login, bloqueo, enumeración de cuentas, sesión única, expiración y revocación de sesiones, recuperación de contraseña, permisos, CRUD, duplicidad, carga masiva, menú, dashboard, bienvenida y la publicación de la documentación.
- `ConcurrentLoginTest` y `ConcurrentUserCreationTest` lanzan procesos reales en paralelo (`pcntl_fork`) contra MySQL. Prueban la sesión única, el conteo de fallos y la unicidad del correo.
- `php artisan test` da el mismo resultado, pero marca cada test con una advertencia. Laravel intenta leer `apps/backend/.env`, que no existe a propósito, y Collision muestra esa lectura aunque está silenciada. `phpunit` da la salida limpia.

## Problemas frecuentes

| Síntoma | Solución |
|---|---|
| Un puerto ya está en uso | Cámbialo en `envs/ports.env` y vuelve a correr `./up.sh` |
| El login responde "Ya existe una sesión activa" | Cierra sesión en la otra pestaña, o espera a que venza (120 minutos). Si es un usuario común, un administrador puede cerrarla desde **Usuarios → Cerrar sesiones** |
| Un usuario demo quedó bloqueado tras 3 intentos | `./seed.sh` lo deja activo y desbloqueado |
| El frontend queda en blanco después de renombrar o borrar archivos | El Vite del contenedor guardó la ruta vieja: `cd infrastructure && docker compose restart frontend` |
| No llega el correo de recuperación o de bloqueo | Revisa Mailpit (http://localhost:8025) y que el contenedor `queue` esté corriendo |
| El backend no arranca | `cd infrastructure && docker compose logs backend` |

## Backend

### Endpoints

Todas las respuestas son JSON. Los recursos van dentro de `data`. Los errores tienen la forma `{ "message": "...", "code": "..." }`, o en validación `{ "message": "...", "errors": { campo: [...] } }`.

| Método | Ruta | Acceso |
|---|---|---|
| GET | `/` | Público: nombre, versión y enlaces a la documentación |
| GET | `/docs/api`, `/docs/api.json` | Documentación (en `local`, o con `API_DOCS_PUBLIC=true`) |
| POST | `/api/auth/login` | Público (`login` = usuario o correo, `password`) |
| POST | `/api/auth/forgot-password` | Público |
| POST | `/api/auth/reset-password` | Público (token del correo) |
| POST | `/api/auth/logout` | Autenticado |
| GET | `/api/auth/me` | Autenticado |
| GET | `/api/menu` | Autenticado (según permisos del rol) |
| GET | `/api/welcome` | `welcome.view` |
| GET | `/api/dashboard` | `dashboard.view` (admin) |
| PATCH | `/api/profile` | `profile.update` (datos propios) |
| GET | `/api/users` | `users.view`: `filter[status]`, `filter[role]`, `filter[search]` (mín. 3 caracteres), `sort`, `per_page` |
| POST | `/api/users` | `users.create` |
| GET | `/api/users/{id}` | `users.view` o el propio usuario |
| PATCH | `/api/users/{id}` | `users.update` (no sobre administradores) |
| DELETE | `/api/users/{id}` | `users.delete` (borrado lógico, no sobre administradores) |
| POST | `/api/users/{id}/unlock` | `users.unlock` |
| POST | `/api/users/{id}/sessions/revoke` | `sessions.revoke` (no sobre administradores) |
| POST | `/api/users/imports` | `users.import`: sube CSV o XLSX y responde 202 |
| GET | `/api/users/imports/{id}` | `users.import` y ser quien subió el archivo: estado y resumen |
| GET | `/api/users/imports/{id}/rows` | `users.import` y ser quien subió el archivo: resultado por fila (`filter[status]`, paginado) |

Códigos de error propios: `invalid_credentials`, `active_session`, `account_blocked`, `account_inactive`, `session_expired`, `unauthenticated`, `forbidden`, `not_found`, `too_many_requests`, `duplicate_resource`, `user_not_blocked`, `user_blocked`, `invalid_reset_token`. El login responde siempre `invalid_credentials`; `account_blocked` y `account_inactive` solo aparecen en peticiones autenticadas.

### Arquitectura

```
HTTP        FormRequest (validación + autorización) → Controller (una línea) → Resource
Escritura   Actions (un caso de uso, define la transacción)
Lectura     Queries (spatie/query-builder, métricas, bienvenida)
Dominio     Services (sesiones, bloqueo, intentos, correo, menú) · Enums · DTOs (app/Data)
Datos       Eloquent + migraciones + función MySQL
```

- **Controllers sin lógica.** Reciben un FormRequest, llaman una Action o una Query y devuelven un Resource.
- **FormRequest.** Validan, autorizan con la Policy y entregan un DTO (`toData()`) con los datos ya tipados.
- **Actions.** Cada una es un caso de uso que escribe (`CreateUserAction`, `LoginAction`, `ImportUsersAction`…). Es la única capa que abre transacciones.
- **Services.** Lógica reutilizable por varias Actions: sesiones, bloqueo, intentos y generación del correo.
- **Queries.** Lecturas con filtros, orden y paginación (`UserIndexQuery` extiende `QueryBuilder`), métricas del dashboard y resumen de bienvenida.
- **Autorización en el servidor.** `UserPolicy` combina los permisos del rol con las reglas del propietario del recurso. Un admin no modifica a otros admins ni a sí mismo, y un usuario solo edita sus datos permitidos.
- **Errores consistentes.** Las excepciones de dominio extienden `ApiException`. Las del framework (401, 403, 404, 409, 429) se traducen al mismo formato en `bootstrap/app.php`.
- **Constantes** de negocio en `app/helpers.php` (cargado por Composer). Los valores ajustables viven en `config/security.php` y `config/accounts.php`.

Se eligió MVC con capas (Actions/Services/Queries) en lugar de DDD completo: el dominio es un solo agregado (persona + usuario) y DDD agregaría ceremonia sin beneficio en este alcance.

### Seguridad

- **Sesión única, incluso con logins simultáneos.** El login bloquea la fila del usuario (`SELECT … FOR UPDATE`). Si ya existe una sesión activa, responde `409 active_session`.
- **Bloqueo.** Al tercer fallo consecutivo la cuenta pasa a `blocked`, se avisa al usuario por correo y solo un administrador la desbloquea. El contador se actualiza bajo el mismo bloqueo de fila, así que los fallos simultáneos no se pierden.
- **Sin enumeración de cuentas.**
  - El login responde igual (`401 invalid_credentials`) para un usuario inexistente, una contraseña incorrecta o una cuenta bloqueada o inactiva, tenga o no la contraseña correcta. El motivo real queda en `login_attempts`.
  - Se compara siempre contra un hash: si el usuario no existe, contra uno ficticio guardado en cache. Todo login fallido tarda al menos `LOGIN_MIN_FAILURE_RESPONSE_MS` (`Timebox`).
  - `forgot-password` responde siempre el mismo mensaje.
- **Recuperación de contraseña.**
  - Token aleatorio guardado como hash, de un solo uso y válido 60 minutos.
  - El enlace solo se envía a cuentas activas, aunque la respuesta es la misma para todas.
  - El correo encolado va cifrado, así que el token no queda en texto plano en Redis ni en `failed_jobs`.
  - Al restablecer, se revocan las sesiones abiertas y los intentos fallidos vuelven a cero. El enlace apunta a `FRONTEND_URL/reset-password`.
- **Expiración y revocación.** Los tokens vencen a los `SANCTUM_TOKEN_EXPIRATION` minutos y el API responde `401 session_expired`. Se revocan al cerrar sesión, cuando un administrador las revoca, al desactivar, borrar o bloquear al usuario, y al restablecer la contraseña.
- **Límite de intentos.** El login permite 5 intentos por minuto por identificador y 20 por IP. Pedir el enlace y restablecer tienen límites separados, cada uno de 3 cada 10 minutos por correo y 10 por IP. Las claves se guardan hasheadas.
- **Auditoría.**
  - `login_attempts` registra cada intento con su resultado.
  - `activity_log` registra los cambios de usuario y persona, el desbloqueo, la revocación y la importación, con el administrador como autor.
  - Ninguna tabla guarda contraseñas ni tokens en texto plano: los tokens de sesión y de recuperación se guardan como hash.
- **Contraseñas.** Solo se guarda el hash bcrypt, y se rehashea si cambia el costo configurado.

### Carga masiva (asíncrona, por cola)

Columnas: `first_name, middle_name, first_surname, second_surname, identification, birth_date, username`.

Flujo:
1. `POST /api/users/imports` (CSV o XLSX, hasta 10 MB):
   - guarda el archivo en `storage/app/private/imports`;
   - crea el registro `user_imports` en estado `pending` y encola el job;
   - responde **202** con la cabecera `Location`.
2. El worker `queue-imports` procesa el archivo **por bloques de 500 filas** (`WithChunkReading`), así que un archivo grande no se carga entero en memoria. Los contadores (`total`, `created`, `skipped`, `failed`) se actualizan fila por fila.
3. El front consulta `GET /api/users/imports/{id}` (estado y resumen) y `GET /api/users/imports/{id}/rows?filter[status]=failed` (filas paginadas con sus errores).
4. Al terminar, el archivo se borra. Quedan su nombre y su SHA-256 en el registro y en `activity_log`.

Estrategia de persistencia:
- **Una transacción por fila**, con la misma Action que el alta manual. El correo se genera igual, y una fila inválida no impide guardar las demás.
- **Idempotencia por clave natural.** Una fila cuya identificación ya existe (incluso con borrado lógico) queda `skipped`, así que reprocesar el archivo no duplica nada. Las identificaciones o usuarios repetidos dentro del mismo archivo quedan `failed`.
- **Errores por fila** en `user_import_rows`: línea exacta del archivo, estado, correo generado y errores por campo, con las mismas reglas que el alta.
- **Normalización de Excel.** Las identificaciones numéricas recuperan el cero inicial y las fechas en formato serial de Excel se convierten.
- **Sin contraseñas en el archivo.** Los usuarios importados reciben una contraseña aleatoria que nadie conoce y definen la suya con "olvidé mi contraseña".
- **Cola propia (`imports`)** con su propio worker, timeout de 15 minutos y un solo intento. Una importación larga no retrasa los correos de la cola `default`. Si el job falla, la importación queda `failed` y el archivo se borra.
- **Autoría y privacidad.** Los usuarios creados por el job quedan auditados con el administrador que subió el archivo como autor (`CauserResolver::withCauser`). Cada importación solo la puede consultar quien la subió, porque contiene identificaciones y correos.

### Base de datos

Ver [docs/database.md](docs/database.md): modelo, restricciones, función almacenada, índices justificados con `EXPLAIN` y migración a PostgreSQL o SQL Server.

### Scripts SQL

Las migraciones y los seeders son la fuente de verdad. Los scripts de `docs/sql/` se generaron a partir de ellos para quien quiera ver o montar la base sin Laravel:

| Script | Contenido |
|---|---|
| `01_schema.sql` | Las 22 tablas con claves foráneas, índices y `CHECK`, más el registro de `migrations` (así Laravel reconoce la base como migrada) |
| `02_functions.sql` | La función `fn_next_available_email` |
| `03_seed.sql` | Roles, permisos, menú por rol y los dos usuarios demo |
| `04_queries.sql` | Consultas parametrizadas de la app (login con bloqueo de fila, menú por rol, listado con filtros y paginación, dashboard, bienvenida) y los `EXPLAIN` que justifican los índices |

Para cargarlos en una base nueva:

```bash
cd infrastructure
docker compose exec -T mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE gestion_sql CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"'
for f in ../docs/sql/0[1-3]_*.sql; do
  docker compose exec -T mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" gestion_sql' < "$f"
done
docker compose exec -T mysql sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" --table gestion_sql' < ../docs/sql/04_queries.sql
```

Se verificaron cargándolos en una base vacía: la estructura resultante (columnas, índices, claves foráneas, `CHECK` y función) es idéntica a la que crean las migraciones. Crear la función requiere `--log-bin-trust-function-creators=1`, que el contenedor de MySQL ya trae.

## Frontend

```
apps/frontend/src/
├── app/        App.tsx (providers) · routes.tsx (rutas y permisos)
├── core/       http.ts      cliente HTTP, token, errores y log de peticiones
│               auth.tsx     todo el login: llamadas, sesión, useAuth y guards
│               types.ts     contrato de la API
├── services/   account · dashboard · users · imports  (un archivo por recurso)
├── shared/     ui/ (Button, Form, Feedback, Layout, Dialog, DataTable) · hooks · validation · domain · utils
├── layout/     AppLayout (sidebar y menú móvil) · AuthLayout
└── features/   auth · welcome · dashboard · users · imports · profile · errors
```

### Reglas de organización

- **Las dependencias van hacia abajo.** `features` y `layout` usan `services`, y `services` usa `core`. `core` y `shared` son la base: nunca importan de `services` ni de `features`.
- **Un archivo agrupa lo que cambia junto.** Por ejemplo, `UserDialogs.tsx` tiene los diálogos de alta, edición y confirmación, y `PasswordRecovery.tsx` las dos pantallas de recuperación.
- **La lógica sin JSX va en `*.logic.ts`.** Por ejemplo, `users.logic.ts`: filtros en la URL, permisos por fila y esquema del formulario de persona.
- **Imports con alias `@/`** (apunta a `src/`), así que la ruta de cada import dice qué capa se está usando.

### Equivalencia con Angular

| Angular | Aquí |
|---|---|
| `users.service.ts` con `HttpClient` | `services/users.service.ts`: el objeto `usersService` hace el HTTP y, debajo, los hooks `useUserList` y `useUserMutations` lo conectan con la caché |
| `users.component.ts` (estado y llamadas) | La parte de arriba de `UsersPage.tsx`: hooks y handlers |
| `users.component.html` | El `return (...)` del mismo componente |
| `users.component.css` | Clases de Tailwind en el JSX; las recetas compartidas están en `shared/ui/styles.ts` |
| `HttpInterceptor` | `core/http.ts`: agrega el token, traduce errores, maneja el 401 y registra cada petición |
| `AuthService` + `AuthGuard` | `core/auth.tsx`: login, logout, recuperación de contraseña, sesión, `useAuth()` y los guards `RequireAuth`, `GuestOnly`, `RequirePermission` |
| `CoreModule` / `SharedModule` | `core/` y `shared/` |

### Cómo seguir una llamada

1. En el componente: `const users = useUserList(query)`. El import dice de dónde sale: `@/services/users.service`.
2. En el servicio, el hook llama a `usersService.list(query)`, que hace `http.get('/users', query)`.
3. En desarrollo, cada petición aparece en la consola del navegador como `[API] GET /users?page=1 200 · 38 ms`. Al desplegarla muestra el payload y la respuesta, con contraseñas y tokens ocultos.

### Decisiones

- **El menú viene del backend** (`GET /api/menu`) y cada ruta además exige su permiso. Si alguien escribe la URL a mano, ve "No tienes acceso".
- **Sesión.** El token y su vencimiento se guardan en `localStorage`. Un 401 del API (`session_expired` o token revocado) cierra la sesión y lleva al login con el motivo. También se cierra al llegar a `expires_at`, sin esperar una petición.
- **Formularios.** `useForm` valida en el cliente con las mismas reglas que el backend (`shared/validation.ts`) y vuelca los errores 422 del servidor en cada campo.
- **Usuarios.**
  - Los filtros, el orden y la página viven en la URL, así que se pueden compartir y sobreviven al refrescar.
  - La búsqueda espera 300 ms y solo consulta con 3 caracteres o más.
  - Las acciones de cada fila se muestran según el permiso, nunca sobre un administrador ni sobre uno mismo, y el backend lo vuelve a validar.
- **Carga masiva.**
  - Valida la extensión y el tamaño antes de subir, y ofrece una plantilla CSV.
  - Consulta el estado cada 1,5 s mientras está `pending` o `processing`. Al terminar, muestra el resultado por fila, filtrable.
  - El id queda en la URL (`?import=ID`), así que se puede salir y volver.
- **Diseño.** Sigue el lenguaje de Tailwind Plus Application UI con utilidades propias: sidebar blanco, un solo acento indigo, tablas en tarjeta y diálogos nativos (`<dialog>`) accesibles con teclado. Se probó en 1920, 1024, 760 y 390 px. El sistema completo está en [DESIGN.md](DESIGN.md).

## Operación en Linux

```bash
# Permisos: el contenedor corre como root; si los archivos generados quedan con dueño root en el host:
sudo chown -R "$USER":"$USER" apps/backend/storage apps/backend/bootstrap/cache
chmod -R ug+rwX apps/backend/storage apps/backend/bootstrap/cache

# Migraciones
cd infrastructure
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan migrate:status

# Logs de la aplicación (canal single)
tail -f ../apps/backend/storage/logs/laravel.log
grep -n "ERROR" ../apps/backend/storage/logs/laravel.log | tail -20

# Logs de los contenedores
docker compose logs -f --tail=200 backend
docker compose logs --since 10m queue queue-imports scheduler
docker compose logs mysql | grep -i error

# Colas y tareas programadas
docker compose exec backend php artisan queue:failed
docker compose exec backend php artisan queue:retry all
docker compose exec backend php artisan schedule:list

# Estado de los servicios y uso de recursos
docker compose ps
docker stats --no-stream
```

## Riesgos y limitaciones

- **Bloqueo como denegación de servicio.** Cualquiera que conozca un usuario puede bloquearlo con 3 intentos. La prueba exige el bloqueo y el desbloqueo por administrador; se mitiga con el aviso por correo al dueño y el límite de intentos.
- **Sesión única.** Quien cierra el navegador sin cerrar sesión no puede volver a entrar hasta que venza el token (120 minutos) o un administrador revoque la sesión.
- **Borrado lógico.** La identificación, el usuario y el correo de un usuario borrado quedan reservados.
- **El correo no se regenera al cambiar el nombre**, porque es la identidad de la cuenta.
- **Detrás de un proxy** hay que configurar `trustProxies`. Si no, el límite por IP agrupa a todos los clientes bajo la IP del proxy.
- **Carga masiva.** Las filas se procesan una a una dentro del job. Para volúmenes muy grandes (cientos de miles de filas) convendría repartir los bloques en varios jobs y paralelizarlos.
- **El historial de sesiones** se cierra en el siguiente login del usuario. Las métricas usan el vencimiento real, así que no dependen de ese cierre.
- **Mensajes del framework.** Solo están traducidos al español los de las reglas que usa la API (`lang/es/validation.php`). El resto queda en inglés.
- **Historial de commits.** El repositorio todavía no tiene commits incrementales.
# test-laravel-react
