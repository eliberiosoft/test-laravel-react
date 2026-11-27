---
name: Gestión de usuarios
description: Consola interna de administración de cuentas con el lenguaje de Tailwind Plus Application UI.
colors:
  indigo-accion: "oklch(51.1% 0.262 276.966)"
  indigo-accion-hover: "oklch(58.5% 0.233 277.117)"
  indigo-tinte: "oklch(96.2% 0.018 272.314)"
  indigo-halo: "oklch(93% 0.034 272.788)"
  indigo-tinta: "oklch(45.7% 0.24 277.023)"
  blanco: "#fff"
  gris-lavado: "oklch(98.5% 0.002 247.839)"
  gris-esqueleto: "oklch(96.7% 0.003 264.542)"
  gris-divisor: "oklch(92.8% 0.006 264.531)"
  gris-contorno: "oklch(87.2% 0.01 258.338)"
  gris-decorativo: "oklch(70.7% 0.022 261.325)"
  gris-secundario: "oklch(55.1% 0.027 264.364)"
  gris-descripcion: "oklch(44.6% 0.03 256.802)"
  gris-cuerpo: "oklch(37.3% 0.034 259.733)"
  gris-tinta: "oklch(21% 0.034 264.665)"
  rojo-peligro: "oklch(57.7% 0.245 27.325)"
  rojo-peligro-hover: "oklch(63.7% 0.237 25.331)"
  rojo-tinte: "oklch(97.1% 0.013 17.38)"
  rojo-tinta: "oklch(50.5% 0.213 27.518)"
  verde-tinte: "oklch(98.2% 0.018 155.826)"
  verde-tinta: "oklch(52.7% 0.154 150.069)"
  ambar-tinte: "oklch(98.7% 0.022 95.277)"
  ambar-tinta: "oklch(47.3% 0.137 46.201)"
  cielo-tinte: "oklch(97.7% 0.013 236.62)"
  cielo-tinta: "oklch(50% 0.134 242.749)"
typography:
  display:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.875rem"
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 600
    lineHeight: 1.333
    letterSpacing: "-0.025em"
  title:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 600
    lineHeight: 1.75
  body:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.43
  label:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 500
    lineHeight: 1.714
  caption:
    fontFamily: "Inter Variable, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 500
    lineHeight: 1.333
rounded:
  sm: "0.25rem"
  md: "0.375rem"
  lg: "0.5rem"
  full: "9999px"
spacing:
  "1": "0.25rem"
  "2": "0.5rem"
  "3": "0.75rem"
  "4": "1rem"
  "5": "1.25rem"
  "6": "1.5rem"
  "8": "2rem"
  "10": "2.5rem"
components:
  button-primary:
    backgroundColor: "{colors.indigo-accion}"
    textColor: "{colors.blanco}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
  button-primary-hover:
    backgroundColor: "{colors.indigo-accion-hover}"
  button-secondary:
    backgroundColor: "{colors.blanco}"
    textColor: "{colors.gris-tinta}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
  button-secondary-hover:
    backgroundColor: "{colors.gris-lavado}"
  button-danger:
    backgroundColor: "{colors.rojo-peligro}"
    textColor: "{colors.blanco}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
  button-danger-hover:
    backgroundColor: "{colors.rojo-peligro-hover}"
  button-sm:
    rounded: "{rounded.md}"
    padding: "6px 10px"
  button-link:
    textColor: "{colors.indigo-accion}"
    rounded: "{rounded.sm}"
  input:
    backgroundColor: "{colors.blanco}"
    textColor: "{colors.gris-tinta}"
    rounded: "{rounded.md}"
    padding: "6px 12px"
  input-disabled:
    backgroundColor: "{colors.gris-lavado}"
    textColor: "{colors.gris-secundario}"
  card:
    backgroundColor: "{colors.blanco}"
    rounded: "{rounded.lg}"
    padding: "24px"
  badge-green:
    backgroundColor: "{colors.verde-tinte}"
    textColor: "{colors.verde-tinta}"
    typography: "{typography.caption}"
    rounded: "{rounded.md}"
    padding: "4px 8px"
  badge-red:
    backgroundColor: "{colors.rojo-tinte}"
    textColor: "{colors.rojo-tinta}"
    rounded: "{rounded.md}"
    padding: "4px 8px"
  badge-amber:
    backgroundColor: "{colors.ambar-tinte}"
    textColor: "{colors.ambar-tinta}"
    rounded: "{rounded.md}"
    padding: "4px 8px"
  badge-sky:
    backgroundColor: "{colors.cielo-tinte}"
    textColor: "{colors.cielo-tinta}"
    rounded: "{rounded.md}"
    padding: "4px 8px"
  badge-gray:
    backgroundColor: "{colors.gris-lavado}"
    textColor: "{colors.gris-descripcion}"
    rounded: "{rounded.md}"
    padding: "4px 8px"
  nav-item:
    textColor: "{colors.gris-cuerpo}"
    typography: "{typography.label}"
    rounded: "{rounded.md}"
    padding: "8px"
  nav-item-active:
    backgroundColor: "{colors.gris-lavado}"
    textColor: "{colors.indigo-accion}"
  segmented-selected:
    backgroundColor: "{colors.indigo-accion}"
    textColor: "{colors.blanco}"
  sidebar:
    backgroundColor: "{colors.blanco}"
    width: "288px"
---

# Design System: Gestión de usuarios

## Overview

**Creative North Star: "La consola que se usa sin pensar"**

Lenguaje visual fijado por el usuario: Tailwind Plus Application UI, «Sidebar layout». El marcado es de utilidades escritas a mano y no copia código con licencia de Tailwind Plus. Todo vive sobre la paleta por defecto de Tailwind CSS v4 (valores OKLCH de `tailwindcss/theme.css`); el único token propio es la familia tipográfica (`apps/frontend/src/styles/app.css`).

Superficies blancas, grises neutros y un solo acento indigo para la acción primaria, la selección y el foco. La densidad es la de una herramienta de trabajo: texto de UI de 14px, tablas densas, formularios sobrios, encabezado de página con título a la izquierda y acción primaria a la derecha. El color de estado se reserva a badges, alertas y diálogos, siempre con su texto.

Rechazos confirmados en el contrato de dirección: el dashboard de tarjetas iguales y la métrica héroe.

**Key Characteristics:**
- Un acento (indigo-600) para acción, selección y foco; el rojo solo para destrucción y error.
- Planos blancos con anillo gris-900/5 y sombra `xs`; la profundidad real solo en diálogos.
- Inter Variable en todo; jerarquía por peso y tamaño, nunca por mayúsculas.
- Primitivas únicas en `apps/frontend/src/shared/ui/`; las pantallas las componen, no las reinventan.

## Colors

Neutros fríos de Tailwind con un acento indigo saturado y cuatro tonos semánticos en pareja tinte -50 / tinta -700.

### Primary
- **Indigo Acción** (`indigo-accion`): botón primario, enlace, ítem activo del menú, página actual de la paginación, segmento seleccionado, anillo de foco, caret de inputs. Hover en **Indigo Acción Hover** (`indigo-accion-hover`).
- **Indigo Tinte / Halo / Tinta** (`indigo-tinte`, `indigo-halo`, `indigo-tinta`): avatar de iniciales (tinte + tinta), selección de texto (`::selection` indigo-100 sobre indigo-900), fondo del icono en diálogos de tono primario (halo), zona de arrastre activa (tinte al 50%).

### Neutral
- **Blanco** (`blanco`): fondo de página, sidebar, tarjetas, inputs, filas de tabla.
- **Gris Lavado** (`gris-lavado`): cabecera de tabla, pie de diálogo, hover de filas y menú, input deshabilitado.
- **Gris Esqueleto** (`gris-esqueleto`): skeletons y pista de barras.
- **Gris Divisor** (`gris-divisor`): borde del sidebar, divisores de tabla, bordes de encabezado de panel y paginación.
- **Gris Contorno** (`gris-contorno`): contorno de inputs, anillo de botón secundario, segmentos y paginación.
- **Gris Decorativo** (`gris-decorativo`): solo iconos decorativos con `aria-hidden` (iconos del menú en reposo, icono dentro de botón secundario, icono de orden inactivo).
- **Gris Secundario** (`gris-secundario`): mínimo para texto con significado, celdas secundarias, hints, placeholders, iconos de botones solo-icono.
- **Gris Descripción / Cuerpo** (`gris-descripcion`, `gris-cuerpo`): descripciones de página, valores de listas, texto del menú.
- **Gris Tinta** (`gris-tinta`): títulos, etiquetas, texto principal (`html` usa gray-900 por defecto).

### Semánticos
- **Rojo Peligro** (`rojo-peligro`, hover `rojo-peligro-hover`): botón destructivo, acción «Eliminar» en tablas, texto de error de campo, foco de input con error (contorno red-300 en reposo).
- **Verde / Ámbar / Cielo / Rojo / Gris** (tinte + tinta): badges y alertas. Ámbar usa tinta -800 en badge por contraste.

### Named Rules
**The One Accent Rule.** Indigo es el único color de marca. Acción, selección y foco comparten indigo-600; ningún otro tono hace de acento.

**The Gray-500 Floor Rule.** Texto con significado y botones solo-icono usan como mínimo gray-500. Gray-400 queda para iconos decorativos con `aria-hidden`.

**The Tone Map Rule.** El color de un estado de dominio sale de `apps/frontend/src/shared/domain.ts` (`USER_STATUS`, `IMPORT_STATUS`, `IMPORT_ROW_STATUS`) y se pinta con `BADGE_TONES` de `shared/ui/styles.ts`. Activo/creado/completada = green, bloqueado/fallida/con errores = red, en cola/omitido = amber, procesando = sky, inactivo = gray.

## Typography

**Display Font:** Inter Variable (con ui-sans-serif, system-ui, sans-serif)
**Body Font:** Inter Variable
**Label/Mono Font:** Inter; `font-mono` solo para nombres de columnas del CSV (`ImportUploader.tsx`).

**Character:** Una sola familia neutra y legible; la jerarquía la hacen el peso (400/500/600) y el tamaño. Cifras siempre con `tabular-nums`.

### Hierarchy
- **Display** (600, 30px móvil / 48px desde `sm`, tracking-tight): solo la página 404.
- **Headline** (600, 24px/32px, tracking-tight, `text-balance`): título de página en `PageHeader`. En `AuthHeading` 700, 24px/36px.
- **Title** (600, 16px/28px): títulos de `Panel`, `StatGroup`, secciones y diálogos.
- **Body** (400, 14px/20px): texto de UI, celdas, descripciones (máx. `max-w-2xl` en descripciones de página). Inputs a 16px en móvil y 14px desde `sm` para evitar zoom en iOS.
- **Label** (500 o 600, 14px/24px): etiquetas de campo (500), botones y menú (600).
- **Caption** (500, 12px): badges, grupos del menú (600, 12px/24px, gray-500), hints de estadística.
- **Stat** (600, 30px, tracking-tight, tabular): valores de `StatGroup`; 24px en el resumen de importación.

### Named Rules
**The No-Caps Rule.** Ningún texto en mayúsculas ni con tracking ancho. Los grupos del menú son 12px semibold gray-500 en caja normal.

## Layout

- **Esqueleto** (`apps/frontend/src/layout/AppLayout.tsx`): sidebar fija de 288px (`lg:w-72`) desde `lg` (64rem); contenido con `lg:pl-72`, contenedor `max-w-7xl` y gutters 16/24/32px (`px-4 sm:px-6 lg:px-8`), `py-8 sm:py-10`.
- **Móvil (< lg):** barra superior sticky blanca con sombra xs, botón de menú y marca; el menú abre en un `<dialog>` de máx. 320px (`max-w-xs`) con backdrop gray-900/80. Se cierra solo al pasar a escritorio.
- **Auth** (`AuthLayout.tsx`): columna centrada `max-w-sm`, marca centrada, `py-12`.
- **Ritmo:** escala de 4px de Tailwind. Entre bloques de página `space-y-6`/`space-y-8` (dashboard `space-y-10`); entre controles `gap-4`; dentro de tarjetas `px-4 py-5 sm:p-6`; celdas `px-4 py-4 sm:px-6`; label→control 8px (`mt-2`).
- **Rejillas:** filtros `1 → 2 (sm) → 2fr/1fr/1fr/auto (lg)`; paneles en pares `lg:grid-cols-2 gap-6`; estadísticas 2–4 columnas con separadores de 1px (`gap-px` sobre gray-900/5).
- **Tablas responsivas** (`features/users/UsersTable.tsx`): bajo `xl` las columnas secundarias (usuario, correo, rol) se ocultan y se apilan en la primera celda como `<dl>` con `dt` sr-only; bajo `sm` también el estado. La tabla mantiene `overflow-x-auto`.
- **Paginación:** bajo `sm` solo anterior/actual/siguiente; los números y la elipsis aparecen desde `sm`.
- Breakpoints de Tailwind: sm 40rem, md 48rem, lg 64rem, xl 80rem.

## Elevation & Depth

Planos con un hilo de sombra. La profundidad se comunica con anillos finos y tonos de gris, no con sombras grandes.

### Shadow Vocabulary
- **Hilo** (`shadow-xs`: `0 1px 2px 0 rgb(0 0 0 / 0.05)`): botones primario/secundario/peligro, tarjetas, grupos de botones, barra superior móvil.
- **Modal** (`shadow-xl`: `0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)`): solo diálogos, con backdrop gray-500/75.

### Named Rules
**The Ring-Not-Border Rule.** Tarjetas y botones secundarios se delimitan con anillo (`ring-1 ring-gray-900/5`, `ring-gray-300 ring-inset`), no con borde. Los bordes `gray-200` se reservan a divisores estructurales (sidebar, cabeceras de panel, pie de tabla).

## Shapes

Esquinas suaves y consistentes: `rounded-md` (6px) para todo lo interactivo (botones, inputs, badges, alertas, ítems de menú, grupos segmentados); `rounded-lg` (8px) para contenedores (tarjetas, tablas, diálogos, zona de arrastre); `rounded-full` solo para avatar de iniciales, icono de diálogo y puntos/barras de distribución; `rounded-sm` (4px) para botones-enlace, para que el anillo de foco tenga forma. La zona de arrastre es el único borde discontinuo (`border-dashed gray-900/25`, indigo-600 al arrastrar).

## Components

Todas las recetas viven en `apps/frontend/src/shared/ui/styles.ts`; las primitivas en `shared/ui/*.tsx`.

### Buttons (`Button.tsx`, `buttonClasses`)
- **Shape:** `rounded-md` (6px), 14px semibold, `inline-flex` con icono a 8px (`gap-x-2`; `sm` 6px).
- **Tamaños:** `md` 8×12px; `sm` 6×10px.
- **Variantes:** `primary` indigo-600 → hover indigo-500; `secondary` blanco con anillo inset gray-300 → hover gray-50; `danger` red-600 → hover red-500; `link` texto indigo-600 → indigo-500 sin relleno.
- **Estados:** deshabilitado `opacity-60` y `cursor-not-allowed`, sin cambio de hover; `isLoading` muestra `Spinner` (16px), deshabilita y marca `aria-busy`. Transición de color 150ms.
- **Enlaces con apariencia de botón:** usar `buttonClasses()` sobre `<Link>` (`features/errors/ErrorPages.tsx`).
- **Acciones de fila:** variante `link`; destructiva en red-600/red-500, con nombre del registro en `sr-only`.
- **Solo-icono:** `p-2`/`p-1.5`, `rounded-md`, gray-500 → hover gray-700 sobre gray-50, etiqueta `sr-only`.

### Chips (Badge, `Feedback.tsx`)
- **Style:** `rounded-md`, 4×8px, 12px medium, anillo inset del tono (`BADGE_TONES`): green/red/amber/sky/indigo/gray, tinte -50 y tinta -700 (ámbar -800, gris -600).
- **State:** `StatusBadge` traduce un estado de dominio a etiqueta y tono; nunca un badge sin texto.

### Segmented control (`features/imports/ImportRowsTable.tsx`)
- `role="group"` con `aria-label`, botones con `aria-pressed`. Grupo `rounded-md shadow-xs`, segmentos 8×12px semibold con anillo gray-300; seleccionado indigo-600 con texto blanco; resto blanco → hover gray-50.

### Cards / Containers (`cardClasses`, `Panel`, `StatGroup`, `DescriptionList`)
- **Corner Style:** `rounded-lg` (8px).
- **Background:** blanco.
- **Shadow Strategy:** hilo `shadow-xs` + `ring-1 ring-gray-900/5`.
- **Internal Padding:** cabecera `px-4 py-5 sm:px-6` con borde inferior gray-200; cuerpo `px-4 py-5 sm:p-6`.
- **DescriptionList:** filas divididas gray-100, etiqueta 1/3 y valor 2/3 desde `sm`; valor vacío «—».
- **StatGroup:** celdas blancas separadas por 1px, etiqueta gray-500, valor 30px tabular; sin iconos ni tendencias.

### Inputs / Fields (`Form.tsx`, `controlClasses`)
- **Style:** blanco, `rounded-md`, contorno 1px gray-300 (outline inset), 6×12px, 16px → 14px/24px desde `sm`, placeholder gray-500.
- **Focus:** contorno 2px indigo-600 inset.
- **Error:** contorno red-300, foco red-600, `aria-invalid` y mensaje red-600 enlazado por `aria-describedby`. El error sustituye al hint.
- **Disabled:** fondo gray-50, texto gray-500.
- **Etiqueta:** 14px/24px medium gray-900 sobre el control; «Opcional» o acción de etiqueta a la derecha.
- **Select:** nativo con `appearance-none` y chevron Heroicons 16 solid gray-500 superpuesto.

### Navigation (`layout/AppLayout.tsx`)
- Menú cargado del servidor. Ítems 14px/24px semibold, `p-2`, `rounded-md`, icono Heroicons 24 outline de 24px. Reposo gray-700 con icono gray-400; hover y activo indigo-600 sobre gray-50.
- Grupos con encabezado 12px semibold gray-500.
- Resumen de usuario al pie: avatar de iniciales indigo-50/indigo-700, nombre y rol truncados, botón de cierre de sesión solo-icono.
- Enlace «Saltar al contenido» visible al foco, apunta a `#contenido`.

### Dialogs (`Dialog.tsx`)
- `<dialog>` nativo con `showModal()` vía `useModalDialog` (`shared/hooks.ts`). `rounded-lg`, `shadow-xl`, `sm:max-w-lg` (`lg`: `sm:max-w-2xl`), backdrop gray-500/75.
- Icono opcional en círculo (primary indigo-100/indigo-600; danger red-100/red-600). Título 16px semibold, descripción 14px gray-500.
- Pie gray-50 con acciones a la derecha; en móvil apiladas con la principal arriba (`flex-col-reverse`).
- `ConfirmDialog`: Cancelar (secundario) + confirmar (primario o peligro), con `isPending` y error en `Alert`.
- Cierre con Escape o clic en el backdrop (solo si el puntero empezó en él).

### Tables (`DataTable.tsx`)
- Dentro de una tarjeta; cabecera gray-50, 14px semibold gray-900; filas divididas gray-200, hover gray-50/60; celdas 14px gray-500 con dato principal en medium gray-900.
- Orden: botón en la cabecera con chevrons (gray-400 inactivo, gray-900 activo) y `aria-sort`.
- `caption` sr-only obligatorio. Carga inicial: 5 filas skeleton; recarga: cuerpo a opacidad 60% y `aria-busy`. Vacío: `EmptyState` en celda completa.
- `Pagination`: «Mostrando X a Y de Z resultados», grupo unido con página actual indigo-600 y `aria-current="page"`.

### Feedback (`Feedback.tsx`, `Button.tsx`)
- **Alert:** `rounded-md p-4`, fondo tinte, icono Heroicons 20 solid tono-400, título -800 y cuerpo -700; `role="alert"` para error, `status` para el resto.
- **EmptyState:** centrado, icono 40px gray-400, título 14px semibold, descripción gray-500, acción opcional.
- **Skeleton / PanelSkeleton:** gray-100 pulsante, `role="status"`. **QueryState** encadena skeleton → `QueryError` (alerta con «Reintentar») → contenido.
- **PageLoader:** spinner indigo-600 + «Cargando…».

## Do's and Don'ts

### Do:
- **Do** componer pantallas con las primitivas de `apps/frontend/src/shared/ui/` y las recetas de `styles.ts`; si falta una variante, añadirla ahí.
- **Do** usar indigo-600 para la acción primaria, la selección (página actual, segmento, ítem de menú) y el foco.
- **Do** dejar el foco visible global de `app.css` (2px indigo-600, offset 2px) y dar `rounded-sm`/`rounded-md` a todo lo enfocable.
- **Do** usar `<dialog>` nativo con `useModalDialog`: bloqueo de scroll por `html:has(dialog[open])`, foco devuelto al disparador o a `#contenido` si ya no existe.
- **Do** dar `role="alertdialog"` y `aria-describedby` a los diálogos de tono `danger`; toda acción destructiva pasa por `ConfirmDialog`.
- **Do** etiquetar cada control, enlazar hint y error con `aria-describedby` y repetir el nombre del registro en `sr-only` en acciones de fila.
- **Do** apilar columnas secundarias en la primera celda bajo `xl` (y el estado bajo `sm`) en vez de forzar scroll horizontal.
- **Do** usar `tabular-nums` en cifras, identificaciones, IPs y paginación.
- **Do** respetar `prefers-reduced-motion` (ya global en `app.css`); transiciones de color de 150ms como máximo.

### Don't:
- **Don't** usar texto con significado ni botones solo-icono por debajo de gray-500; gray-400 es solo decorativo.
- **Don't** introducir un segundo color de acento ni usar verde, ámbar o cielo para acciones.
- **Don't** construir dashboards de tarjetas iguales ni una métrica héroe; las cifras van en `StatGroup`.
- **Don't** usar sombras mayores que `shadow-xs` fuera de diálogos, ni sombras desplazadas duras.
- **Don't** poner texto en mayúsculas, tracking ancho ni etiquetas inventadas sobre el título (eyebrows). La única línea sobre un título es el código «404» de `NotFoundPage`, que es dato, no decoración.
- **Don't** usar iconos que no sean Heroicons (24 outline en menú y diálogos, 20 solid en alertas y botones, 16 solid en selects), ni glifos o emojis como iconos.
- **Don't** mostrar un estado de dominio sin pasar por los mapas de `shared/domain.ts`.
- **Don't** copiar marcado con licencia de Tailwind Plus; escribir las utilidades a mano.
