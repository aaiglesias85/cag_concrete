<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\DataTracking;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Utils\Base;

class DefaultService extends Base
{

    /*
     * FiltrarDashboard
     */
    public function FiltrarDashboard($project_id, $status, $fecha_inicial, $fecha_fin)
    {

        //last 6 projects
        $projects = $this->ListarProjectsParaDashboard( $project_id,$fecha_inicial, $fecha_fin, 'DESC', 6);

        $stats = $this->ListarStats($fecha_inicial, $fecha_fin);

        $chart_costs = $this->DevolverDataChartCosts($project_id, $fecha_inicial, $fecha_fin, $status);
        $chart_profit = $this->DevolverDataChartProfit($project_id, $fecha_inicial, $fecha_fin, $status);
        $chart3 = $this->DevolverDataChart3($project_id, $fecha_inicial, $fecha_fin, $status);

        $items = $this->ListarItemsConMontos($project_id, $fecha_inicial, $fecha_fin, $status);

        $materials = $this->ListarMaterialsConMontos($project_id, $fecha_inicial, $fecha_fin, $status);

        return [
            'projects' => $projects,
            'stats' => $stats,
            'chart_costs' => $chart_costs,
            'chart_profit' => $chart_profit,
            'chart3' => $chart3,
            'items' => $items,
            'materials' => $materials,
        ];
    }

    /**
     * ListarProjectsParaDashboard: lista los projects ordenados por el due date
     * @return array
     */
    public function ListarProjectsParaDashboard($project_id = '', $from = '', $to = '', $sort = 'ASC', $limit = '')
    {
        $arreglo_resultado = [];

        $lista = $this->getDoctrine()->getRepository(Project::class)
            ->ListarProjectsParaDashboard($from, $to, $sort, $limit, $project_id);
        foreach ($lista as $value) {
            $project_id = $value->getProjectId();

            $arreglo_resultado[] = [
                'project_id' => $project_id,
                'number' => $value->getProjectNumber(),
                'name' => $value->getName(),
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
        // total de proyectos In Progress
        $total_proyectos_activos = $this->getDoctrine()->getRepository(Project::class)
            ->TotalProjects('', '', '', 1, $from, $to);

        // total de proyectos Not Started
        $total_proyectos_inactivos = $this->getDoctrine()->getRepository(Project::class)
            ->TotalProjects('', '', '', 0, $from, $to);

        // total de proyectos Completed
        $total_proyectos_completed = $this->getDoctrine()->getRepository(Project::class)
            ->TotalProjects('', '', '', 2, $from, $to);

        return [
            'total_proyectos_activos' => $total_proyectos_activos,
            'total_proyectos_inactivos' => $total_proyectos_inactivos,
            'total_proyectos_completed' => $total_proyectos_completed
        ];
    }

    /**
     * ListarMaterialsConMontos: lista los materials ordenados por el monto
     * @return array
     */
    public function ListarMaterialsConMontos($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $arreglo_resultado = [];

        $materials = $this->getDoctrine()->getRepository(Material::class)->findAll();
        /** @var Material $material */
        foreach ($materials as $material) {
            $material_id = $material->getMaterialId();

            $quantity = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
                ->TotalQuantity('', $project_id, $material_id, $fecha_inicial, $fecha_fin, $status);

            $amount = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
                ->TotalMaterials('', $material_id, $project_id, $fecha_inicial, $fecha_fin, $status);

            if ($quantity > 0) {
                $arreglo_resultado[] = [
                    'material_id' => $material_id,
                    'name' => $material->getName(),
                    'unit' => $material->getUnit()->getDescription(),
                    'quantity' => $quantity,
                    'amount' => $amount
                ];
            }
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
        $arreglo_resultado = [];

        $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->ListarItemsDeProject($project_id);
        foreach ($project_items as $project_item) {
            $project_item_id = $project_item->getId();

            $quantity = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalQuantity('', $project_item_id, $fecha_inicial, $fecha_fin, $status );

            $amount = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalDaily('', $project_item_id, '', $fecha_inicial, $fecha_fin, $status);

            if ($quantity > 0) {
                $arreglo_resultado[] = [
                    'item_id' => $project_item_id,
                    'name' => $project_item->getItem()->getDescription(),
                    'unit' => $project_item->getItem()->getUnit()->getDescription(),
                    'quantity' => $quantity,
                    'amount' => $amount
                ];
            }
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
        $total = $this->getDoctrine()->getRepository(InvoiceItem::class)->TotalInvoice('', '', '', $fecha_inicial, $fecha_fin, '', '');

        // invoices
        $data = [];

        $invoices = $this->getDoctrine()->getRepository(Invoice::class)
            ->ListarInvoicesRangoFecha('', $project_id, $fecha_inicial, $fecha_fin, $status);
        foreach ($invoices as $invoice) {

            $amount = $this->getDoctrine()->getRepository(InvoiceItem::class)
                ->TotalInvoice($invoice->getInvoiceId());

            $porciento = $total > 0 ? round($amount / $total * 100) : 0;

            $data[] = [
                'name' => 'Invoice #' . $invoice->getNumber(). ", Project: #".$invoice->getProject()->getProjectNumber(),
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
        $amount_daily = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->TotalDaily('', '', $project_id, $fecha_inicial, $fecha_fin, $status);
        $porciento_daily = $total > 0 ? round($amount_daily / $total * 100) : 0;

        $data[] = [
            'name' => 'Invoiced',
            'amount' => $amount_daily,
            'porciento' => $porciento_daily
        ];

        // profit
        $amount_profit = $this->CalcularProfitTotal($project_id, $fecha_inicial, $fecha_fin, $status);;
        $porciento_profit = $total > 0 ? round($amount_profit / $total * 100) : 0;

        $data[] = [
            'name' => 'Profit',
            'amount' => $amount_profit,
            'porciento' => $porciento_profit
        ];

        return [
            'total' => $total,
            'data' => $data
        ];
    }

    private function CalcularProfitTotal($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $profit = 0;

        $projects = $this->ListarProjectsParaDashboard($project_id);
        foreach ($projects as $project) {
            $project_id = $project['project_id'];

            $total_daily_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalDaily('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

            $total_subcontract = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class)
                ->TotalPrice('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

            $total_daily_today = $total_daily_today - $total_subcontract;


            // concrete used price
            $total_concrete = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                ->TotalConcPrice('', $project_id, $fecha_inicial, $fecha_fin, $status);


            $total_labor = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                ->TotalLabor('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

            $total_material = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
                ->TotalMaterials('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

            $total_overhead = $this->getDoctrine()->getRepository(DataTracking::class)
                ->TotalOverhead('', $project_id, $fecha_inicial, $fecha_fin, $status);

            // "Labor Total" is the sum of Labor and Overhead Totals
            $total_labor = $total_labor + $total_overhead;

            $profit += $total_daily_today - ($total_concrete + $total_labor + $total_material);
        }


        return $profit;
    }

    /**
     * DevolverDataChartCosts: devuelve la data para el grafico
     * @return array
     */
    public function DevolverDataChartCosts($project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        // concrete used price
        $total_concrete = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
            ->TotalConcPrice('', $project_id, $fecha_inicial, $fecha_fin, $status);

        $total_labor = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->TotalLabor('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

        $total_material = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
            ->TotalMaterials('', '', $project_id, $fecha_inicial, $fecha_fin, $status);

        $total_overhead = $this->getDoctrine()->getRepository(DataTracking::class)
            ->TotalOverhead('', $project_id, $fecha_inicial, $fecha_fin, $status);

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
            'porciento' => $porciento_concrete
        ];

        // labor
        $porciento_labor = $total > 0 ? round($total_labor / $total * 100) : 0;

        $data[] = [
            'name' => 'Labor',
            'amount' => $total_labor,
            'porciento' => $porciento_labor
        ];

        // material
        $porciento_material = $total > 0 ? round($total_material / $total * 100) : 0;

        $data[] = [
            'name' => 'Materials',
            'amount' => $total_material,
            'porciento' => $porciento_material
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

        $projects = $this->getDoctrine()->getRepository(Project::class)
            ->ListarOrdenados();
        foreach ($projects as $project) {
            $project_id = $project->getProjectId();

            $amount = $this->getDoctrine()->getRepository(InvoiceItem::class)
                ->TotalInvoice("", "", $project_id);
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

}