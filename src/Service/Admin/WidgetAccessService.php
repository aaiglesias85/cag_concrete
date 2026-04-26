<?php

namespace App\Service\Admin;

use App\Entity\Usuario;
use App\Repository\RolWidgetAccessRepository;
use App\Repository\UserWidgetAccessRepository;
use App\Repository\WidgetRepository;
use App\Entity\RolWidgetAccess;
use App\Entity\UserWidgetAccess;
use App\Entity\Widget;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Home + My Widgets: el estado mostrado es el de `user_widget_access` (is_enabled).
 * Si el usuario aún no tiene filas, se copian los valores del rol una sola vez (ver ensureUserWidgetAccessSeededFromRolIfEmpty).
 */
final class WidgetAccessService
{
    public function __construct(
        private readonly WidgetRepository $widgetRepository,
        private readonly RolWidgetAccessRepository $rolWidgetAccessRepository,
        private readonly UserWidgetAccessRepository $userWidgetAccessRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function isWidgetEnabledForUser(int $userId, string $code): bool
    {
        $widget = $this->widgetRepository->findOneByCode($code);
        if ($widget === null) {
            return false;
        }

        $wId = (int) $widget->getWidgetId();
        $userMap = $this->userWidgetAccessRepository->getEnabledMapByUserId($userId);

        return (bool) ($userMap[$wId] ?? false);
    }

    /**
     * Si el usuario no tiene filas en user_widget_access, rellenar una vez desde el rol
     * (misma matriz completa) para no exigir `rol_widget_access` en runtime.
     */
    public function ensureUserWidgetAccessSeededFromRolIfEmpty(int $userId): void
    {
        $rolId = $this->getRolIdForUser($userId);
        if ($rolId === null) {
            return;
        }
        $this->copyRolWidgetsToUserIfEmpty($userId, $rolId);
    }

    /**
     * Fila en rol_widget_access o en user_widget_access (legacy: antes filtraba "My Widgets");
     * la pantalla pública ahora lista todo el catálogo.
     */
    public function isWidgetInMyWidgetsScope(int $userId, string $code): bool
    {
        $widget = $this->widgetRepository->findOneByCode($code);
        if ($widget === null) {
            return false;
        }
        $rolId = $this->getRolIdForUser($userId);
        if ($rolId === null) {
            return false;
        }
        $wId = (int) $widget->getWidgetId();
        $userM = $this->userWidgetAccessRepository->getEnabledMapByUserId($userId);
        if (array_key_exists($wId, $userM)) {
            return true;
        }
        $rolW = $this->rolWidgetAccessRepository->getEnabledMapByRolId($rolId);

        return (bool) ($rolW[$wId] ?? false);
    }

    /**
     * Toggle en My Widgets: escribe en user_widget_access para cualquier widget del catálogo
     * (código existente y usuario con rol asignado).
     */
    public function setUserWidgetFromMyWidgetsPage(int $userId, string $code, bool $enabled): void
    {
        if ($this->getRolIdForUser($userId) === null) {
            throw new \InvalidArgumentException('User has no profile');
        }
        $widget = $this->widgetRepository->findOneByCode($code);
        if ($widget === null) {
            throw new \InvalidArgumentException('Unknown widget code');
        }
        $this->userWidgetAccessRepository->setEnabledByUserIdAndWidgetId(
            $userId,
            (int) $widget->getWidgetId(),
            $enabled
        );
    }

    /**
     * Flags de tarjetas en Home (no controlan el menú: eso es user_permission por function_id).
     *
     * @return array<string, bool>
     */
    public function getLayoutWidgetFlagsForUser(int $userId): array
    {
        $this->ensureUserWidgetAccessSeededFromRolIfEmpty($userId);
        $map = [
            'widgetTasks' => false,
            'widgetWorkSchedule' => false,
            'widgetBidDeadlines' => false,
            'widgetEstimateWinLoss' => false,
            'widgetEstimatesSubmitted' => false,
            'widgetEstimatorShare' => false,
            'widgetCurrentMonthProjects' => false,
            'widgetInvoicedProjects' => false,
            'widgetPayItemTotals' => false,
            'widgetInvoiceProfitShare' => false,
            'widgetJobCostBreakdown' => false,
        ];
        $codeToKey = self::codeToLayoutKeys();
        foreach ($this->widgetRepository->findAllOrdered() as $w) {
            $code = $w->getCode();
            $enabled = $this->isWidgetEnabledForUser($userId, $code);
            if (!isset($codeToKey[$code])) {
                continue;
            }
            foreach ($codeToKey[$code] as $key) {
                $map[$key] = $enabled;
            }
        }

        return $map;
    }

    /**
     * @return list<array{widget_id: int, code: string, title: string, is_enabled: bool}>
     */
    public function getWidgetStatesForRol(int $rolId): array
    {
        $rolW = $this->rolWidgetAccessRepository->getEnabledMapByRolId($rolId);
        $out = [];
        foreach ($this->widgetRepository->findAllOrdered() as $w) {
            $wid = (int) $w->getWidgetId();
            $out[] = [
                'widget_id' => $wid,
                'code' => $w->getCode(),
                'title' => $w->getTitle(),
                'is_enabled' => $rolW[$wid] ?? false,
            ];
        }

        return $out;
    }

    /**
     * @return list<array{widget_id: int, code: string, title: string, is_enabled: bool}>
     */
    public function getWidgetStatesForUserForm(int $userId): array
    {
        $this->ensureUserWidgetAccessSeededFromRolIfEmpty($userId);
        $userM = $this->userWidgetAccessRepository->getEnabledMapByUserId($userId);

        $out = [];
        foreach ($this->widgetRepository->findAllOrdered() as $w) {
            $wid = (int) $w->getWidgetId();
            $out[] = [
                'widget_id' => $wid,
                'code' => $w->getCode(),
                'title' => $w->getTitle(),
                'is_enabled' => (bool) ($userM[$wid] ?? false),
            ];
        }

        return $out;
    }

    /**
     * Sustituye filas de `rol_widget_access` para un rol.
     *
     * @param list<\stdClass>|list<array<string, mixed>> $rows [{widget_id, is_enabled}, ...]
     */
    public function replaceRolWidgets(int $rolId, array $rows): void
    {
        $rol = $this->em->getReference(\App\Entity\Rol::class, $rolId);
        $this->rolWidgetAccessRepository->deleteByRolId($rolId);

        $byId = [];
        foreach ($rows as $r) {
            $wid = (int) (is_array($r) ? $r['widget_id'] : $r->widget_id);
            $byId[$wid] = (bool) (is_array($r) ? $r['is_enabled'] : $r->is_enabled);
        }

        foreach ($this->widgetRepository->findAllOrdered() as $w) {
            $wid = (int) $w->getWidgetId();
            $on = $byId[$wid] ?? false;
            $e = new RolWidgetAccess();
            $e->setRol($rol);
            $e->setWidget($w);
            $e->setEnabled($on);
            $this->em->persist($e);
        }
        $this->em->flush();
    }

    /**
     * Sustituye toda la matriz de acceso a widgets del usuario.
     *
     * @param list<\stdClass>|list<array<string, mixed>> $rows
     */
    public function replaceUserWidgetAccess(int $userId, array $rows): void
    {
        $u = $this->em->getReference(Usuario::class, $userId);
        $this->userWidgetAccessRepository->deleteByUserId($userId);

        $byId = [];
        foreach ($rows as $r) {
            $wid = (int) (is_array($r) ? $r['widget_id'] : $r->widget_id);
            $byId[$wid] = (bool) (is_array($r) ? $r['is_enabled'] : $r->is_enabled);
        }

        foreach ($this->widgetRepository->findAllOrdered() as $w) {
            $wid = (int) $w->getWidgetId();
            $on = $byId[$wid] ?? false;
            $e = new UserWidgetAccess();
            $e->setUsuario($u);
            $e->setWidget($w);
            $e->setEnabled($on);
            $this->em->persist($e);
        }
        $this->em->flush();
    }

    public function copyRolWidgetsToUserIfEmpty(int $userId, int $rolId): void
    {
        $existing = $this->userWidgetAccessRepository->getEnabledMapByUserId($userId);
        if (count($existing) > 0) {
            return;
        }
        $rolRows = $this->getWidgetStatesForRol($rolId);
        $rows = [];
        foreach ($rolRows as $row) {
            $rows[] = [
                'widget_id' => $row['widget_id'],
                'is_enabled' => $row['is_enabled'],
            ];
        }
        $this->replaceUserWidgetAccess($userId, $rows);
    }

    private function getRolIdForUser(int $userId): ?int
    {
        $user = $this->em->find(Usuario::class, $userId);
        if ($user === null || $user->getRol() === null) {
            return null;
        }

        return $user->getRol()->getRolId();
    }

    /**
     * @return array<string, list<string>>
     */
    private static function codeToLayoutKeys(): array
    {
        return [
            'tasks' => ['widgetTasks'],
            'work_schedule' => ['widgetWorkSchedule'],
            'bid_deadlines' => ['widgetBidDeadlines'],
            'estimate_win_loss' => ['widgetEstimateWinLoss'],
            'estimates_submitted_totals' => ['widgetEstimatesSubmitted'],
            'estimator_submitted_share' => ['widgetEstimatorShare'],
            'current_month_data_tracking' => ['widgetCurrentMonthProjects'],
            'invoiced_projects' => ['widgetInvoicedProjects'],
            'pay_item_totals' => ['widgetPayItemTotals'],
            'invoice_profit_share' => ['widgetInvoiceProfitShare'],
            'job_cost_breakdown' => ['widgetJobCostBreakdown'],
        ];
    }
}
