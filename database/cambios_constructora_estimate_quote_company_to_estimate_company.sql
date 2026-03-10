-- --------------------------------------------------------
-- estimate_quote_company: relacionar con estimate_company (contacto/email)
-- En lugar de company_id, se usa estimate_company_id para enviar al email
-- del contacto (estimate_company.contact_id -> company_contact.email).
-- --------------------------------------------------------

-- 1. Añadir columna estimate_company_id (nullable para migración)
ALTER TABLE `estimate_quote_company`
  ADD COLUMN `estimate_company_id` int(11) DEFAULT NULL AFTER `estimate_quote_id`;

-- 2. Migrar datos: por cada fila actual (estimate_quote_id, company_id) asignar
--    un estimate_company_id (mismo estimate que la quote y mismo company_id).
--    Si hay varios estimate_company para la misma company, se toma uno (MIN id).
UPDATE `estimate_quote_company` eqc
INNER JOIN `estimate_quote` q ON q.`id` = eqc.`estimate_quote_id`
SET eqc.`estimate_company_id` = (
  SELECT ec.`id`
  FROM `estimate_company` ec
  WHERE ec.`estimate_id` = q.`estimate_id`
    AND ec.`company_id` = eqc.`company_id`
  ORDER BY ec.`id`
  LIMIT 1
);

-- 3. Eliminar filas que no tengan estimate_company (company ya no en el estimate)
DELETE eqc FROM `estimate_quote_company` eqc
INNER JOIN `estimate_quote` q ON q.`id` = eqc.`estimate_quote_id`
WHERE eqc.`estimate_company_id` IS NULL;

-- 4. Eliminar FK y columna company_id
ALTER TABLE `estimate_quote_company`
  DROP FOREIGN KEY `Refestimate_quote_company2`;

ALTER TABLE `estimate_quote_company`
  DROP KEY `Refestimate_quote_company2`;

ALTER TABLE `estimate_quote_company`
  DROP COLUMN `company_id`;

-- 5. estimate_company_id NOT NULL e índice/FK
ALTER TABLE `estimate_quote_company`
  MODIFY COLUMN `estimate_company_id` int(11) NOT NULL;

ALTER TABLE `estimate_quote_company`
  ADD KEY `Refestimate_quote_company_estimate_company` (`estimate_company_id`);

ALTER TABLE `estimate_quote_company`
  ADD CONSTRAINT `Refestimate_quote_company_estimate_company`
  FOREIGN KEY (`estimate_company_id`) REFERENCES `estimate_company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
