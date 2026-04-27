# Roadmap de mejoras: Symfony 7, PHP 8.2+ y arquitectura

Este documento resume el **análisis del estado actual** del proyecto y propone **mejoras alineadas con buenas prácticas de Symfony y PHP**, priorizadas para **implementarlas poco a poco**. Cada sección explica *qué* cambiar, *por qué*, *cómo* abordarlo y *qué beneficios* aporta.

**Stack actual identificado (referencia):** Symfony 7.2, PHP ≥ 8.2, Doctrine ORM 3.4, panel web `/admin` + API bajo `/api`, seguridad con firewall `main` (form login) y `api` (token), rutas en YAML bajo `src/Routes/`, repositorios con consultas custom, capa de permisos por “función” (`FunctionId`) y servicio `AdminAccessService` en evolución positiva.

### Ya implementado (fase inicial, bajo impacto)

- **PHPStan** (nivel 5 + extensión Symfony):
  - **`composer phpstan`**: analiza `src/` con las mismas reglas **siempre**; **`phpstan-baseline.neon` no desactiva el análisis**, solo hace que **no fallen** los avisos *ya inventariados* (deuda vieja). **Código nuevo o cambios que generen un problema nuevo** siguen haciendo fallar el comando hasta corregirlos o actualizar el baseline a conciencia.
  - **`composer phpstan:full`**: misma configuración **sin** baseline → ves **toda** la deuda (cientos de avisos), útil para auditorías o planificar fixes.
  - Config: `phpstan.base.neon` (reglas), `phpstan.neon` (base + baseline), `phpstan.full.neon` (solo base). Antes de analizar: `bin/console cache:warmup --env=dev`. Si el equipo acordó regenerar baseline: `vendor/bin/phpstan analyse --memory-limit=512M --generate-baseline phpstan-baseline.neon` (con `phpstan.neon` activo).
- **PHP-CS-Fixer** (reglas `@Symfony`, ámbito `src/`, `tests/`, `config/`): `composer cs-check` / `composer cs-fix`. No entra en `composer quality` hasta normalizar el estilo del repo por partes.
- **Hook Git `pre-push`** (`.githooks/pre-push`): antes de cada `git push` ejecuta `composer cs-fix` y, si el árbol queda limpio, `composer phpstan`. Activar una vez por clon: `composer install-git-hooks` (configura `core.hooksPath .githooks`). Omitir si hace falta: `SKIP_PRE_PUSH=1 git push` o `git push --no-verify`.
- **Metronic (estáticos):** tema y JS propios en `public/assets/metronic8/` (antes `public/bundles/metronic8/`), referencias vía `asset('assets/metronic8/...')` para no ser borrados por `assets:install`.
- **Tests de humo:** `tests/SmokeTest.php` + `composer test`. En entorno `test`, Doctrine usa SQLite en `var/test.db` (sin MySQL obligatorio).
- **Login throttling** en el firewall `main`: 5 intentos / 15 minutos (ajustable en `config/packages/security.yaml`).
- **Doctrine:** la opción MySQL `sql_mode` solo aplica en `dev` y `prod`, para no romper SQLite en tests.

---

## 1. Visión de arquitectura actual (breve)

| Área | Lo que hoy tenés (patrón) | Comentario |
|------|---------------------------|------------|
| **Controladores** | Acciones que delegan en `*Service` y componen plantillas/JSON | Coherente con Symfony; se beneficia de más delgadez y menos duplicación. |
| **Lógica de negocio** | `App\Service\Admin`, `App\Service\App` y `App\Service\Base` | Namespace unificado en `App\Service\*` (antes `App\Utils\*`). |
| **Clase `Base`** | Muy grande, inyecta el contenedor, `getDoctrine()`, `container->get` puntuales | Típico cuello de botella para tests, refactors y tipado. |
| **Inyección de dependencias** | Autowire en `config/services.yaml`; algunos usos de `ContainerInterface` y `container->get` | Funciona, pero dificulta el análisis estático y los tests unitarios. |
| **Autorización** | `AdminAccessService` + lógica antigua en `Base::BuscarPermiso` (en migración) | Buen paso: centralizar en un servicio dedicado. |
| **Pruebas automáticas** | Casi inexistentes más allá del `bootstrap` | Alto riesgo de regresiones al cambiar lógica crítica. |

Este roadmap **no** obliga a reescribir todo; apunta a **mover el código hacia un modelo más predecible** sin frenar el desarrollo de producto.

---

## 2. Prioridad alta — base para todo lo demás

### 2.1. Reducir el “service locator” (contenedor genérico)

**Qué es:** Uso de `ContainerInterface` y `$this->container->get(...)` para obtener servicios o Doctrine en tiempo de ejecución en lugar de inyectar dependencias explícitas en el constructor.

**Por qué importa:** En Symfony, el contenedor es una herramienta de *composición*, no un sustituto de la inyección de dependencias. Cada `get()` oculta qué usa realmente la clase, rompe el autowiring, complica el análisis de tipos (PHPStan) y hace imposible mockear en tests sin reemplazar el contenedor entero.

**Cómo implementarlo (gradual):**

1. Inyectar **interfaces concretas**: `EntityManagerInterface` o `ManagerRegistry` (o repositorios específicos) en lugar de `getDoctrine()`.
2. Sustituir `container->get(WidgetAccessService::class)` por un parámetro del constructor `WidgetAccessService $widgetAccessService`.
3. En `ProjectService` (y similares), reemplazar `get(AdminProjectService::class)` por inyección directa de `AdminProjectService` (o un interfaz + binding en `services.yaml` si acoplamos demasiado).
4. Mantener *como excepción* muy acotada el contenedor (p. ej. fábricas realmente dinámicas) y documentar el motivo.

**Ganancias:** Código autodocumentado, tests más simples, menos sorpresas al actualizar Symfony, mejor soporte del IDE y de PHPStan.

---

### 2.2. Partir o diluir la clase `Base` (Dios object)

**Qué es:** Una sola clase heredada con cientos o miles de líneas, muchos `use` de entidades, acceso a mailer, security, logger, y helpers mezclados con reglas de negocio.

**Por qué importa:** Viola el *Single Responsibility Principle*; cualquier cambio toca un archivo masivo; es difícil probar y de difícil adopción para nuevas personas en el equipo.

**Cómo implementarlo (por etapas, sin “big bang”):**

1. **No añadir** métodos nuevos a `Base` salvo extrema necesidad; crear servicios en `App\Service\...` o sub-namespaces `App\Domain\...` / `App\Application\...` según prefieran nombrar la capa.
2. Identificar *agrupaciones naturales* (p. ej. “adjuntos de proyecto”, “permisos/roles”, “exportaciones Excel/PDF”) y extraer a clases inyectables.
3. Donde aún haya herencia, usar **traits** muy pequeños y con nombre claro (p. ej. `AuthorizationTrait` *solo* si el equipo acepta traits; si no, preferir servicios inyectados).
4. Sustituir progresivamente las llamadas desde `DefaultService` y demás hacia esos servicios.

**Ganancias:** Refactors localizados, riesgo acotado, posibilidad de reutilizar lógica fuera de la jerarquía `Base`.

---

### 2.3. Unificar nomenclatura: de `Utils` a `Service` (y subdominios)

**Qué es:** Alinear el namespace con la convención Symfony (`App\Service\...`) en lugar de `App\Utils\...` para servicios de aplicación.

**Por qué importa:** Onboarding, convención del ecosistema y búsqueda en el IDE; `Utils` suele reservarse para funciones realmente técnicas sin negocio.

**Cómo implementarlo:**

1. Elegir convención: p. ej. `App\Service\Admin\Company\CompanyService` o mantener `App\Service\Admin\CompanyService` sin anidar si el proyecto prefiere plano.
2. Mover clases con **class_alias** o renombrar en un PR acotado por módulo (empresa, facturación, etc.), actualizando imports.
3. No mezclar: un PR no debe tocar 40 módulos a la vez.

**Ganancias:** Claridad para nuevos devs, alineación con [Symfony best practices (services)](https://symfony.com/doc/current/best_practices.html).

---

## 3. Calidad y seguridad del código

### 3.1. Análisis estático: PHPStan (o Psalm) con nivel creciente

**Qué es:** Análisis de tipos y de uso de APIs en tiempo de *CI* o local, sin ejecutar la app.

**Por qué importa:** PHP 8.x con propiedades tipadas y *readonly* brinda mucho, pero un proyecto grande acumula `mixed`, arrays sin forma y accesos nulos. PHPStan detecta bugs antes de producción.

**Cómo implementarlo:**

1. `composer require --dev phpstan/phpstan-symfony` (y el plugin de Doctrine si aplica).
2. Añadir `phpstan.neon` con nivel 5 o 6 inicial y **exclusión o baseline** de errores viejos (`--generate-baseline`).
3. En CI: fallar en nuevos errores, ir bajando el baseline módulo a módulo.

**Ganancias:** Menos *null pointer*, menos parámetros invertidos, mejor confianza al subir de versión de PHP o Symfony.

---

### 3.2. CS Fixer o ECS (estilo de código unificado)

**Qué es:** Formato automático (comillas, espacios, `declare(strict_types=1)` si lo adoptan, orden de `use`).

**Por qué importa:** Los diffs de PR se centran en lógica, no en estilo; se reduce el ruido en revisiones.

**Cómo implementarlo:** Reglas PSR-12 o el set de Symfony, ejecutado en pre-commit o CI.

**Ganancias:** Coherencia visual; menos fricción en *code review*.

---

### 3.3. Activar o endurecer *login throttling* (admin y API)

**En el `security.yaml` se observa `login_throttling: null` en el firewall `main`.** En entornos expuestos a internet, el *throttling* de intentos de login es una defensa básica contra fuerza bruta.

**Cómo implementarlo:** Seguir la [documentación de Login Throttling de Symfony](https://symfony.com/doc/current/security.html#limiting-login-attempts) y ajustar límites a la realidad de usuarios (p. ej. 5 intentos / minuto, con *lock* temporal).

**Ganancias:** Mejor postura de seguridad sin tocar lógica de negocio.

---

### 3.4. Actualizar identificadores de acceso anónimo (deprecations)

**Qué es:** En Symfony 5.4+ se deprecó en favor de `PUBLIC_ACCESS` ciertas formas de expresar acceso anónimo.

**Cómo:** Revisar `access_control` y migrar según [upgrade guide](https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.0.md#security) del major que corresponda; ejecutar con deprecations en logs en entorno de staging.

**Ganancias:** Preparación limpia para el próximo salto de versión de Symfony.

---

## 4. Capa de dominio y capa HTTP

### 4.1. DTOs (Data Transfer Objects) y `Validator` para entradas

**Qué es:** Clases inmutables o casi inmutables que representan lo que el cliente envía (formulario, JSON) con restricciones `Assert\*` o validación en servicio.

**Por qué importa:** Hoy mezcláis `Request` crudo, arrays y validación manual. Los DTOs acotan contratos, facilitan documentación OpenAPI y tests.

**Cómo implementarlo:**

1. Para un endpoint o acción, crear `CreateCompanyRequest` (nombre de ejemplo) con atributos/Assert.
2. Usar `#[MapRequestPayload]` (Symfony 6.3+) o resolver manual + `$request->getPayload()` con validación explícita.
3. Reutilizar el DTO en la acción: si falla, respuesta 422 unificada.

**Ganancias:** Validación centralizada, API más predecible, menos *if* anidados en controladores.

---

### 4.2. *Voters* o políticas de autorización frente a solo comprobar en controlador

**Qué importa hoy:** `exigirUsuarioYPermisoVer` centraliza un patrón; sigue siendo lógica repetida en muchas acciones.

**Cómo complementar (opcional, no reemplaza todo a la vez):**

- Voter que reciba el “recurso” (p. ej. compañía) y el `FunctionId` o permiso, y se use con `isGranted` o con un *attribute* de seguridad.
- Mantiene `AdminAccessService` para redirecciones al panel web, pero alinea con el modelo de Security de Symfony.

**Ganancias:** Reutilización en API y admin, mezcla con `access_control` donde encaje, tests de autorización más claros.

---

## 5. Asincronía y desacoplamiento

### 5.1. Messenger para trabajo pesado (emails, PDF, integraciones)

**Qué es:** Enviar “mensajes” a cola (o sync al principio) en lugar de bloquear la petición HTTP.

**Por qué importa:** Ya tenéis `symfony/messenger` en dependencias; usarlos de forma estructurada mejora tiempos de respuesta y reintentos.

**Cómo:** Definir mensajes (p. ej. `SendQuoteEmailMessage`) y *handlers*; conectar transport según `MESSENGER_TRANSPORT_DSN`.

**Ganancias:** UX (respuestas rápidas), resiliencia, posibilidad de *scale* horizontal más adelante.

---

## 6. Pruebas automatizadas

### 6.1. Tests de integración con `KernelTestCase` y `WebTestCase`

**Estado actual:** Estructura de `tests/` mínima; casi no hay red de seguridad al refactorizar.

**Cómo empezar:**

1. Elegir **un flujo crítico y estable**: p. ej. login admin, o un `listar` con DataTables mock.
2. Añadir test que arranque el kernel, obtenga un servicio real o use HTTP client.
3. Objetivo realista: **no** 80% de cobertura al mes uno, sí **2–3 tests** que no se toquen y crecer por PR.

**Ganancias:** Regresiones detectadas antes; los refactors de la sección 2 se vuelven posibles con confianza.

---

## 7. API, serialización y documentación

### 7.1. Grupos de serialización o DTOs de salida

**Qué es:** No exponer entidades Doctrine directamente en JSON sin control, para evitar *lazy loading* accidental, ciclos y datos sensibles.

**Cómo:** Serializar con atributos `#[Groups]`, o devolver DTOs/array explícito desde un *transformer* o `Normalizer` dedicado.

**Ganancias:** Contratos de API estables, menos fugas de datos, mejor rendimiento (menos *queries* implícitas).

---

### 7.2. Alineación Nelmio + código

**Cómo:** A medida que introduzcáis DTOs, documentar *request/response* en anotaciones/atributos Nelmio o *schemas* reutilizables.

**Ganancias:** `/api/doc` fiable para el equipo y clientes móviles.

---

## 8. Revisión de configuración y operación

### 8.1. Servicio público solo cuando sea inevitable

Hoy `App\Service\Admin\ProjectService` está marcado `public: true` “para no cargar Doctrine al generar `api/doc`”.

**Mejor enfoque a largo plazo:** Que la generación de documentación no instancie servicios pesados (lazy proxies, o separar *doc* de la lógica de negocio). El objetivo es **no** depender de `public: true` como default.

**Ganancias:** Menor superficie de contenedor, inicialización predecible.

---

### 8.2. Variables de entorno y secretos

Asegurar documentación mínima de `env` requeridos (puede vivir en `.env` comentado o en README interno), rotación de claves (Firebase, API Google, recaptcha) y que producción use `APP_ENV=prod` y *secrets* reales, no archivos de desarrollo.

---

## 9. Orden sugerido de implementación (hoja de ruta práctica)

Agrupado en **fases** para tocar poco a poco y poder desplegar entre medias.

| Fase | Acciones | Esfuerzo aprox. | Riesgo |
|------|----------|-----------------|--------|
| **A — Fundación** | PHPStan con baseline; CS Fixer en CI; 1er test de humo (kernel o ruta) | Bajo / medio | Bajo |
| **B — DI limpia** | Quitar el próximo `container->get` más usado; inyectar repositorio o servicio; repetir en PRs pequeños | Medio | Bajo si es incremental |
| **C — Base** | Extraer *un* módulo de lógica de `Base` a un servicio dedicado; dejar de crecer `Base` | Medio | Medio; mitigar con tests |
| **D — Nombres** | `App\Utils\*` → `App\Service\*` (admin, app API, `Base`, QBWC, etc.) | Hecho (repo completo) | Bajo |
| **E — API** | DTO + validación en un endpoint nuevo o refactor de uno existente | Medio | Medio |
| **F — Seguridad ops** | Login throttling, revisar deprecations security | Bajo | Bajo |
| **G — Async** | Un message + handler de caso real (email o reporte) | Medio | Bajo con transport sync primero |

---

## 10. Criterios de “hecho” para cerrar un ítem del roadmap

- **DI:** Clase no usa `ContainerInterface` salvo justificación comentada en 2–3 líneas.
- **Base:** Tamaño o complejidad *no* crece en un merge que su propósito no sea justamente refactor.
- **PHPStan:** Nivel acordado verde en CI, sin *baseline* nuevo salvo aprobado.
- **Test:** Nuevo código crítico cubierto o “justificación en PR” (spike documentado).
- **API:** Respuestas y validaciones alineadas con DTOs o *groups* de serialización.

---

## Referencias oficiales útiles

- [Symfony: Best practices](https://symfony.com/doc/current/best_practices.html)  
- [Service container / autowiring](https://symfony.com/doc/current/service_container/autowiring.html)  
- [Doctrine: Repository pattern](https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository)  
- [Security: Voters](https://symfony.com/doc/current/security/voters.html)  
- [Validation](https://symfony.com/doc/current/validation.html)  
- [Messenger](https://symfony.com/doc/current/messenger.html)  
- [Testing](https://symfony.com/doc/current/testing.html)  

---

*Documento vivo: actualizar fases a medida que se completen, anotar fecha y PR de referencia en cada fase completada.*
