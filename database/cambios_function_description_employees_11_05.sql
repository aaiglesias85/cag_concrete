-- Distinguir las tres funciones que tenían description = 'Employees'
-- ID 14 (url: employees)        → empleados de campo usados en schedules/proyectos
-- ID 20 (url: reporte_employee) → reporte de empleados
-- ID 35 (url: employee_rrhh)    → empleados HR (SSN, fechas contratación, etc.)

UPDATE `function` SET `description` = 'Field Employees'  WHERE `function_id` = 14;
UPDATE `function` SET `description` = 'Employee Report'  WHERE `function_id` = 20;
UPDATE `function` SET `description` = 'HR Employees'     WHERE `function_id` = 35;
