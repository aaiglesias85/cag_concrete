# Constructora

Aplicación empresarial para gestión de proyectos de construcción: estimaciones, facturación, seguimiento de obra, integraciones contables y app móvil vía API.

## Stack principal

- **Backend:** PHP 8.2+, Symfony 7.2 (monolito desplegable único)
- **Persistencia:** Doctrine ORM 3, una base de datos relacional (configuración vía `DATABASE_URL`)
- **Interfaces:** panel web admin (Twig + sesión), API REST con autenticación por token, endpoints HTTP para tareas programadas (cron) e integración QuickBooks Web Connector (QBWC)

## Documentación

| Documento | Contenido |
|-----------|-------------|
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Arquitectura actual, trade-offs (monolito vs microservicios, etc.), complejidad y líneas de evolución recomendadas |
| [docs/PHASE_A_REDUCIR_COMPLEJIDAD.md](docs/PHASE_A_REDUCIR_COMPLEJIDAD.md) | Guía de implementación de la Fase A: capas, módulos, migraciones y tests (sin Messenger) |

Otros README temáticos en la raíz del repositorio documentan funcionalidades concretas (facturación, Firebase, etc.).

## Arranque rápido (desarrollo)

1. `composer install`
2. Copiar y ajustar variables de entorno (p. ej. `.env.dev` → `.env.local`)
3. `symfony server:start` o el vhost que uses hacia `public/`
4. Consola Symfony: `php bin/console`

Para detalles de despliegue, seguridad y convenciones internas, ver **docs/ARCHITECTURE.md**.
