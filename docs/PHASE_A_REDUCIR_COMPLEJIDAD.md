# Guía de implementación — Fase A: reducir complejidad (sin Messenger)

Este documento es el **plan de trabajo** para aplicar la *Fase A* descrita en [ARCHITECTURE.md](ARCHITECTURE.md), **excluyendo Symfony Messenger** (no se usará colas asíncronas en esta fase).

**Objetivo:** bajar la complejidad cognitiva y el acoplamiento **sin** cambiar el modelo de despliegue (sigue siendo un monolito Symfony), mediante convenciones de capas, módulos lógicos, migraciones y tests.

---

## 1. Alcance de la Fase A (versión acordada)

| Incluido | Excluido (explícito) |
|----------|----------------------|
| Acotar capas (controladores → servicios → repositorios) | Extraer microservicios |
| Módulos lógicos por dominio (namespaces / carpetas) | Symfony Messenger / workers |
| Nuevos cambios de esquema vía Doctrine Migrations | Reescritura masiva del frontend |
| Tests de integración en flujos críticos | “Big bang” refactor de todo el `src/` |

---

## 2. Principios que regirán los cambios

### 2.1 Una responsabilidad por capa

| Capa | Responsabilidad | Evitar |
|------|-----------------|--------|
| **Controller** | HTTP: request/response, status codes, delegar en un servicio | Reglas de negocio, SQL, bucles sobre entidades complejas |
| **Servicio de aplicación** (orquestación) | Casos de uso: coordina repositorios y otras operaciones; transacciones (`EntityManager`) cuando aplique | Conocer detalles de Twig, sesión o headers HTTP |
| **Repositorio** | Consultas DQL/SQL, `find`, persistencia repetible | Reglas de negocio que deban reutilizarse fuera del listado |
| **Entidad** | Estado, invariantes simples, métodos de dominio pequeños si aportan claridad | Lógica que requiera muchos servicios inyectados |

*Nota:* Hoy gran parte de la lógica vive en `App\Utils\Admin\*Service`. **No es obligatorio renombrar todo de golpe**; sí conviene que **el código nuevo** y los refactors **sigan** la separación anterior, aunque el namespace siga siendo `Utils` temporalmente.

### 2.2 Refactor incremental (regla de oro)

- Elegir **un flujo vertical** (p. ej. “override de pago en factura”, “retainage”, un endpoint de API concreto).
- Mover lógica **solo** lo necesario para que el controlador quede delgado y la regla tenga un sitio único.
- **No** “limpiar” ficheros no tocados por la tarea actual (evita difs enormes y riesgo de regresiones).

### 2.3 Dependencias entre módulos

- Los módulos de **informes** o **UI** no deben importar clases internas de **integraciones** (QBWC, Firebase) salvo a través de una **interfaz o fachada** en el mismo dominio que las use.
- Preferir dependencias **hacia dentro** (desde features periféricos hacia núcleo de dominio), no al revés.

---

## 3. Módulos lógicos (cómo organizarlos)

### 3.1 Mapa de dominios (orientativo)

Agrupar por **lenguaje de negocio** coherente, no solo por nombre técnico:

- **Proyecto y obra** — proyectos, etapas, ítems, adjuntos, tipos.
- **Estimación y presupuestos** — estimates, quotes, plantillas, notas.
- **Facturación y cobros** — invoices, pagos, retainage, overrides, notas.
- **Data tracking / campo** — seguimiento, materiales, subcontratas en obra.
- **Personas y permisos** — usuarios, roles, perfiles.
- **Integraciones** — QuickBooks/QBWC, correo transaccional, Google, Firebase (cada una con puntos de entrada claros).

### 3.2 Convención de namespaces (evolutiva)

**Paso 1 (bajo riesgo):** documentar en qué dominio cae cada área grande (`docs/` ya tiene temas por feature).

**Paso 2 (cuando se toque código):** introducir subnamespaces bajo `App\` que reflejen dominio, por ejemplo:

- `App\Invoice\` — servicios y, más adelante, DTOs específicos de facturación.
- `App\Integration\QuickBooks\` — adaptadores hacia QB.

No hace falta mover **todas** las clases de una vez: **mover al crear o al refactorizar** un caso de uso.

### 3.3 Relación con lo existente

- `src/Controller/Admin/*` y `src/Routes/Admin/*` pueden **permanecer**; los controladores solo **importan** servicios desde namespaces de dominio nuevos o desde `Utils` existentes.
- `src/Entity` y `src/Repository` pueden permanecer centralizados al principio; opcionalmente, más adelante, agrupar repositorios por dominio si el equipo lo valora.

---

## 4. Repositorios vs servicios (criterio práctico)

- **Listados con filtros, ordenación, paginación** → pueden quedarse en el repositorio **si** son consultas puras.
- **“Si el total retenido supera X entonces…”** o **cálculos de negocio** compartidos por admin y API → **servicio de aplicación** (un solo lugar).
- Si el mismo criterio se repite en dos controladores → **extraer método en servicio**, no copiar.

---

## 5. Migraciones de base de datos (Doctrine)

1. **A partir de ahora:** cualquier cambio de esquema nuevo se versiona con **`doctrine:migrations:diff`** (o migración escrita a mano si hace falta) y se revisa en PR.
2. Los scripts históricos en `database/` se **mantienen como referencia**; no borrarlos sin acuerdo.
3. Documentar en el mensaje de commit o en el PR **qué entorno** debe aplicar la migración y si requiere datos de transición.

*(Si `migrations/` está vacío, la primera migración puede ser una “baseline” acordada con el estado real de la BD de desarrollo.)*

---

## 6. Tests de integración (alcance Fase A)

- **Prioridad:** flujos con dinero o inconsistencias costosas — facturación, pagos, retainage, overrides de cantidades/fechas.
- **Herramienta:** PHPUnit + `WebTestCase` o `KernelTestCase` + transacciones o base `test` (según lo ya configurado en el proyecto).
- **Mínimo viable:** un test que ejecute el **servicio** con fixtures mínimas y aserte el resultado (no hace falta cubrir toda la app).

Los tests dan **red de seguridad** para los refactors de capas; sin ellos, la Fase A es más frágil.

---

## 7. Orden sugerido de trabajo (iteraciones)

Cada iteración debe ser **entregable** (mergeable) y **acotada**.

1. **Fijar convención** en el equipo (este documento + revisión rápida).
2. **Elegir un primer flujo vertical** (el más doloroso o el que vais a tocar ya).
3. **Extraer** lógica del controlador → servicio; del repositorio → servicio si es regla de negocio.
4. **Añadir** al menos un test de integración para ese flujo.
5. **Repetir** con el siguiente flujo; opcionalmente introducir un namespace de dominio nuevo en ese mismo PR.

---

## 8. Lista de comprobación por pull request

- [ ] El controlador solo coordina HTTP y delega en servicios.
- [ ] No se duplica lógica de negocio copiada entre admin y API sin extraerla.
- [ ] Cambios de BD van con migración Doctrine (si aplica).
- [ ] Tests actualizados o nuevos para el comportamiento crítico tocado.
- [ ] Diff acotado al flujo acordado (sin refactors masivos no relacionados).

---

## 9. Qué no haremos en esta fase

- No introducir Messenger ni workers.
- No dividir el repositorio en múltiples aplicaciones desplegables.
- No exigir renombrar `Utils` completo antes de seguir avanzando.

---

## 10. Relación con otros documentos

- Visión global: [ARCHITECTURE.md](ARCHITECTURE.md).
- Reglas de negocio por tema: `docs/*.md` (facturación, retainage, overrides, etc.) — al refactorizar, **actualizar** el doc si cambia el comportamiento.

---

*Guía viva: actualizar este fichero si la convención de namespaces o el orden de prioridades cambia.*
