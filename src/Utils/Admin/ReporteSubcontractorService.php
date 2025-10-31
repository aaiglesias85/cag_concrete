<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingSubcontract;
use App\Repository\DataTrackingSubcontractRepository;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReporteSubcontractorService extends Base
{

   /**
    * DevolverTotal: devuelve el total
    * @param $search
    * @param $subcontractor_id
    * @param $project_id
    * @param $project_item_id
    * @param $fecha_inicial
    * @param $fecha_fin
    * @return bool|float|int|string|null
    */
   public function DevolverTotal($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin)
   {
      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $total = $dataTrackingSubcontractRepo->DevolverTotalReporteSubcontractors($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);

      $total = number_format($total, 2, '.', ',');

      return $total;
   }

   /**
    * ExportarExcel: Exporta a excel el invoice
    *
    *
    * @author Marcel
    */
   public function ExportarExcel($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin)
   {
      //Configurar excel
      Cell::setValueBinder(new AdvancedValueBinder());

      $styleArray = array(
         'borders' => array(
            'outline' => array(
               'borderStyle' => Border::BORDER_THIN
            ),
         ),
      );

      // reader
      $reader = IOFactory::createReader('Xlsx');
      $objPHPExcel = $reader->load("bundles/metronic8/excel" . DIRECTORY_SEPARATOR . 'report-subcontractor.xlsx');
      $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

      $fila = 10;
      $total = 0;
      $total_qty = 0;
      $total_price = 0;

      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $lista = $dataTrackingSubcontractRepo->ListarReporteSubcontractorsParaExcel($search, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);
      foreach ($lista as $value) {


         $date = $value->getDataTracking()->getDate()->format('m/d/Y');
         $objWorksheet->setCellValueExplicit('A' . $fila, $date, DataType::TYPE_STRING);

         $subcontractor = $value->getSubcontractor() ? $value->getSubcontractor()->getName() : "";
         $project = $value->getDataTracking()->getProject()->getProjectNumber() . " - " . $value->getDataTracking()->getProject()->getDescription();
         $item = $value->getProjectItem()->getItem()->getDescription();
         $unit = $value->getProjectItem()->getItem()->getUnit()->getDescription();

         $quantity = $value->getQuantity();
         $total_qty += $quantity;

         $price = $value->getPrice();
         $total_price += $price;

         $subtotal = $quantity * $price;
         $total += $subtotal;

         $objWorksheet
            ->setCellValue('B' . $fila, $subcontractor)
            ->setCellValue('C' . $fila, $project)
            ->setCellValue('D' . $fila, $item)
            ->setCellValue('E' . $fila, $unit)
            ->setCellValue('F' . $fila, $quantity)
            ->setCellValue('G' . $fila, $price)
            ->setCellValue('H' . $fila, $subtotal);

         $objWorksheet->getStyle('A' . $fila . ':A' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('B' . $fila . ':B' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('C' . $fila . ':C' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('D' . $fila . ':D' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('E' . $fila . ':E' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('F' . $fila . ':F' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('G' . $fila . ':G' . $fila)->applyFromArray($styleArray);
         $objWorksheet->getStyle('H' . $fila . ':H' . $fila)->applyFromArray($styleArray);

         $fila++;
      }

      // total
      $fila++;
      $objWorksheet
         ->setCellValue('E' . $fila, "Total")
         ->setCellValue('F' . $fila, $total_qty)
         ->setCellValue('G' . $fila, $total_price)
         ->setCellValue('H' . $fila, $total);

      $objWorksheet->getStyle('E' . $fila . ':E' . $fila)->applyFromArray($styleArray);
      $objWorksheet->getStyle('F' . $fila . ':F' . $fila)->applyFromArray($styleArray);
      $objWorksheet->getStyle('G' . $fila . ':G' . $fila)->applyFromArray($styleArray);
      $objWorksheet->getStyle('H' . $fila . ':H' . $fila)->applyFromArray($styleArray);

      //Salvar excel
      $fichero = "report-subcontractor.xlsx";

      $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
      $objWriter->save("uploads" . DIRECTORY_SEPARATOR . "excel" . DIRECTORY_SEPARATOR . $fichero);
      $objPHPExcel->disconnectWorksheets();
      unset($objPHPExcel);

      $ruta = $this->ObtenerURL();
      $dir = 'uploads/excel/' . $fichero;
      $url = $ruta . $dir;

      return $url;
   }

   /**
    * ListarReporteSubcontractors: Listar los reporte subcontractors
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarReporteSubcontractors(
      $start,
      $limit,
      $sSearch,
      $iSortCol_0,
      $sSortDir_0,
      $subcontractor_id,
      $project_id,
      $project_item_id,
      $fecha_inicial,
      $fecha_fin
   ) {
      /** @var DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
      $dataTrackingSubcontractRepo = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class);
      $resultado = $dataTrackingSubcontractRepo->ListarReporteSubcontractorsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id, $project_id, $project_item_id, $fecha_inicial, $fecha_fin);

      $data = [];

      foreach ($resultado['data'] as $value) {

         $quantity = $value->getQuantity();
         $price = $value->getPrice();
         $total = $quantity * $price;

         $data[] = [
            "id" => $value->getId(),
            'subcontractor' => $value->getSubcontractor() ? $value->getSubcontractor()->getName() : "",
            'project' => $value->getDataTracking()->getProject()->getProjectNumber() . " - " . $value->getDataTracking()->getProject()->getDescription(),
            'date' => $value->getDataTracking()->getDate()->format('m/d/Y'),
            'item' => $value->getProjectItem()->getItem()->getDescription(),
            'unit' => $value->getProjectItem()->getItem()->getUnit()->getDescription(),
            "quantity" => $quantity,
            "price" => $price,
            "total" => $total,
            "notes" => $value->getNotes(),
         ];
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }
}
