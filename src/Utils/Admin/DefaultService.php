<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\DataTracking;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Estimate;
use App\Entity\EstimateEstimator;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Repository\DataTrackingConcVendorRepository;
use App\Repository\DataTrackingItemRepository;
use App\Repository\DataTrackingLaborRepository;
use App\Repository\DataTrackingMaterialRepository;
use App\Repository\DataTrackingRepository;
use App\Repository\DataTrackingSubcontractRepository;
use App\Repository\EstimateRepository;
use App\Repository\EstimateEstimatorRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;
use App\Repository\MaterialRepository;
use App\Repository\ProjectItemRepository;
use App\Repository\ProjectRepository;
use App\Utils\Base;

class DefaultService extends Base
{

   /*
     * FiltrarDashboard
     */
   public function FiltrarDashboard($project_id, $status, $fecha_inicial, $fecha_fin)
   {

      //last 6 projects
      $projects = $this->ListarProjectsParaDashboard($project_id, $fecha_inicial, $fecha_fin, 'DESC', 6);

      $stats = $this->ListarStats($fecha_inicial, $fecha_fin);

      $chart_costs = $this->DevolverDataChartCosts($project_id, $fecha_inicial, $fecha_fin, $status);
      $chart_profit = $this->DevolverDataChartProfit($project_id, $fecha_inicial, $fecha_fin, $status);
      // Módulo Estimate: los datos del listado/entidad no se filtran por "project" de obra; el filtro del dashboard de proyecto no aplica.
      $chart_estimate_win_loss = $this->DevolverDataChartEstimateWinLoss('', $fecha_inicial, $fecha_fin);
      $chart_estimates_submitted_totals = $this->DevolverDataChartEstimateSubmittedTotals('', $fecha_inicial, $fecha_fin);
      $chart_estimator_submitted_share = $this->DevolverDataChartEstimatorSubmittedShare('', $fecha_inicial, $fecha_fin);
      $invoiced_projects = $this->ListarInvoicedProjectsPayloadHome($project_id, $fecha_inicial, $fecha_fin);
      $chart3 = $this->DevolverDataChart3($project_id, $fecha_inicial, $fecha_fin, $status);

      $items = $this->ListarItemsConMontos($project_id, $fecha_inicial, $fecha_fin, $status);

      $materials = $this->ListarMaterialsConMontos($project_id, $fecha_inicial, $fecha_fin, $status);

      return [
         'projects' => $projects,
         'stats' => $stats,
         'chart_costs' => $chart_costs,
         'chart_profit' => $chart_profit,
         'chart_estimate_win_loss' => $chart_estimate_win_loss,
         'chart_estimates_submitted_totals' => $chart_estimates_submitted_totals,
         'chart_estimator_submitted_share' => $chart_estimator_submitted_share,
         'invoiced_projects' => $invoiced_projects,
         'chart3' => $chart3,
         'items' => $items,
         'materials' => $materials,
      ];
   }

   /**
    * Widget payload: invoiced projects in period (invoice number + date + amount total).
    *
    * @return list<array<string, mixed>>
    */
   public function ListarInvoicedProjectsPayloadHome($project_id = '', $fecha_inicial = '', $fecha_fin = ''): array
   {
      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $resultado = $invoiceRepo->ListarInvoicesConTotal(
         0,
         0,
         '',
         'startDate',
         'DESC',
         '',
         (string) $project_id,
         (string) $fecha_inicial,
         (string) $fecha_fin,
         ''
      );

      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $ids = [];
      foreach ($resultado['data'] as $invoice) {
         if ($invoice instanceof Invoice) {
            $ids[] = (int) $invoice->getInvoiceId();
         }
      }
      $amountsById = $invoiceItemRepo->mapTotalInvoiceFinalAmountThisPeriodByInvoiceIds($ids);

      $out = [];
      foreach ($resultado['data'] as $invoice) {
         if (!$invoice instanceof Invoice) {
            continue;
         }
         $invoiceId = (int) $invoice->getInvoiceId();
         $amount = (float) ($amountsById[$invoiceId] ?? 0);
         $invoiceNumber = (string) ($invoice->getNumber() ?? '');
         $invoiceDate = $invoice->getStartDate() ? $invoice->getStartDate()->format('m/d/Y') : '';

         $project = $invoice->getProject();
         $projectNumber = $project !== null ? (string) ($project->getProjectNumber() ?? '') : '';
         $projectDesc = $project !== null ? (string) ($project->getDescription() ?? '') : '';
         $projectLabel = trim($projectNumber !== '' ? ($projectNumber . ($projectDesc !== '' ? ' - ' . $projectDesc : '')) : $projectDesc);

         $out[] = [
            'id' => $invoiceId,
            'invoice_label' => trim($invoiceNumber . ($invoiceDate !== '' ? ' · ' . $invoiceDate : '')),
            'project_label' => $projectLabel,
            'amount_total' => $amount,
         ];
      }

      return $out;
   }

   private function ResolverProjectRefsParaEstimate($project_id): array
   {
      $projectNumber = '';
      $projectIdRaw = '';
      if ($project_id === null || $project_id === '') {
         return [$projectNumber, $projectIdRaw];
      }

      $projectIdRaw = (string) $project_id;
      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      $project = $projectRepo->find((int) $project_id);
      if ($project !== null) {
         $projectNumber = (string) ($project->getProjectNumber() ?? '');
      }

      return [$projectNumber, $projectIdRaw];
   }

   /**
    * Donut widget: Estimate win/loss ratio (awarded_date vs lost_date).
    */
   public function DevolverDataChartEstimateWinLoss($project_id = '', $fecha_inicial = '', $fecha_fin = ''): array
   {
      [$projectNumber, $projectIdRaw] = $this->ResolverProjectRefsParaEstimate($project_id);
      /** @var EstimateRepository $estimateRepo */
      $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);

      $won = $estimateRepo->CountByDateFieldPresenceForDashboard(
         'awardedDate',
         true,
         $projectNumber,
         $projectIdRaw,
         (string) $fecha_inicial,
         (string) $fecha_fin
      );
      $lost = $estimateRepo->CountByDateFieldPresenceForDashboard(
         'lostDate',
         true,
         $projectNumber,
         $projectIdRaw,
         (string) $fecha_inicial,
         (string) $fecha_fin
      );

      return [
         'total' => (int) ($won + $lost),
         'data' => [
            ['name' => 'Won', 'amount' => (int) $won, 'color' => '#50CD89'],
            ['name' => 'Lost', 'amount' => (int) $lost, 'color' => '#F1416C'],
         ],
      ];
   }

   /**
    * Donut widget: submitted vs not submitted by submitted_date.
    */
   public function DevolverDataChartEstimateSubmittedTotals($project_id = '', $fecha_inicial = '', $fecha_fin = ''): array
   {
      [$projectNumber, $projectIdRaw] = $this->ResolverProjectRefsParaEstimate($project_id);
      /** @var EstimateRepository $estimateRepo */
      $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);

      $submitted = $estimateRepo->CountByDateFieldPresenceForDashboard(
         'submittedDate',
         true,
         $projectNumber,
         $projectIdRaw,
         (string) $fecha_inicial,
         (string) $fecha_fin
      );
      $notSubmitted = $estimateRepo->CountByDateFieldPresenceForDashboard(
         'submittedDate',
         false,
         $projectNumber,
         $projectIdRaw,
         '',
         ''
      );

      return [
         'total' => (int) ($submitted + $notSubmitted),
         'data' => [
            ['name' => 'Submitted', 'amount' => (int) $submitted, 'color' => '#3699FF'],
            ['name' => 'Not submitted', 'amount' => (int) $notSubmitted, 'color' => '#E4E6EF'],
         ],
      ];
   }

   /**
    * Donut widget: porcentaje de propuestas enviadas por estimator.
    * El total base es la cantidad de estimates enviados (submitted_date != null).
    */
   public function DevolverDataChartEstimatorSubmittedShare($project_id = '', $fecha_inicial = '', $fecha_fin = ''): array
   {
      [$projectNumber, $projectIdRaw] = $this->ResolverProjectRefsParaEstimate($project_id);
      /** @var EstimateRepository $estimateRepo */
      $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);

      $submittedEstimates = $estimateRepo->ListarSubmittedEstimatesForDashboard(
         $projectNumber,
         $projectIdRaw,
         (string) $fecha_inicial,
         (string) $fecha_fin
      );

      $totalSubmitted = count($submittedEstimates);
      $acc = [];
      $palette = ['#3699FF', '#50CD89', '#7239EA', '#F1416C', '#FFC700', '#00A3FF', '#1BC5BD', '#8950FC'];
      $paletteIdx = 0;

      $eids = [];
      /** @var Estimate $e0 */
      foreach ($submittedEstimates as $e0) {
         $eids[] = (int) $e0->getEstimateId();
      }
      $allEe = $estimateEstimatorRepo->listarByEstimateIds($eids);
      $linksByEst = [];
      /** @var EstimateEstimator $eRow */
      foreach ($allEe as $eRow) {
         $est = $eRow->getEstimate();
         if ($est === null) {
            continue;
         }
         $eid = (int) $est->getEstimateId();
         if (!isset($linksByEst[$eid])) {
            $linksByEst[$eid] = [];
         }
         $linksByEst[$eid][] = $eRow;
      }

      /** @var Estimate $estimate */
      foreach ($submittedEstimates as $estimate) {
         $estimateId = (int) $estimate->getEstimateId();
         $links = $linksByEst[$estimateId] ?? [];

         if (count($links) === 0) {
            $k = 'unassigned';
            if (!isset($acc[$k])) {
               $acc[$k] = [
                  'name' => 'Unassigned',
                  'amount' => 0.0,
                  'color' => '#E4E6EF',
               ];
            }
            $acc[$k]['amount'] += 1.0;
            continue;
         }

         $share = 1.0 / count($links);
         /** @var EstimateEstimator $ee */
         foreach ($links as $ee) {
            $u = $ee->getUser();
            if ($u === null) {
               continue;
            }
            $uid = (int) $u->getUsuarioId();
            $key = 'u_' . $uid;
            if (!isset($acc[$key])) {
               $fullName = trim((string) ($u->getNombreCompleto() ?? ''));
               if ($fullName === '') {
                  $fullName = trim((string) (($u->getNombre() ?? '') . ' ' . ($u->getApellidos() ?? '')));
               }
               $acc[$key] = [
                  'name' => $fullName !== '' ? $fullName : ('Estimator #' . $uid),
                  'amount' => 0.0,
                  'color' => $palette[$paletteIdx % count($palette)],
               ];
               $paletteIdx++;
            }
            $acc[$key]['amount'] += $share;
         }
      }

      $items = array_values($acc);
      usort($items, static function (array $a, array $b): int {
         return (float) $b['amount'] <=> (float) $a['amount'];
      });

      foreach ($items as &$item) {
         $item['amount'] = round((float) $item['amount'], 2);
      }
      unset($item);

      return [
         'total' => (int) $totalSubmitted,
         'data' => $items,
      ];
   }

   /**
    * ListarProjectsParaDashboard: lista los projects ordenados por el due date
    * @return array
    */
   public function ListarProjectsParaDashboard($project_id = '', $from = '', $to = '', $sort = 'ASC', $limit = '')
   {
      $arreglo_resultado = [];

      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      $lista = $projectRepo->ListarProjectsParaDashboard($from, $to, $sort, $limit, $project_id);
      foreach ($lista as $value) {
         $project_id = $value->getProjectId();

         $arreglo_resultado[] = [
            'project_id' => $project_id,
            'number' => $value->getProjectNumber(),
            'name' => $value->getName(),
            'description' => $value->getDescription(),
            'dueDate' => $value->getDueDate() != '' ? $value->getDueDate()->format('m/d/Y') : ''
         ];
      }

      return $arreglo_resultado;
   }

   /**
    * ListarStats: listar stats
    * @return array
    */
   public function ListarStats($from = '', $to = '')
   {
      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      $stats = $projectRepo->ListarStats('', null, null, $from, $to);

      $total = (int)$stats['total'];

      $stats['porcentaje_proyectos_activos']   = 0;
      $stats['porcentaje_proyectos_inactivos'] = 0;
      $stats['porcentaje_proyectos_completed'] = 0;
      $stats['porcentaje_proyectos_canceled']  = 0;

      if ($total > 0) {
         $stats['porcentaje_proyectos_activos']   = round(($stats['total_proyectos_activos']   / $total) * 100, 2);
         $stats['porcentaje_proyectos_inactivos'] = round(($stats['total_proyectos_inactivos'] / $total) * 100, 2);
         $stats['porcentaje_proyectos_completed'] = round(($stats['total_proyectos_completed'] / $total) * 100, 2);
         $stats['porcentaje_proyectos_canceled']  = round(($stats['total_proyectos_canceled']  / $total) * 100, 2);
      }

      return $stats;
   }

   /**
    * ListarMaterialsConMontos: lista los materials ordenados por el monto
    * @return array
    */
   public function ListarMaterialsConMontos($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
      $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
      $aggregates = $dataTrackingMaterialRepo->aggregateTotalsByMaterialId(
         (string) $project_id,
         (string) $fecha_inicial,
         (string) $fecha_fin,
         (string) $status
      );
      if ($aggregates === []) {
         return [];
      }
      $materialIds = array_column($aggregates, 'material_id');
      /** @var MaterialRepository $materialRepo */
      $materialRepo = $this->getDoctrine()->getRepository(Material::class);
      $mats = $materialRepo->createQueryBuilder('m')
         ->leftJoin('m.unit', 'u')
         ->addSelect('u')
         ->andWhere('m.materialId IN (:ids)')
         ->setParameter('ids', $materialIds)
         ->getQuery()
         ->getResult();
      $byId = [];
      /** @var Material $mat */
      foreach ($mats as $mat) {
         $byId[$mat->getMaterialId()] = $mat;
      }

      $arreglo_resultado = [];
      foreach ($aggregates as $row) {
         $mid = (int) $row['material_id'];
         if (!isset($byId[$mid])) {
            continue;
         }
         $material = $byId[$mid];
         $arreglo_resultado[] = [
            'material_id' => $mid,
            'name' => $material->getName(),
            'unit' => $material->getUnit() != null ? $material->getUnit()->getDescription() : '',
            'quantity' => (float) $row['total_qty'],
            'amount' => (float) $row['total_amount'],
         ];
      }

      // ordenar
      $arreglo_resultado = $this->ordenarArrayDesc($arreglo_resultado, 'amount');

      // sacar los primeros 6
      /*if ($project_id == '') {
            $arreglo_resultado = array_slice($arreglo_resultado, 0, 6);
        }*/

      return $arreglo_resultado;
   }

   /**
    * ListarItemsConMontos: lista los items ordenados por el monto
    * @return array
    */
   public function ListarItemsConMontos($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $rows = $dataTrackingItemRepo->aggregatePayItemTotalsByProjectItem(
         (string) $project_id,
         (string) $fecha_inicial,
         (string) $fecha_fin,
         (string) $status
      );
      if ($rows === []) {
         return [];
      }
      $pids = array_column($rows, 'project_item_id');
      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
      $itemsLoaded = $projectItemRepo->createQueryBuilder('p_i')
         ->leftJoin('p_i.item', 'i')
         ->addSelect('i')
         ->leftJoin('i.unit', 'u')
         ->addSelect('u')
         ->andWhere('p_i.id IN (:ids)')
         ->setParameter('ids', $pids)
         ->getQuery()
         ->getResult();
      $byPi = [];
      foreach ($itemsLoaded as $project_item) {
         if ($project_item->getId() !== null) {
            $byPi[$project_item->getId()] = $project_item;
         }
      }

      $arreglo_resultado = [];
      foreach ($rows as $row) {
         $project_item_id = (int) $row['project_item_id'];
         if (!isset($byPi[$project_item_id])) {
            continue;
         }
         $project_item = $byPi[$project_item_id];
         $arreglo_resultado[] = [
            'item_id' => $project_item_id,
            'name' => $project_item->getItem()->getName(),
            'unit' => $project_item->getItem()->getUnit() != null ? $project_item->getItem()->getUnit()->getDescription() : '',
            'quantity' => (float) $row['total_qty'],
            'amount' => (float) $row['total_amount'],
         ];
      }

      // ordenar
      $arreglo_resultado = $this->ordenarArrayDesc($arreglo_resultado, 'amount');

      // sacar los primeros 6
      /*if ($project_id == '') {
            $arreglo_resultado = array_slice($arreglo_resultado, 0, 6);
        }*/

      return $arreglo_resultado;
   }

   /**
    * DevolverDataChart3: devuelve la data para el grafico
    * @return array
    */
   public function DevolverDataChart3($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      /*$anno = date('Y');
        $fecha_inicial =  $fecha_inicial == '' ? "01/01/$anno": $fecha_inicial;
        $fecha_final = $fecha_fin == '' ? "12/31/$anno": $fecha_fin;
        */

      // profit total
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $total = $invoiceItemRepo->TotalInvoice('', '', '', $fecha_inicial, $fecha_fin, '', '');

      // invoices
      $data = [];

      /** @var InvoiceRepository $invoiceRepo */
      $invoiceRepo = $this->getDoctrine()->getRepository(Invoice::class);
      $invoices = $invoiceRepo->ListarInvoicesRangoFecha('', $project_id, $fecha_inicial, $fecha_fin, $status);
      $invIds = [];
      foreach ($invoices as $inv) {
         if ($inv instanceof Invoice) {
            $invIds[] = (int) $inv->getInvoiceId();
         }
      }
      $amountsById = $invoiceItemRepo->mapTotalInvoiceLineAmountByInvoiceIds($invIds);
      foreach ($invoices as $invoice) {
         if (!$invoice instanceof Invoice) {
            continue;
         }
         $amount = (float) ($amountsById[(int) $invoice->getInvoiceId()] ?? 0);

         $porciento = $total > 0 ? round($amount / $total * 100) : 0;

         $pr = $invoice->getProject();
         $pn = $pr !== null ? (string) ($pr->getProjectNumber() ?? '') : '';

         $data[] = [
            'name' => 'Invoice #' . $invoice->getNumber() . ", Project: #" . $pn,
            'amount' => $amount,
            'porciento' => $porciento
         ];
      }

      return [
         'total' => $total,
         'data' => $data
      ];
   }

   /**
    * DevolverDataChartProfit: devuelve la data para el grafico
    * @return array
    */
   public function DevolverDataChartProfit($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      // profit total
      $total = $this->CalcularProfitTotal('', $fecha_inicial, $fecha_fin, $status);


      // projects
      $data = [];

      // daily
      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $amount_daily = $dataTrackingItemRepo->TotalDaily('', '', $project_id, $fecha_inicial, $fecha_fin, $status);
      $porciento_daily = $total > 0 ? round($amount_daily / $total * 100) : 0;

      $data[] = [
         'name' => 'Invoiced',
         'amount' => $amount_daily,
         'porciento' => $porciento_daily,
         'color' => '#17C653'
      ];

      // profit
      $amount_profit = $this->CalcularProfitTotal($project_id, $fecha_inicial, $fecha_fin, $status);;
      $porciento_profit = $total > 0 ? round($amount_profit / $total * 100) : 0;

      $data[] = [
         'name' => 'Profit',
         'amount' => $amount_profit,
         'porciento' => $porciento_profit,
         'color' => '#F6C000'
      ];

      return [
         'total' => $total,
         'data' => $data
      ];
   }

   private function CalcularProfitTotal($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      $pid = (string) $project_id;

      /** @var DataTrackingItemRepository $dataTrackingItemRepo */
      $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
      $totalDailyToday = $dataTrackingItemRepo->TotalDaily('', '', $pid, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $totalSubcontract = $dataTrackingSubcontractRepo->TotalPrice('', '', $pid, $fecha_inicial, $fecha_fin, $status);

      $totalDailyToday -= $totalSubcontract;

      /** @var DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
      $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
      $totalConcrete = $dataTrackingConcVendorRepo->TotalConcPrice('', $pid, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
      $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
      $totalLabor = $dataTrackingLaborRepo->TotalLabor('', '', $pid, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
      $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
      $totalMaterial = $dataTrackingMaterialRepo->TotalMaterials('', '', $pid, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingRepository $dataTrackingRepo */
      $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
      $totalOverhead = $dataTrackingRepo->TotalOverhead('', $pid, $fecha_inicial, $fecha_fin, $status);

      $totalLabor += $totalOverhead;

      return $totalDailyToday - ($totalConcrete + $totalLabor + $totalMaterial);
   }

   /**
    * DevolverDataChartCosts: devuelve la data para el grafico
    * @return array
    */
   public function DevolverDataChartCosts($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
   {
      // concrete used price
      /** @var DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
      $dataTrackingConcVendorRepo = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class);
      $total_concrete = $dataTrackingConcVendorRepo->TotalConcPrice('', $project_id, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingLaborRepository $dataTrackingLaborRepo */
      $dataTrackingLaborRepo = $this->getDoctrine()->getRepository(DataTrackingLabor::class);
      $total_labor = $dataTrackingLaborRepo->TotalLabor('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingMaterialRepository $dataTrackingMaterialRepo */
      $dataTrackingMaterialRepo = $this->getDoctrine()->getRepository(DataTrackingMaterial::class);
      $total_material = $dataTrackingMaterialRepo->TotalMaterials('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

      /** @var DataTrackingRepository $dataTrackingRepo */
      $dataTrackingRepo = $this->getDoctrine()->getRepository(DataTracking::class);
      $total_overhead = $dataTrackingRepo->TotalOverhead('', $project_id, $fecha_inicial, $fecha_fin, $status);

      // "Labor Total" is the sum of Labor and Overhead Totals
      $total_labor = $total_labor + $total_overhead;

      $total = $total_concrete + $total_labor + $total_material;


      // projects
      $data = [];

      // concrete
      $porciento_concrete = $total > 0 ? round($total_concrete / $total * 100) : 0;

      $data[] = [
         'name' => 'Concrete',
         'amount' => $total_concrete,
         'porciento' => $porciento_concrete,
         'color' => '#17C653'
      ];

      // labor
      $porciento_labor = $total > 0 ? round($total_labor / $total * 100) : 0;

      $data[] = [
         'name' => 'Labor',
         'amount' => $total_labor,
         'porciento' => $porciento_labor,
         'color' => '#F6C000'
      ];

      // material
      $porciento_material = $total > 0 ? round($total_material / $total * 100) : 0;

      $data[] = [
         'name' => 'Materials',
         'amount' => $total_material,
         'porciento' => $porciento_material,
         'color' => '#1B84FF'
      ];

      return [
         'total' => $total,
         'data' => $data
      ];
   }

   /**
    * ListarProyectosConMontos: lista los proyectos ordenados por el monto
    * @return array
    */
   public function ListarProyectosConMontos()
   {
      $arreglo_resultado = [];

      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      $projects = $projectRepo->ListarOrdenados();
      foreach ($projects as $project) {
         $project_id = $project->getProjectId();

         /** @var InvoiceItemRepository $invoiceItemRepo */
         $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
         $amount = $invoiceItemRepo->TotalInvoice("", "", $project_id);
         if ($amount > 0) {
            $arreglo_resultado[] = [
               'project_id' => $project_id,
               'number' => $project->getProjectNumber(),
               'name' => $project->getName(),
               'company' => $project->getCompany()->getName(),
               'amount' => $amount
            ];
         }
      }

      // ordenar
      $arreglo_resultado = $this->ordenarArrayDesc($arreglo_resultado, 'amount');
      // sacar los primeros 3
      $arreglo_resultado = array_slice($arreglo_resultado, 0, 3);

      return $arreglo_resultado;
   }

   /**
    * @return list<array<string, mixed>>
    */
   public function ObtenerWidgetsDashboardV3($usuarioId): array
   {
      $definiciones = [
         [
            'id' => 'tasks',
            'title' => 'Tasks',
            'description' => 'Your assigned work and due dates',
            'layout' => 'table',
            'columns' => ['Status', 'Description', 'Due date', 'Actions'],
            'links' => [
               ['route' => 'tasks', 'label' => 'Tasks', 'funcion_id' => 40],
            ],
         ],
         [
            'id' => 'work_schedule',
            'title' => 'Work Schedule',
            'description' => 'Weekly view of field operations and priorities.',
            'layout' => 'table',
            'columns' => ['Project #', 'Day', 'Priority'],
            'links' => [
               ['route' => 'schedule', 'label' => 'Open schedule', 'funcion_id' => 22],
            ],
         ],
         [
            'id' => 'bid_deadlines',
            'title' => 'Upcoming bid deadlines',
            'description' => 'Projects with critical proposal dates and assigned estimator.',
            'layout' => 'table',
            'columns' => ['Project', 'Bid deadline', 'Estimator'],
            'links' => [
               ['route' => 'estimate', 'label' => 'Estimates', 'funcion_id' => 29],
            ],
         ],
         [
            'id' => 'estimate_win_loss',
            'title' => 'Estimate win / loss ratio',
            'description' => 'Submitted estimates won vs. lost.',
            'layout' => 'placeholder',
            'placeholder_hint' => 'Donut chart: won vs. lost (coming soon)',
            'links' => [
               ['route' => 'estimate', 'label' => 'Estimates', 'funcion_id' => 29],
            ],
         ],
         [
            'id' => 'estimates_submitted_totals',
            'title' => 'Total estimates — submitted / not submitted',
            'description' => 'Count of submitted vs. draft or pending.',
            'layout' => 'table',
            'columns' => ['Category', 'Count'],
            'links' => [
               ['route' => 'estimate', 'label' => 'Estimates', 'funcion_id' => 29],
            ],
         ],
         [
            'id' => 'estimator_submitted_share',
            'title' => 'Estimator submitted share',
            'description' => 'Share of submitted proposals by estimator.',
            'layout' => 'placeholder',
            'placeholder_hint' => 'Donut or bar chart (coming soon)',
            'links' => [
               ['route' => 'estimate', 'label' => 'Estimates', 'funcion_id' => 29],
            ],
         ],
         [
            'id' => 'current_month_data_tracking',
            'title' => 'Current month projects (data tracking)',
            'description' => 'Aggregates for the current month from data tracking.',
            'layout' => 'table',
            'columns' => ['Date', 'Project #', 'Daily total', 'Profit total', 'Labor total', 'Concrete total'],
            'links' => [
               ['route' => 'data_tracking', 'label' => 'Data tracking', 'funcion_id' => 10],
            ],
         ],
         [
            'id' => 'pay_item_totals',
            'title' => 'Pay item totals (period)',
            'description' => 'Sums of pay item quantities and amounts; filter by project later.',
            'layout' => 'table',
            'columns' => ['Item', 'Quantity', 'Amount'],
            'links' => [
               ['route' => 'data_tracking', 'label' => 'Data tracking', 'funcion_id' => 10],
            ],
         ],
         [
            'id' => 'invoiced_projects',
            'title' => 'Invoiced projects (period)',
            'description' => 'Billed amount and quick glance of payment total.',
            'layout' => 'table',
            'columns' => ['Project', 'Invoice', 'Amount total'],
            'links' => [
               ['route' => 'invoice', 'label' => 'Invoices', 'funcion_id' => 11],
               ['route' => 'payment', 'label' => 'Payments', 'funcion_id' => 33],
            ],
         ],
         [
            'id' => 'invoice_profit_share',
            'title' => 'Invoice / profit share',
            'description' => 'Real profitability vs. invoiced amounts.',
            'layout' => 'table',
            'columns' => ['Label', 'Value'],
            'links' => [
               ['route' => 'invoice', 'label' => 'Invoices', 'funcion_id' => 11],
            ],
         ],
         [
            'id' => 'job_cost_breakdown',
            'title' => 'Job Cost Breakdown',
            'description' => 'Labor, materials, and other direct costs.',
            'layout' => 'table',
            'columns' => ['Category', 'Amount'],
            'links' => [
               ['route' => 'data_tracking', 'label' => 'Data tracking', 'funcion_id' => 10],
            ],
         ],
      ];

      $out = [];
      foreach ($definiciones as $def) {
         $linkViews = [];
         $widgetVisible = false;
         foreach ($def['links'] as $link) {
            $p = $this->BuscarPermiso($usuarioId, (int) $link['funcion_id']);
            $ok = is_array($p) && count($p) > 0 && !empty($p[0]['ver']);
            $linkViews[] = array_merge($link, ['canView' => $ok]);
            if ($ok) {
               $widgetVisible = true;
            }
         }
         if (!$widgetVisible) {
            continue;
         }
         $out[] = array_merge($def, [
            'links' => $linkViews,
            'canView' => true,
         ]);
      }

      return $out;
   }
}
