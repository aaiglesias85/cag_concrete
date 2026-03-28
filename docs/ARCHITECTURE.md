# Documento de arquitectura — Constructora

**Ámbito:** descripción del estado actual del código en el repositorio, trade-offs entre estilos arquitectónicos y orientación para evolucionar el sistema cuando la complejidad crece.

**Fecha de referencia:** marzo 2026.

---

## 1. Resumen ejecutivo

El proyecto es un **monolito Symfony** con **una base de datos** y **varias superficies de acceso** (admin web, API para app, URLs de cron y QBWC). La complejidad viene del **dominio** (muchas entidades y reglas de negocio acopladas), del **volumen de código** (controladores, servicios en `Utils/`, repositorios con lógica de consulta) y de **integraciones externas** (QuickBooks, Firebase, Google, correo, PDF/Excel).

No hay indicios de despliegue como **microservicios** independientes: un solo `composer.json`, un solo kernel y un proceso PHP que atiende todo.

**Conclusión directa:** la arquitectura “de despliegue” es **monolítica**. La pregunta útil no es solo “¿monolito o microservicios?” sino **cómo modularizar y acotar acoplamientos dentro del monolito** antes de partir servicios.

---

## 2. Estado actual (vista C4 simplificada)

### 2.1 Contenedor principal

| Elemento | Rol |
|----------|-----|
| **Aplicación Symfony** (`App\Kernel`, `public/index.php`) | Único deployable HTTP; enruta admin, API, login, crons y QBWC |
| **Doctrine ORM** | Mapeo objeto-relacional; entidades en `src/Entity/` |
| **Base de datos relacional** | Un solo `DATABASE_URL`; transacciones y joins entre dominios en la misma BD |
| **Messenger** | Configurado (`config/packages/messenger.yaml`) con transporte async y cola `failed` en Doctrine; el enrutado de mensajes de dominio está en gran parte **sin activar** (comentarios) |
| **Front admin** | Twig + assets (Metronic, Stimulus, JS por pantalla; algunos ficheros muy grandes) |

### 2.2 Superficies de uso (actores)

1. **Administración web** — `/admin`, form login, sesión, Twig.
2. **API app** — `/api`, autenticación stateless con token personalizado (`TokenAuthenticator`).
3. **Autenticación legada JSON** — rutas `/usuario/...` hacia `UsuarioController`.
4. **Tareas por HTTP** — rutas tipo `/cron-*`, `/definir-*` en `ScriptController` (trabajos batch vía petición web; típico en hosting sin workers dedicados).
5. **QuickBooks Web Connector** — `QbwcController`, cola `SyncQueueQbwc` en base de datos.
6. **Documentación OpenAPI** — NelmioApiDoc en `/api/doc`.

Esto es un patrón clásico de **monolito con varias “puertas”**, no de microservicios.

### 2.3 Organización del código (aproximado)

| Área | Observación |
|------|-------------|
| **Entidades** | ~83 entidades; dominio amplio (proyectos, estimaciones, facturas, data tracking, RRHH, mensajería, etc.) |
| **Repositorios** | ~81 repositorios; mucha lógica de listados, filtros y totales en SQL/DQL |
| **Controladores** | Decenas de controladores en `Admin`, `App`, más `ScriptController`, `QbwcController` |
| **Servicios de aplicación** | Convención `Utils/Admin/*Service` y otros; mezcla de orquestación y reglas de negocio |
| **Rutas YAML** | Fragmentadas por módulo en `src/Routes/Admin/` y `src/Routes/App/` |
| **Esquema de BD** | Scripts SQL versionados manualmente en `database/`; carpeta `migrations/` de Doctrine **sin** clases `Version*` detectables en el repositorio |

### 2.4 Integraciones

Incluyen entre otras: **QuickBooks** (QBWC), **Firebase** (push), **Google** (traducción, mapas, reCAPTCHA), **correo** (Mailer estándar + DSN específico para presupuestos), **PDF** (mPDF), **Excel** (PhpSpreadsheet), paquetes de envío (p. ej. Chilexpress en `vendor`).

Cada integración añade **acoplamiento** y superficie de fallo; en monolito se concentran en el mismo proceso y la misma base de datos.

---

## 3. Clasificación: ¿monolito, modular, microservicios?

| Estilo | ¿Aplica aquí? | Notas |
|--------|----------------|-------|
| **Monolito** | Sí | Un artefacto, un runtime, una BD principal |
| **Monolito modular** | Parcial | Hay separación por carpetas y rutas, pero sin límites estrictos entre módulos (sin bundles internos ni paquetes Composer propios) |
| **Microservicios** | No (hoy) | No hay servicios desplegables independientes con bases de datos y equipos separados |
| **Macro servicios / “minis”** | Posible evolución | Partir por dominios solo cuando haya límites claros y coste operativo asumible |

---

## 4. Trade-offs relevantes

### 4.1 Monolito vs microservicios

| | Monolito (actual) | Microservicios |
|--|-------------------|----------------|
| **Despliegue** | Simple: un artefacto | Complejo: N servicios, versionado, redes |
| **Consistencia de datos** | Transacciones ACID en una BD | Consistencia eventual, sagas, mayor complejidad |
| **Refactor / navegación** | Todo en un repo; IDE y búsqueda global | Requiere contratos API y versionado estricto |
| **Escalado** | Escala el proceso completo (o vertical) | Escala por servicio según carga |
| **Equipo pequeño** | Suele ser más productivo | Overhead de DevOps y comunicación |

**Criterio práctico:** los microservicios compensan cuando hay **equipos independientes**, **límites de dominio estables** y **necesidad real de escalar o desplegar por partes**. Si el dolor actual es “código difícil de mantener”, la primera palanca suele ser **modularización dentro del monolito** y **límites claros entre capas**, no partir servicios.

### 4.2 Un proceso vs colas asíncronas (Messenger)

Messenger está preparado pero el uso de **handlers asíncronos** parece limitado. Mantener trabajo pesado en **requests HTTP síncronos** (especialmente en crons vía URL) aumenta timeouts y acopla disponibilidad del worker web al batch.

**Trade-off:** introducir colas mejora resiliencia y tiempos de respuesta, pero exige **supervisión de workers**, reintentos y monitorización de la cola `failed`.

### 4.3 Scripts SQL sueltos vs migraciones Doctrine

Scripts en `database/` dan flexibilidad y visibilidad en SQL crudo, pero complican **reproducibilidad**, **orden de aplicación** y **CI** frente a migraciones versionadas y reversibles.

**Trade-off:** migraciones Doctrine (o Flyway/Liquibase) mejoran trazabilidad; requiere disciplina y posible migración gradual desde el histórico de `.sql`.

### 4.4 Lógica en repositorios vs servicios vs controladores

Patrón habitual en el código: repositorios con métodos de negocio de listado; servicios `Utils/*` con orquestación. El riesgo es **“modelo anémico + repositorios gordos”** o **servicios que conocen demasiadas entidades**.

**Trade-off:** concentrar reglas en **servicios de aplicación o de dominio** con interfaces claras reduce duplicación; implica refactor incremental.

### 4.5 API + admin en el mismo proyecto

**Ventaja:** una sola fuente de verdad y menos duplicación de DTOs si se comparten servicios bien diseñados.  
**Riesgo:** fugas de concerns (sesión vs stateless, validaciones distintas) y controladores que divergen.

---

## 5. Fuentes de complejidad observadas

1. **Tamaño del dominio** — muchas entidades y relaciones; cambios locales pueden tener efectos globales.
2. **Varias vías de entrada** — mismo núcleo sirve UI, API, batch y QBWC; un fallo en servicio compartido afecta todo.
3. **Integraciones** — fallos externos (QuickBooks, FCM, traducción) mezclados con flujo principal si no hay aislamiento (timeouts, circuit breakers, reintentos).
4. **Frontend admin** — JS voluminoso por pantalla dificulta el mantenimiento; conviene alinear con componentes reutilizables o un SPA solo donde compense.
5. **Deuda de esquema** — muchos scripts SQL sueltos; onboarding y entornos nuevos dependen de documentación manual.

---

## 6. Líneas de evolución recomendadas (orden pragmático)

### Fase A — Reducir complejidad sin cambiar el despliegue (alto ROI)

Guía operativa detallada (alcance, convenciones, orden de trabajo, checklist): **[PHASE_A_REDUCIR_COMPLEJIDAD.md](PHASE_A_REDUCIR_COMPLEJIDAD.md)**.

1. **Acotar capas:** reglas de negocio reutilizables en servicios dedicados; repositorios principalmente persistencia y consultas; controladores delgados.
2. **Módulos lógicos:** agrupar por dominio (facturación, estimación, proyecto, integraciones) con namespaces o subcarpetas explícitas y dependencias “hacia dentro” (p. ej. el módulo de informes no importa detalles internos de QBWC).
3. **Messenger (opcional):** mover trabajo pesado a mensajes y handlers solo si el equipo lo prioriza; no forma parte de la guía Fase A acordada en el repositorio.
4. **Migraciones:** nuevos cambios de esquema vía **Doctrine Migrations**; ir archivando o consolidando scripts legacy.
5. **Tests:** pruebas de integración en servicios críticos (facturación, pagos, overrides) para permitir refactor.

### Fase B — “Monolito modular” más estricto

- Extraer **librerías Composer internas** (p. ej. `constructora/invoice`, `constructora/qbwc`) con interfaces públicas mínimas.
- Definir **contextos acotados** (DDD ligero): cada contexto expone servicios o facades; evitar que entidades de un contexto arrastren dependencias de otro sin necesidad.

### Fase C — Extraer servicios solo con justificación

Considerar un **servicio aparte** solo si:

- hay **límites de escalado** reales (CPU/memoria en un subconjunto),
- un **equipo** puede poseer el ciclo de vida del servicio,
- el **contrato API** es estable y el coste operativo (observabilidad, despliegues) está cubierto.

Candidatos típicos en empresas de este tipo: **notificaciones push**, **generación masiva de PDF**, **conector QB** como proceso aislado — siempre empezando por **colas y límites claros dentro del monolito**.

### Fase D — Microservicios (opcional, a largo plazo)

Solo después de que los límites de dominio estén claros en código y en equipos. Patrón habitual: **strangler** (extraer primero lo más acoplado o costoso de escalar).

---

## 7. Métricas orientativas del repositorio (orden de magnitud)

Estas cifras sirven para dimensionar esfuerzo de modularización; pueden variar con el tiempo.

| Métrica | Orden de magnitud |
|---------|-------------------|
| Entidades (`src/Entity`) | ~83 |
| Repositorios | ~81 |
| Ficheros PHP en `src/` | ~270+ |
| Controladores | ~50+ |
| Rutas YAML modulares | ~46 ficheros bajo `src/Routes/` |

---

## 8. Glosario breve

- **Monolito:** una aplicación desplegable que concentra la mayor parte de la lógica.
- **Trade-off:** equilibrio entre dos objetivos (p. ej. simplicidad operativa vs escalado independiente).
- **Bounded context (DDD):** frontera de lenguaje y modelo coherente; útil para modularizar sin microservicios.
- **Strangler fig:** migrar funcionalidad poco a poco de un núcleo legacy a componentes nuevos.

---

## 9. Mantenimiento de este documento

Convendría revisar este documento cuando:

- se añadan **nuevos canales** (otra API, otro cliente),
- se **extraiga** un proceso a cola o servicio,
- se **formalice** la estrategia de migraciones de base de datos.

---

*Generado a partir de la estructura del repositorio y archivos de configuración Symfony; no sustituye diagramas de despliegue o políticas de seguridad internas.*
