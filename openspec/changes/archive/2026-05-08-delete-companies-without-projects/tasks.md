## 1. Script SQL en `database/`

- [x] 1.1 Crear `database/cambios_constructora_delete_companies_without_projects_08_05.sql` con comentarios de propósito, advertencia de borrado y dependencias FK (`company_contact`, `estimate`, `estimate_company`).
- [x] 1.2 Añadir `SELECT` que deje clara la exclusión por estimados (p. ej. sin proyecto pero con `estimate` / `estimate_company`) y otro `SELECT` de candidatas sin proyecto **ni** estimados.
- [x] 1.3 Añadir `SELECT` de “solo borrables”: sin `company_contact` además de sin proyecto y sin estimados (o documentar pasos previos si el negocio prefiere otra política).
- [x] 1.4 Añadir `DELETE` acotado con la misma condición acordada en 1.2–1.3; si hace falta borrar `company_contact` antes, incluirlo en orden explícito en el mismo archivo comentado o como sentencias previas claramente separadas.

## 2. Verificación

- [ ] 2.1 Ejecutar los `SELECT` en un entorno de prueba y validar el recuento con negocio antes de cualquier `DELETE`.
- [ ] 2.2 Confirmar que el `DELETE` no viola FKs (prueba en copia de BD).
