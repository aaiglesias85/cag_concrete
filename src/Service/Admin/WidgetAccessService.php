<?php

namespace App\Service\Admin;

use App\Entity\RolWidgetAccess;
use App\Entity\UserWidgetAccess;
use App\Entity\Usuario;
use App\Entity\Widget;
use App\Repository\RolWidgetAccessRepository;
use App\Repository\UserPreferenceWidgetRepository;
use App\Repository\UserWidgetAccessRepository;
use App\Repository\WidgetRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * `user_widget_access`: asignación administrativa (qué widgets puede usar el usuario).
 * `user_preference_widget`: qué el usuario elige mostrar en el Home entre los permitidos.
 * Si el usuario aún no tiene filas en acceso, se copian los valores del rol una sola vez
 * (ensureUserWidgetAccessSeededFromRolIfEmpty).
 */
final class WidgetAccessService
{
    public function __construct(
        private readonly WidgetRepository $widgetRepository,
        private readonly RolWidgetAccessRepository $rolWidgetAccessRepository,
        private readonly UserWidgetAccessRepository $userWidgetAccessRepository,
        private readonly UserPreferenceWidgetRepository $userPreferenceWidgetRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Catálogo/admin: el widget está permitido para el usuario (`user_widget_access.is_enabled`).
     */
    public function isWidgetEnabledForUser(int $userId, string $code): bool
    {
        $widget = $this->widgetRepository->findOneByCode($code);
        if (null === $widget) {
            return false;
        }

        $wId = (int) $widget->getWidgetId();
        $userMap = $this->userWidgetAccessRepository->getEnabledMapByUserId($userId);

        return (bool) ($userMap[$wId] ?? false);
    }

    /**
     * Home/dashboard: permitido por admin y visible en preferencia (sin fila de preferencia ⇒ visible).
     */
    public function isWidgetVisibleOnHome(int $userId, string $code): bool
    {
        if (!$this->isWidgetEnabledForUser($userId, $code)) {
            return false;
        }
        $widget = $this->widgetRepository->findOneByCode($code);
        if (null === $widget) {
            return false;
        }
        $wId = (int) $widget->getWidgetId();
        $prefMap = $this->userPreferenceWidgetRepository->getVisibleMapByUserId($userId);
        if (!\array_key_exists($wId, $prefMap)) {
            return true;
        }

        return (bool) $prefMap[$wId];
    }

    /**
     * Si el usuario no tiene filas en user_widget_access, rellenar una vez desde el rol
     * (misma matriz completa) para no exigir `rol_widget_access` en runtime.
     */
    public function ensureUserWidgetAccessSeededFromRolIfEmpty(int $userId): void
    {
        $rolId = $this->getRolIdForUser($userId);
        if (null === $rolId) {
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
        if (null === $widget) {
            return false;
        }
        $rolId = $this->getRolIdForUser($userId);
        if (null === $rolId) {
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
     * Toggle en My Widgets: escribe visibilidad en `user_preference_widget` (solo widgets permitidos en acceso).
     */
    public function setUserWidgetFromMyWidgetsPage(int $userId, string $code, bool $visible): void
    {
        if (null === $this->getRolIdForUser($userId)) {
            throw new \InvalidArgumentException('User has no profile');
        }
        $widget = $this->widgetRepository->findOneByCode($code);
        if (null === $widget) {
            throw new \InvalidArgumentException('Unknown widget code');
        }
        if (!$this->isWidgetEnabledForUser($userId, $code)) {
            throw new \InvalidArgumentException('Widget is not enabled for your account');
        }
        $this->userPreferenceWidgetRepository->setVisibleByUserIdAndWidgetId(
            $userId,
            (int) $widget->getWidgetId(),
            $visible
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
            $enabled = $this->isWidgetVisibleOnHome($userId, $code);
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
        $this->syncUserPreferenceWithAccessMatrix($userId);
    }

    /**
     * Tras guardar la matriz de `user_widget_access`: elimina preferencias de widgets revocados;
     * para widgets permitidos sin fila en preferencia, inserta visible=true.
     */
    public function syncUserPreferenceWithAccessMatrix(int $userId): void
    {
        $accessMap = $this->userWidgetAccessRepository->getEnabledMapByUserId($userId);
        $prefMap = $this->userPreferenceWidgetRepository->getVisibleMapByUserId($userId);
        foreach ($this->widgetRepository->findAllOrdered() as $w) {
            $wid = (int) $w->getWidgetId();
            $allowed = (bool) ($accessMap[$wid] ?? false);
            if (!$allowed) {
                $this->userPreferenceWidgetRepository->deleteByUserIdAndWidgetId($userId, $wid);
                unset($prefMap[$wid]);

                continue;
            }
            if (!\array_key_exists($wid, $prefMap)) {
                $this->userPreferenceWidgetRepository->setVisibleByUserIdAndWidgetId($userId, $wid, true);
                $prefMap[$wid] = true;
            }
        }
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
        if (null === $user || null === $user->getRol()) {
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
