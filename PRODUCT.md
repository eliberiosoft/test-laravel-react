# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Personal interno de una organización.

- **Administradores** (TI o seguridad): crean, editan, desactivan y eliminan cuentas de usuarios comunes, desbloquean cuentas, revocan sesiones, hacen cargas masivas desde CSV/XLSX y revisan indicadores de cuentas, sesiones e intentos fallidos.
- **Usuarios comunes** (empleados): inician sesión, ven su bienvenida (datos, última sesión, intentos fallidos) y editan sus datos permitidos y su contraseña.

Uso principal en escritorio durante la jornada laboral; también desde el celular.

## Product Purpose

Autenticación y administración de usuarios con seguridad verificable: sesión única por usuario, bloqueo al tercer intento fallido, recuperación de contraseña con token de un solo uso, auditoría de accesos y permisos por rol. El éxito es que un administrador gestione cuentas sin errores ni ambigüedad y que cada usuario entienda el estado de su cuenta y de su acceso.

## Operating Context

- API REST propia (`apps/backend`, Laravel) con tokens Bearer que vencen; el frontend debe manejar la sesión expirada.
- El menú se carga desde la base de datos según el rol (`GET /api/menu`).
- La carga masiva es asíncrona: se sube el archivo, se consulta el progreso y se revisa el resultado por fila.
- Correos de recuperación y de cuenta bloqueada (Mailpit en desarrollo).

## Capabilities and Constraints

- Login por usuario o correo; mensajes genéricos que no revelan si una cuenta existe.
- Un segundo inicio de sesión con una sesión activa se rechaza (409) con un mensaje claro.
- Los administradores gestionan usuarios comunes; no pueden modificar a otros administradores.
- Los usuarios comunes solo editan nombres, fecha de nacimiento y contraseña.
- Paginación, filtros y orden se resuelven en el servidor.
- Reglas de negocio: usuario de 8 a 20 caracteres alfanuméricos con una mayúscula y un número; contraseña de 8 o más caracteres con una mayúscula y un signo, sin espacios; identificación de 10 dígitos sin cuatro iguales seguidos.

## Stack

React 19 con Vite y TypeScript, React Router, TanStack Query y Tailwind CSS 4 con el lenguaje visual de Tailwind Plus Application UI (decisión del usuario). Sin librería de componentes: las primitivas viven en `apps/frontend/src/shared/ui`.

## Brand Commitments

Sin marca ni logos de terceros. Nombre neutro: «Gestión de usuarios». Interfaz en español.

## Evidence on Hand

- Usuarios demo: `Admin001` (administrador) y `Jpiguave01` (usuario común), definidos en `apps/backend/database/seeders/DemoUsersSeeder.php`.
- Plantilla de carga masiva: `docs/import-sample.csv`.
- No hay testimonios, métricas ni contenido de marketing; no deben inventarse.

## Product Principles

1. La seguridad se explica, no se esconde: el estado de la cuenta y de la sesión se comunica con claridad, sin revelar información a terceros.
2. Cada acción administrativa es explícita y reversible cuando es posible; las destructivas piden confirmación.
3. El servidor es la autoridad: la interfaz refleja sus reglas y sus errores, no las duplica con criterios distintos.
4. Ninguna tarea depende del tamaño de la pantalla.

## Accessibility & Inclusion

Accesibilidad básica requerida por la prueba: HTML semántico, formularios etiquetados con errores asociados, navegación por teclado, foco visible y contraste suficiente.
