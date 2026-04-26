# Menu Restructure

**Archivo afectado:** `templates/admin/layout/menu.html.twig`  
**Fecha:** 2026-04-26  
**Ticket:** CGC-450 (referencia)

---

## Estructura Nueva del Menú Lateral

### 0. Home
Enlace directo al dashboard. Controlado por `menu.menuInicio`.

---

### 1. Estimating
| Ítem | Variable backend | Ruta |
|---|---|---|
| Project Estimates | `menu.menuEstimate` | `estimate` |
| **Libraries** | | |
| → Items | `menu.menuItem` | `item` |
| → Equations | `menu.menuEquation` | `equation` |
| → Unit of Measurement | `menu.menuUnit` | `unit` |
| → Project Stages | `menu.menuProjectStage` | `project_stage` |
| → Project Types | `menu.menuProjectType` | `project_type` |
| → Proposal Types | `menu.menuProposalType` | `proposal_type` |
| → Plan Status | `menu.menuPlanStatus` | `plan_status` |
| → Districts | `menu.menuDistrict` | `district` |
| → Locations *(antes: Counties)* | `menu.menuCounty` | `county` |
| → Notes | `menu.menuEstimateNoteItem` | `estimate_note_item` |
| → Companies | `menu.menuCompany` | `company` |

---

### 2. Project Onboarding
| Ítem | Variable backend | Ruta |
|---|---|---|
| Contracts ⚠️ *nuevo* | `menu.menuContract` | `contract` |
| Companies | `menu.menuCompany` | `company` |
| Projects | `menu.menuProject` | `project` |
| Subcontractors | `menu.menuSubcontractor` | `subcontractor` |
| **Libraries** | | |
| → Concrete Classes | `menu.menuConcreteClass` | `concrete_class` |

> **Nota:** Companies aparece tanto en Estimating > Libraries como aquí. Esto es intencional — un usuario con acceso solo a Project Onboarding (sin acceso a Estimating) también necesita ver Companies.

---

### 3. Project Management
| Ítem | Variable backend | Ruta |
|---|---|---|
| Schedule | `menu.menuSchedule` | `schedule` |
| Data Tracking | `menu.menuDataTracking` | `data_tracking` |
| **Libraries** | | |
| → Inspectors | `menu.menuInspector` | `inspectors` |
| → Holidays | `menu.menuHoliday` | `holiday` |

---

### 4. Accounting

#### Accounts Receivable
| Ítem | Variable backend | Ruta |
|---|---|---|
| Invoices | `menu.menuInvoice` | `invoice` |
| Payments | `menu.menuPayment` | `payment` |
| Payment Override *(antes: Override)* | `menu.menuOverridePayment` | `override_payment` |

#### Accounts Payable
| Ítem | Variable backend | Ruta |
|---|---|---|
| Subcontractors' Report | `menu.menuReporteSubcontractor` | `reporte_subcontractor` |
| Vendors *(antes: Concrete Vendors)* | `menu.menuConcreteVendor` | `conc_vendor` |

#### Libraries
| Ítem | Variable backend | Ruta |
|---|---|---|
| Overhead Price | `menu.menuOverhead` | `overheadprice` |
| Materials | `menu.menuMaterial` | `material` |

---

### 5. HR
| Ítem | Variable backend | Ruta |
|---|---|---|
| Employees | `menu.menuEmployeeRrhh` | `employee_rrhh` |
| **Reports** | | |
| → Certified Payrolls ⚠️ *nuevo* | `menu.menuCertifiedPayroll` | `certified_payroll` |
| → Employees' Report *(antes: Employees, bajo Accounting)* | `menu.menuReporteEmployee` | `reporte_employee` |
| **Libraries** | | |
| → Ethnicity / Race | `menu.menuRace` | `race` |
| → Employees *(temporal hasta que HR module esté listo)* | `menu.menuEmployee` | `employee` |
| → Employee Roles | `menu.menuEmployeeRole` | `employee_role` |
| → Crews ⚠️ *nuevo* | `menu.menuCrew` | `crew` |

---

### 6. Admin
| Ítem | Variable backend | Ruta |
|---|---|---|
| Profiles | `menu.menuRol` | `rol` |
| Users | `menu.menuUsuario` | `users` |
| Announcements *(antes: Advertisements)* | `menu.menuAdvertisement` | `advertisement` |

---

### 7. User Settings *(top right — fuera del sidebar)*
Estos ítems se removieron del menú lateral y deben implementarse en el área top-right del layout:

| Ítem | Variable backend | Nota |
|---|---|---|
| Reminders | `menu.menuReminder` | Bell Icon |
| Notifications | `menu.menuNotification` | Bell Icon |
| Logs | `menu.menuLog` | User dropdown |

---

## Pendientes de Backend

Los siguientes módulos son **nuevos** y aún no tienen `funcion_id` en `Base.php::DevolverMenu()`. Sus variables se usan en el Twig con `is defined` para que no rompan hasta que se implementen:

| Variable Twig | Descripción | Prioridad |
|---|---|---|
| `menu.menuContract` | Módulo de Contratos (Project Onboarding) | Alta |
| `menu.menuCertifiedPayroll` | Certified Payrolls (HR Reports) | Media |
| `menu.menuCrew` | Crews library (HR Libraries) | Media |

Para activar cada uno, agregar en `src/Utils/Base.php::DevolverMenu()`:
1. Inicializar variable: `$menuContract = false;`
2. Agregar bloque `if ($permiso['funcion_id'] == XX && $permiso['ver']) { $menuContract = true; }`
3. Incluir en el array de retorno: `'menuContract' => $menuContract`

---

## Ítems Eliminados del Sidebar

Removidos de la navegación lateral (no del sistema):

| Ítem | Motivo |
|---|---|
| Plan Downloading (`menuPlanDownloading`) | No incluido en la nueva estructura |
| Tasks (`menuTasks`) | Pasa a ser widget del Home |
| Reminders (`menuReminder`) | Pasa a top-right (Bell Icon) |
| Notifications (`menuNotification`) | Pasa a top-right (Bell Icon) |
| Logs (`menuLog`) | Pasa a top-right (User dropdown) |
