-- Eliminar compañías “huérfanas” con criterios estrictos de negocio e integridad.
-- Fecha: 2026-05-08
--
-- Regla de negocio (obligatoria): NO se eliminan compañías que tengan
--   - algún proyecto asociado (`project.company_id`), o
--   - algún vínculo con estimados (`estimate.company_id`, `estimate_company.company_id`).
-- Es decir: si la compañía aparece en proyectos o en estimados, queda fuera del borrado.
--
-- ADVERTENCIA: El DELETE es irreversible. Ejecutar primero los SELECT en un entorno de prueba.
--
-- Además, por integridad referencial, el DELETE solo aplica a compañías sin filas en
-- `company_contact` (y ya excluidas por proyecto y estimates como arriba).
--
-- Si necesitas borrar casos con contactos u otras referencias, limpia o reasigna antes.

-- 0) Opcional — compañías sin proyecto pero aún ligadas a estimates (protegidas: NO borrar)
SELECT
  c.`company_id`,
  c.`name`
FROM `company` c
WHERE NOT EXISTS (
  SELECT 1
  FROM `project` p
  WHERE p.`company_id` <=> c.`company_id`
)
  AND (
    EXISTS (
      SELECT 1
      FROM `estimate` e
      WHERE e.`company_id` <=> c.`company_id`
    )
    OR EXISTS (
      SELECT 1
      FROM `estimate_company` ec
      WHERE ec.`company_id` <=> c.`company_id`
    )
  );

-- 1) Inspección: sin proyecto ni vínculos en estimados (pueden seguir teniendo contactos)
SELECT
  c.`company_id`,
  c.`name`
FROM `company` c
WHERE NOT EXISTS (
  SELECT 1
  FROM `project` p
  WHERE p.`company_id` <=> c.`company_id`
)
  AND NOT EXISTS (
    SELECT 1
    FROM `estimate` e
    WHERE e.`company_id` <=> c.`company_id`
  )
  AND NOT EXISTS (
    SELECT 1
    FROM `estimate_company` ec
    WHERE ec.`company_id` <=> c.`company_id`
  );

-- 2) Candidatas realmente borrables (sin proyecto, sin estimates, sin contactos)
SELECT
  c.`company_id`,
  c.`name`
FROM `company` c
WHERE NOT EXISTS (
  SELECT 1
  FROM `project` p
  WHERE p.`company_id` <=> c.`company_id`
)
  AND NOT EXISTS (
    SELECT 1
    FROM `company_contact` cc
    WHERE cc.`company_id` <=> c.`company_id`
  )
  AND NOT EXISTS (
    SELECT 1
    FROM `estimate` e
    WHERE e.`company_id` <=> c.`company_id`
  )
  AND NOT EXISTS (
    SELECT 1
    FROM `estimate_company` ec
    WHERE ec.`company_id` <=> c.`company_id`
  );

-- 3) Borrado (misma condición que el SELECT 2)
-- DELETE FROM `company`
-- WHERE NOT EXISTS (
--   SELECT 1
--   FROM `project` p
--   WHERE p.`company_id` <=> `company`.`company_id`
-- )
--   AND NOT EXISTS (
--     SELECT 1
--     FROM `company_contact` cc
--     WHERE cc.`company_id` <=> `company`.`company_id`
--   )
--   AND NOT EXISTS (
--     SELECT 1
--     FROM `estimate` e
--     WHERE e.`company_id` <=> `company`.`company_id`
--   )
--   AND NOT EXISTS (
--     SELECT 1
--     FROM `estimate_company` ec
--     WHERE ec.`company_id` <=> `company`.`company_id`
--   );
