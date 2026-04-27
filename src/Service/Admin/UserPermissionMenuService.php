<?php

namespace App\Service\Admin;

use App\Constants\FunctionId;
use App\Entity\PermisoUsuario;
use Doctrine\Persistence\ManagerRegistry;

class UserPermissionMenuService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly WidgetAccessService $widgetAccessService,
    ) {
    }

    /**
     * @param int $usuario_id
     */
    public function ListarPermisosDeUsuario($usuario_id): array
    {
        $permisos = [];

        /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
        $permisoUsuarioRepo = $this->doctrine->getRepository(PermisoUsuario::class);
        $usuario_permisos = $permisoUsuarioRepo->ListarPermisosUsuario($usuario_id);
        foreach ($usuario_permisos as $permiso) {
            $ver = $permiso->getVer();
            $agregar = $permiso->getAgregar();
            $editar = $permiso->getEditar();
            $eliminar = $permiso->getEliminar();

            $permisos[] = [
                'permiso_id' => $permiso->getPermisoId(),
                'funcion_id' => $permiso->getFuncion()->getFuncionId(),
                'funcion_name' => $permiso->getFuncion()->getDescripcion(),
                'funcion_url' => $permiso->getFuncion()->getUrl(),
                'ver' => (1 == $ver) ? true : false,
                'agregar' => (1 == $agregar) ? true : false,
                'editar' => (1 == $editar) ? true : false,
                'eliminar' => (1 == $eliminar) ? true : false,
                'todos' => (1 == $ver && 1 == $agregar && 1 == $editar && 1 == $eliminar) ? true : false,
            ];
        }

        return $permisos;
    }

    public function DevolverMenu($usuario_id): array
    {
        $menuInicio = false;
        $menuRol = false;
        $menuUsuario = false;
        $menuLog = false;
        $menuUnit = false;
        $menuItem = false;
        $menuInspector = false;
        $menuCompany = false;
        $menuProject = false;
        $menuDataTracking = false;
        $menuInvoice = false;
        $menuNotification = false;
        $menuEquation = false;
        $menuEmployee = false;
        $menuMaterial = false;
        $menuOverhead = false;
        $menuAdvertisement = false;
        $menuSubcontractor = false;
        $menuReporteSubcontractor = false;
        $menuReporteEmployee = false;
        $menuConcreteVendor = false;
        $menuSchedule = false;
        $menuReminder = false;
        $menuProjectStage = false;
        $menuProjectType = false;
        $menuProposalType = false;
        $menuPlanStatus = false;
        $menuDistrict = false;
        $menuEstimate = false;
        $menuPlanDownloading = false;
        $menuHoliday = false;
        $menuTasks = false;
        $menuCounty = false;
        $menuPayment = false;
        $menuOverridePayment = false;
        $menuRace = false;
        $menuEmployeeRrhh = false;
        $menuConcreteClass = false;
        $menuEmployeeRole = false;
        $menuEstimateNoteItem = false;

        // widgets (capa 1 - permisos)
        $widgetTasks = false;
        $widgetWorkSchedule = false;
        $widgetBidDeadlines = false;
        $widgetEstimateWinLoss = false;
        $widgetEstimatesSubmitted = false;
        $widgetEstimatorShare = false;
        $widgetCurrentMonthProjects = false;
        $widgetInvoicedProjects = false;
        $widgetPayItemTotals = false;
        $widgetInvoiceProfitShare = false;
        $widgetJobCostBreakdown = false;

        $permisos = $this->ListarPermisosDeUsuario($usuario_id);
        foreach ($permisos as $permiso) {
            if (FunctionId::HOME == $permiso['funcion_id'] && $permiso['ver']) {
                $menuInicio = true;
            }
            if (FunctionId::ROL == $permiso['funcion_id'] && $permiso['ver']) {
                $menuRol = true;
            }
            if (FunctionId::USUARIO == $permiso['funcion_id'] && $permiso['ver']) {
                $menuUsuario = true;
            }
            if (FunctionId::LOG == $permiso['funcion_id'] && $permiso['ver']) {
                $menuLog = true;
            }
            if (FunctionId::UNIT == $permiso['funcion_id'] && $permiso['ver']) {
                $menuUnit = true;
            }
            if (FunctionId::ITEM == $permiso['funcion_id'] && $permiso['ver']) {
                $menuItem = true;
            }
            if (FunctionId::INSPECTOR == $permiso['funcion_id'] && $permiso['ver']) {
                $menuInspector = true;
            }
            if (FunctionId::COMPANY == $permiso['funcion_id'] && $permiso['ver']) {
                $menuCompany = true;
            }
            if (FunctionId::PROJECT == $permiso['funcion_id'] && $permiso['ver']) {
                $menuProject = true;
            }
            if (FunctionId::DATA_TRACKING == $permiso['funcion_id'] && $permiso['ver']) {
                $menuDataTracking = true;
            }
            if (FunctionId::INVOICE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuInvoice = true;
            }
            if (FunctionId::NOTIFICATION == $permiso['funcion_id'] && $permiso['ver']) {
                $menuNotification = true;
            }
            if (FunctionId::EQUATION == $permiso['funcion_id'] && $permiso['ver']) {
                $menuEquation = true;
            }
            if (FunctionId::EMPLOYEE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuEmployee = true;
            }
            if (FunctionId::MATERIAL == $permiso['funcion_id'] && $permiso['ver']) {
                $menuMaterial = true;
            }
            if (FunctionId::OVERHEAD == $permiso['funcion_id'] && $permiso['ver']) {
                $menuOverhead = true;
            }
            if (FunctionId::ADVERTISEMENT == $permiso['funcion_id'] && $permiso['ver']) {
                $menuAdvertisement = true;
            }
            if (FunctionId::SUBCONTRACTOR == $permiso['funcion_id'] && $permiso['ver']) {
                $menuSubcontractor = true;
            }
            if (FunctionId::REPORTE_SUBCONTRACTOR == $permiso['funcion_id'] && $permiso['ver']) {
                $menuReporteSubcontractor = true;
            }
            if (FunctionId::REPORTE_EMPLOYEE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuReporteEmployee = true;
            }
            if (FunctionId::CONCRETE_VENDOR == $permiso['funcion_id'] && $permiso['ver']) {
                $menuConcreteVendor = true;
            }
            if (FunctionId::SCHEDULE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuSchedule = true;
            }
            if (FunctionId::REMINDER == $permiso['funcion_id'] && $permiso['ver']) {
                $menuReminder = true;
            }
            if (FunctionId::PROJECT_STAGE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuProjectStage = true;
            }
            if (FunctionId::PROJECT_TYPE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuProjectType = true;
            }
            if (FunctionId::PROPOSAL_TYPE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuProposalType = true;
            }
            if (FunctionId::PLAN_STATUS == $permiso['funcion_id'] && $permiso['ver']) {
                $menuPlanStatus = true;
            }
            if (FunctionId::DISTRICT == $permiso['funcion_id'] && $permiso['ver']) {
                $menuDistrict = true;
            }
            if (FunctionId::ESTIMATE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuEstimate = true;
            }
            if (FunctionId::PLAN_DOWNLOADING == $permiso['funcion_id'] && $permiso['ver']) {
                $menuPlanDownloading = true;
            }
            if (FunctionId::HOLIDAY == $permiso['funcion_id'] && $permiso['ver']) {
                $menuHoliday = true;
            }
            if (FunctionId::COUNTY == $permiso['funcion_id'] && $permiso['ver']) {
                $menuCounty = true;
            }
            if (FunctionId::PAYMENT == $permiso['funcion_id'] && $permiso['ver']) {
                $menuPayment = true;
            }
            if (FunctionId::OVERRIDE_PAYMENT == $permiso['funcion_id'] && $permiso['ver']) {
                $menuOverridePayment = true;
            }
            if (FunctionId::RACE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuRace = true;
            }
            if (FunctionId::EMPLOYEE_RRHH == $permiso['funcion_id'] && $permiso['ver']) {
                $menuEmployeeRrhh = true;
            }
            if (FunctionId::CONCRETE_CLASS == $permiso['funcion_id'] && $permiso['ver']) {
                $menuConcreteClass = true;
            }
            if (FunctionId::EMPLOYEE_ROLE == $permiso['funcion_id'] && $permiso['ver']) {
                $menuEmployeeRole = true;
            }
            if (FunctionId::ESTIMATE_NOTE_ITEM == $permiso['funcion_id'] && $permiso['ver']) {
                $menuEstimateNoteItem = true;
            }
            if (FunctionId::TASKS == $permiso['funcion_id'] && $permiso['ver']) {
                $menuTasks = true;
            }
        }

        $wFlags = $this->widgetAccessService->getLayoutWidgetFlagsForUser($usuario_id);
        $widgetTasks = $wFlags['widgetTasks'];
        $widgetWorkSchedule = $wFlags['widgetWorkSchedule'];
        $widgetBidDeadlines = $wFlags['widgetBidDeadlines'];
        $widgetEstimateWinLoss = $wFlags['widgetEstimateWinLoss'];
        $widgetEstimatesSubmitted = $wFlags['widgetEstimatesSubmitted'];
        $widgetEstimatorShare = $wFlags['widgetEstimatorShare'];
        $widgetCurrentMonthProjects = $wFlags['widgetCurrentMonthProjects'];
        $widgetInvoicedProjects = $wFlags['widgetInvoicedProjects'];
        $widgetPayItemTotals = $wFlags['widgetPayItemTotals'];
        $widgetInvoiceProfitShare = $wFlags['widgetInvoiceProfitShare'];
        $widgetJobCostBreakdown = $wFlags['widgetJobCostBreakdown'];

        return [
            'menuInicio' => $menuInicio,
            'menuRol' => $menuRol,
            'menuUsuario' => $menuUsuario,
            'menuLog' => $menuLog,
            'menuUnit' => $menuUnit,
            'menuItem' => $menuItem,
            'menuInspector' => $menuInspector,
            'menuCompany' => $menuCompany,
            'menuProject' => $menuProject,
            'menuDataTracking' => $menuDataTracking,
            'menuInvoice' => $menuInvoice,
            'menuNotification' => $menuNotification,
            'menuEquation' => $menuEquation,
            'menuEmployee' => $menuEmployee,
            'menuMaterial' => $menuMaterial,
            'menuOverhead' => $menuOverhead,
            'menuAdvertisement' => $menuAdvertisement,
            'menuSubcontractor' => $menuSubcontractor,
            'menuReporteSubcontractor' => $menuReporteSubcontractor,
            'menuReporteEmployee' => $menuReporteEmployee,
            'menuConcreteVendor' => $menuConcreteVendor,
            'menuSchedule' => $menuSchedule,
            'menuReminder' => $menuReminder,
            'menuProjectStage' => $menuProjectStage,
            'menuProjectType' => $menuProjectType,
            'menuProposalType' => $menuProposalType,
            'menuPlanStatus' => $menuPlanStatus,
            'menuDistrict' => $menuDistrict,
            'menuEstimate' => $menuEstimate,
            'menuPlanDownloading' => $menuPlanDownloading,
            'menuHoliday' => $menuHoliday,
            'menuTasks' => $menuTasks,
            'menuCounty' => $menuCounty,
            'menuPayment' => $menuPayment,
            'menuOverridePayment' => $menuOverridePayment,
            'menuRace' => $menuRace,
            'menuEmployeeRrhh' => $menuEmployeeRrhh,
            'menuConcreteClass' => $menuConcreteClass,
            'menuEmployeeRole' => $menuEmployeeRole,
            'menuEstimateNoteItem' => $menuEstimateNoteItem,
            'widgetTasks' => $widgetTasks,
            'widgetWorkSchedule' => $widgetWorkSchedule,
            'widgetBidDeadlines' => $widgetBidDeadlines,
            'widgetEstimateWinLoss' => $widgetEstimateWinLoss,
            'widgetEstimatesSubmitted' => $widgetEstimatesSubmitted,
            'widgetEstimatorShare' => $widgetEstimatorShare,
            'widgetCurrentMonthProjects' => $widgetCurrentMonthProjects,
            'widgetInvoicedProjects' => $widgetInvoicedProjects,
            'widgetPayItemTotals' => $widgetPayItemTotals,
            'widgetInvoiceProfitShare' => $widgetInvoiceProfitShare,
            'widgetJobCostBreakdown' => $widgetJobCostBreakdown,
        ];
    }

    /**
     * @param int $usuario_id
     *
     * @return array<string, mixed>|null
     */
    public function DevolverPrimeraFuncionDeUsuario($usuario_id): ?array
    {
        $funcion = null;

        $permisos = $this->ListarPermisosDeUsuario($usuario_id);
        foreach ($permisos as $permiso) {
            if ($permiso['ver']) {
                $funcion = $permiso;
                break;
            }
        }

        return $funcion;
    }

    /**
     * @param int $usuario_id
     * @param int $funcion_id
     */
    public function BuscarPermiso($usuario_id, $funcion_id): array
    {
        $permisos = [];

        /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
        $permisoUsuarioRepo = $this->doctrine->getRepository(PermisoUsuario::class);
        $permiso = $permisoUsuarioRepo->BuscarPermisoUsuario($usuario_id, $funcion_id);
        if (null != $permiso) {
            $ver = $permiso->getVer();
            $agregar = $permiso->getAgregar();
            $editar = $permiso->getEditar();
            $eliminar = $permiso->getEliminar();

            $permisos[] = [
                'permiso_id' => $permiso->getPermisoId(),
                'funcion_id' => $permiso->getFuncion()->getFuncionId(),
                'ver' => (1 == $ver) ? true : false,
                'agregar' => (1 == $agregar) ? true : false,
                'editar' => (1 == $editar) ? true : false,
                'eliminar' => (1 == $eliminar) ? true : false,
                'todos' => (1 == $ver && 1 == $agregar && 1 == $editar && 1 == $eliminar) ? true : false,
            ];
        }

        return $permisos;
    }
}
