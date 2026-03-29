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
| [docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md](docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md) | **Override Payment:** reglas por mes, cadena de unpaid (mes de cabecera vs posteriores), flujo, archivos y depuración |
| [docs/OVERRIDE_PAID_QTY.md](docs/OVERRIDE_PAID_QTY.md) | Contexto de negocio del override de cantidades y plan técnico (complementa el doc anterior) |

Otros README temáticos en la raíz del repositorio documentan funcionalidades concretas (facturación, Firebase, etc.).

### Flujo Override Payment (resumen)

- **Modelo:** cabecera `invoice_override_payment` (proyecto + `date`) y líneas `invoice_item_override_payment` por `project_item` (`paid_qty`, `unpaid_qty`). No se reescribe el histórico en `invoice_item`; los cálculos usan **cantidades efectivas** cuando aplica el override.
- **Criterio de mes (misma ventana para elegir cabecera):** solo caben cabeceras con **mes(cabecera) ≤ mes(invoice.start)** (el invoice es del mes del override o posterior). Entre las candidatas, gana la cabecera de **`date` más reciente** (`InvoiceItemOverridePaymentRepository::pickBestInvoiceItemOverrideByHeaderRule`). Los métodos `findLatestNullStartForInvoicePeriodAfterEndDate` (paid) y `findLatestOverrideWithHeaderOnOrBeforeInvoiceMonth` (unpaid) comparten ese predicado; difieren el **uso**: paid lee `paid_qty` de la fila; unpaid lee `unpaid_qty` o historial de notas y participa en **cadenas** de facturas (`ProjectService`, `InvoiceService::ListarItemsDeInvoice`).
- **Paid efectivo** (`InvoicePaidQtyOverrideResolver`): `getEffectivePaidQty` / `resolvePaidQtyDetails`; en timelines, cada `override_id` cuenta una sola vez (`paidIncrementForHistorialTimeline`).
- **Unpaid efectivo** (`InvoiceUnpaidQtyOverrideResolver`): `getEffectiveUnpaidQty`, `findUnpaidAnchorOverrideRow`, `findEarliestUnpaidOverrideHeaderDate` (partición de la línea de tiempo en facturas guardadas).
- **Unpaid encadenado con override (regla vigente en código):** en el **mismo mes calendario** que la fecha de cabecera del override, el unpaid mostrado/calculado trata el **snapshot de unpaid** como independiente del **paid** de ese período: se arrastra `snapshot + quantity − QBF` (sin restar paid en esa factura del mes del override). En **meses posteriores** la cadena es `unpaid_anterior + quantity − paid_efectivo − QBF` (sí se resta paid). Misma lógica en **`InvoiceService::ListarItemsDeInvoice`**, recálculos QBF y en **`ProjectService`** (`computeUnpaidChainingAfterOverride`, `findInvoiceItemByProjectItemAndDate` para localizar la línea del invoice en el mes de la cabecera, y en la cadena post-cabecera: si el invoice es del mismo mes que el override, `paidToSubtract = 0`). **Detalle:** [docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md#unpaid-cadena-mes-cabecera](docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md#unpaid-cadena-mes-cabecera).
- **Nuevo invoice (borrador):** `POST project/listarItemsParaInvoice` → `ProjectService::ListarItemsParaInvoice` con `fecha_inicial` / `fecha_fin` del modal; agregados y cadena unpaid usan las mismas reglas y fechas del borrador (`findPostOverrideRowForInvoicePeriod`, `findOverrideRowForUnpaidChaining`, `computeUnpaidChainingAfterOverride`, etc.).
- **Invoice guardado / export:** `InvoiceService::CargarDatosInvoice` → `ListarItemsDeInvoice`: paid vía resolver; unpaid con línea temporal de facturas del ítem y partición por `findEarliestUnpaidOverrideHeaderDate` + ancla alineada al resolver (con la distinción mes de override vs meses posteriores descrita arriba).
- **Depuración:** trazas en `InvoiceService::logOverrideInvoice`, `ProjectService::logUnpaidQtyCalc` / `logCompletionPaidTrace` y `OverridePaymentWritelog` están **desactivadas** por defecto (cuerpos comentados). Si se activan, conviene `writelogPublic` → `public/weblog.txt`. Detalle en [docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md](docs/OVERRIDE_PAYMENT_FECHAS_INVOICE.md#depuración-trazas).

## Arranque rápido (desarrollo)

1. `composer install`
2. Copiar y ajustar variables de entorno (p. ej. `.env.dev` → `.env.local`)
3. `symfony server:start` o el vhost que uses hacia `public/`
4. Consola Symfony: `php bin/console`

Para detalles de despliegue, seguridad y convenciones internas, ver **docs/ARCHITECTURE.md**.
