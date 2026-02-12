-- Add company_contact_id to project_contact
-- Project contacts now reference CompanyContact; name/email/phone kept for legacy data

-- 1. Add column
ALTER TABLE `project_contact` ADD COLUMN `company_contact_id` int(11) DEFAULT NULL AFTER `project_id`;

-- 2. Populate company_contact_id by matching email with company_contact (same company as project)
UPDATE `project_contact` pc
INNER JOIN `project` p ON pc.project_id = p.project_id
INNER JOIN `company_contact` cc ON cc.company_id = p.company_id
    AND cc.email IS NOT NULL
    AND cc.email != ''
    AND pc.email IS NOT NULL
    AND pc.email != ''
    AND LOWER(TRIM(cc.email)) = LOWER(TRIM(pc.email))
SET pc.company_contact_id = cc.contact_id;

-- 3. Add index and foreign key
ALTER TABLE `project_contact` ADD KEY `IDX_project_contact_company_contact` (`company_contact_id`);
ALTER TABLE `project_contact` ADD CONSTRAINT `FK_project_contact_company_contact`
    FOREIGN KEY (`company_contact_id`) REFERENCES `company_contact` (`contact_id`);
