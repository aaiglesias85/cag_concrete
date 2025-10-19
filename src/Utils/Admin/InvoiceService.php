<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;

use App\Entity\ProjectItem;
use App\Entity\SyncQueueQbwc;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls\Style\CellAlignment;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InvoiceService extends Base
{

    /**
     * ChangeNumber: Cambiar el number de un invoice
     * @param int $invoice_id Id
     * @author Marcel
     */
    public function ChangeNumber($invoice_id, $number)
    {
        $resultado = array();
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $entity */
        if (!is_null($entity)) {

            $project_id = $entity->getProject()->getProjectId();

            // verificar number
            $invoice = $this->getDoctrine()->getRepository(Invoice::class)
                ->findOneBy(['number' => $number, 'project' => $project_id]);
            if ($invoice != null && $invoice->getInvoiceId() != $entity->getInvoiceId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The invoice number is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setNumber($number);

            $em->flush();

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }
        return $resultado;
    }

    /**
     * PaidInvoice: Paga un invoice
     * @param int $invoice_id Id
     * @author Marcel
     */
    public function PaidInvoice($invoice_id)
    {
        $resultado = array();
        $em = $this->getDoctrine()->getManager();

        $invoice = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $invoice */
        if (!is_null($invoice)) {

            $invoice->setPaid(!$invoice->getPaid());

            $em->flush();

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }
        return $resultado;
    }

    /**
     * ExportarExcel: Exporta a excel el invoice
     *
     *
     * @author Marcel
     */
    public function ExportarExcel($invoice_id)
    {
        // Configurar excel
        Cell::setValueBinder(new AdvancedValueBinder());

        // === Estilo de bordes (4 lados) ===
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'], // negro
                ],
            ],
        ];

        // Reader
        $reader = IOFactory::createReader('Xlsx');
        $objPHPExcel = $reader->load("bundles/metronic8/excel" . DIRECTORY_SEPARATOR . 'invoice.xlsx');
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

        // Datos del invoice
        $invoice_entity = $this->getDoctrine()->getRepository(Invoice::class)->find($invoice_id);
        /** @var Invoice $invoice_entity */
        $number = $invoice_entity->getNumber();

        // Totales
        $total_contract_amount = 0;
        $total_amount_invoice_todate = 0;
        $total_unpaid = 0;
        $total_amount_measured = 0;
        $total_amount_final = 0;

        $fila_inicio = 6;
        $fila = $fila_inicio;

        $items = $this->getDoctrine()->getRepository(InvoiceItem::class)->ListarItems($invoice_id);

        foreach ($items as $key => $value) {

            $contract_qty = $value->getProjectItem()->getQuantity();
            $price = $value->getPrice();
            $contract_amount = $contract_qty * $price;
            $total_contract_amount += $contract_amount;

            $quantity_from_previous = $value->getQuantityFromPrevious();
            $quantity = $value->getQuantity();
            $quantity_completed = $quantity + $quantity_from_previous;

            $amount = $quantity * $price;
            $total_amount_invoice_todate += $amount;

            $amount_from_previous = $quantity_from_previous * $price;
            $total_amount_measured += $amount_from_previous;

            $amount_completed = $quantity_completed * $price;
            $total_amount_final += $amount_completed;

            $paid_qty = $value->getPaidQty();
            $unpaid_qty = $value->getUnpaidQty() ?? ($quantity - $paid_qty);
            $amount_unpaid = $unpaid_qty * $price;
            $total_unpaid += $amount_unpaid;

            // Escribir fila
            $objWorksheet
                ->setCellValue('A' . $fila, ($key + 1))
                ->setCellValue('B' . $fila, $value->getProjectItem()->getItem()->getDescription())
                ->setCellValue('E' . $fila, $value->getProjectItem()->getItem()->getUnit()->getDescription())
                ->setCellValue('F' . $fila, $price)
                ->setCellValue('G' . $fila, $contract_amount)
                ->setCellValue('H' . $fila, $quantity)
                ->setCellValue('I' . $fila, $amount)
                ->setCellValue('J' . $fila, $unpaid_qty)
                ->setCellValue('K' . $fila, $amount_unpaid)
                ->setCellValue('L' . $fila, $quantity_from_previous)
                ->setCellValue('M' . $fila, $amount_from_previous)
                ->setCellValue('N' . $fila, "")
                ->setCellValue('O' . $fila, $quantity_completed)
                ->setCellValue('P' . $fila, $amount_completed);

            // Aplicar bordes a toda la fila (A–P)
            $objWorksheet->getStyle("A{$fila}:P{$fila}")->applyFromArray($styleArray);

            $fila++;
        }

        // Totales
        $objWorksheet->setCellValue("F$fila", "TOTAL")->getStyle("F$fila")->getFont()->setBold(true);
        $objWorksheet->setCellValue("G$fila", $total_contract_amount)->getStyle("G$fila")->getFont()->setBold(true);
        $objWorksheet->setCellValue("I$fila", $total_amount_invoice_todate)->getStyle("I$fila")->getFont()->setBold(true);
        $objWorksheet->setCellValue("K$fila", $total_unpaid)->getStyle("K$fila")->getFont()->setBold(true);
        $objWorksheet->setCellValue("M$fila", $total_amount_measured)->getStyle("M$fila")->getFont()->setBold(true);
        $objWorksheet->setCellValue("P$fila", $total_amount_final)->getStyle("P$fila")->getFont()->setBold(true);

        // Bordes de la fila total
        $objWorksheet->getStyle("A{$fila}:P{$fila}")->applyFromArray($styleArray);

        /* ===========================================
         * COLORES DE FONDO (desde fila 6 hasta totales)
         * =========================================== */
        $lastRow = $fila;

        // H–I (azul claro)
        $objWorksheet->getStyle("H{$fila_inicio}:I{$lastRow}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFDAEEF3');

        // J–K (rojo claro)
        $objWorksheet->getStyle("J{$fila_inicio}:K{$lastRow}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF79494');

        // L–M (naranja suave)
        $objWorksheet->getStyle("L{$fila_inicio}:M{$lastRow}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFCD5B4');

        // N (amarillo suave)
        $objWorksheet->getStyle("N{$fila_inicio}:N{$lastRow}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF2D068');

        // O–P (verde claro)
        $objWorksheet->getStyle("O{$fila_inicio}:P{$lastRow}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD8E4BC');

        // Guardar Excel
        $fichero = "invoice-$number.xlsx";
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->save("uploads" . DIRECTORY_SEPARATOR . "invoice" . DIRECTORY_SEPARATOR . $fichero);

        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);

        $ruta = $this->ObtenerURL();
        $url = $ruta . 'uploads/invoice/' . $fichero;

        return $url;
    }



    /**
     * EliminarItem: Elimina un item en la BD
     * @param int $invoice_item_id Id
     * @author Marcel
     */
    public function EliminarItem($invoice_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->find($invoice_item_id);
        /**@var InvoiceItem $entity */
        if ($entity != null) {

            $item_name = $entity->getItem()->getDescription();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Item Invoice";
            $log_descripcion = "The item details invoice is deleted: $item_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosInvoice: Carga los datos de un invoice
     *
     * @param int $invoice_id Id
     *
     * @author Marcel
     */
    public function CargarDatosInvoice($invoice_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $entity */
        if ($entity != null) {

            $arreglo_resultado['project_id'] = $entity->getProject()->getProjectId();

            $company_id = $entity->getProject()->getCompany()->getCompanyId();
            $arreglo_resultado['company_id'] = $company_id;

            $arreglo_resultado['number'] = $entity->getNumber();
            $arreglo_resultado['start_date'] = $entity->getStartDate()->format('m/d/Y');
            $arreglo_resultado['end_date'] = $entity->getEndDate()->format('m/d/Y');
            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['paid'] = $entity->getPaid();

            // projects
            $projects = $this->ListarProjectsDeCompany($company_id);
            $arreglo_resultado['projects'] = $projects;

            // items
            $items = $this->ListarItemsDeInvoice($invoice_id);
            $arreglo_resultado['items'] = $items;

            // payments
            $payments = $this->ListarPaymentsDeInvoice($invoice_id);
            $arreglo_resultado['payments'] = $payments;

            $resultado['success'] = true;
            $resultado['invoice'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarItemsDeInvoice
     * @param $invoice_id
     * @return array
     */
    public function ListarItemsDeInvoice($invoice_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarItems($invoice_id);
        foreach ($lista as $key => $value) {

            $contract_qty = $value->getProjectItem()->getQuantity();
            $price = $value->getPrice();
            $contract_amount = $contract_qty * $price;

            $quantity_from_previous = $value->getQuantityFromPrevious();
            $unpaid_from_previous = $value->getUnpaidFromPrevious();

            $quantity = $value->getQuantity();

            $quantity_completed = $quantity + $quantity_from_previous;

            $amount = $quantity * $price;

            $total_amount = $quantity_completed * $price;

            $items[] = [
                "invoice_item_id" => $value->getId(),
                "project_item_id" => $value->getProjectItem()->getId(),
                "item_id" => $value->getProjectItem()->getItem()->getItemId(),
                "item" => $value->getProjectItem()->getItem()->getDescription(),
                "unit" => $value->getProjectItem()->getItem()->getUnit()->getDescription(),
                "contract_qty" => $contract_qty,
                "price" => $price,
                "contract_amount" => $contract_amount,
                "quantity_from_previous" => $quantity_from_previous,
                "unpaid_from_previous" => $unpaid_from_previous,
                "quantity" => $quantity,
                "quantity_completed" => $quantity_completed,
                "amount" => $amount,
                "total_amount" => $total_amount,
                "principal" => $value->getProjectItem()->getPrincipal(),
                "posicion" => $key
            ];
        }

        return $items;
    }

    /**
     * ListarProjectsDeCompany
     * @param $company_id
     * @return array
     */
    public function ListarProjectsDeCompany($company_id)
    {
        $projects = [];

        $lista = $this->getDoctrine()->getRepository(Project::class)
            ->ListarOrdenados('', $company_id, '');
        foreach ($lista as $value) {
            $projects[] = [
                'project_id' => $value->getProjectId(),
                'number' => $value->getProjectNumber(),
                'name' => $value->getName(),
                'description' => $value->getDescription()
            ];
        }

        return $projects;
    }

    /**
     * EliminarInvoice: Elimina un rol en la BD
     * @param int $invoice_id Id
     * @author Marcel
     */
    public function EliminarInvoice($invoice_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /**@var Invoice $entity */
        if ($entity != null) {

            // eliminar informacion
            $this->EliminarInformacionDeInvoice($invoice_id);

            $number = $entity->getNumber();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Invoice";
            $log_descripcion = "The invoice #$number is deleted";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarInvoices: Elimina los invoices seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarInvoices($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $invoice_id) {
                if ($invoice_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Invoice::class)
                        ->find($invoice_id);
                    /**@var Invoice $entity */
                    if ($entity != null) {

                        // eliminar informacion
                        $this->EliminarInformacionDeInvoice($invoice_id);

                        $number = $entity->getNumber();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Invoice";
                        $log_descripcion = "The invoice #$number is deleted";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The invoices could not be deleted, because they are associated with a invoice";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected invoices because they are associated with a invoice";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarInvoice: Actuializa los datos del rol en la BD
     * @param int $invoice_id Id
     * @author Marcel
     */
    public function ActualizarInvoice($invoice_id, $number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $entity */
        if ($entity != null) {

            // verificar fechas
            $invoices = $this->getDoctrine()->getRepository(Invoice::class)
                ->ListarInvoicesRangoFecha('', $project_id, $start_date, $end_date);
            if (!empty($invoices) && $invoices[0]->getInvoiceId() != $entity->getInvoiceId()) {
                $resultado['success'] = false;
                $resultado['error'] = "An invoice already exists for that date range";
                return $resultado;
            }

            // verificar que la fecha inicial no sea mayor que la inicial
            if ($start_date != '') {
                $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
            }

            if ($end_date != '') {
                $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
            }

            if ($start_date && $end_date) {
                if ($start_date > $end_date) {
                    $resultado['success'] = false;
                    $resultado['error'] = "The start date cannot be greater than the end date.";
                    return $resultado;
                }
            } else {
                $resultado['success'] = false;
                $resultado['error'] = "Incorrect date format";
                return $resultado;
            }

            // verificar number
            $invoice = $this->getDoctrine()->getRepository(Invoice::class)
                ->findOneBy(['number' => $number, 'project' => $project_id]);
            if ($invoice != null && $invoice->getInvoiceId() != $entity->getInvoiceId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The invoice number is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setNumber($number);

            $entity->setStartDate($start_date);
            $entity->setEndDate($end_date);

            $entity->setNotes($notes);
            $entity->setPaid($paid);

            if ($project_id != '') {
                $project = $this->getDoctrine()->getRepository(Project::class)
                    ->find($project_id);
                $entity->setProject($project);
            }

            $entity->setUpdatedAt(new \DateTime());

            // items
            $this->SalvarItems($entity, $items);

            // salvar en la cola
            $this->SalvarInvoiceQuickbook($entity);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Invoice";

            $number = $entity->getNumber();
            $log_descripcion = "The invoice #$number is modified";

            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            // exportar
            $url = '';
            if ($exportar == 1) {
                $url = $this->ExportarExcel($invoice_id);
            }
            $resultado['url'] = $url;

            return $resultado;
        }
    }

    /**
     * SalvarInvoice: Guarda los datos de invoice en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarInvoice($number, $project_id, $start_date, $end_date, $notes, $paid, $items, $exportar)
    {
        $em = $this->getDoctrine()->getManager();

        // verificar fechas
        $invoices = $this->getDoctrine()->getRepository(Invoice::class)
            ->ListarInvoicesRangoFecha('', $project_id, $start_date, $end_date);
        if (!empty($invoices)) {
            $resultado['success'] = false;
            $resultado['error'] = "An invoice already exists for that date range";
            return $resultado;
        }

        // verificar que la fecha inicial no sea mayor que la inicial
        if ($start_date != '') {
            $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
        }

        if ($end_date != '') {
            $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
        }

        if ($start_date && $end_date) {
            if ($start_date > $end_date) {
                $resultado['success'] = false;
                $resultado['error'] = "The start date cannot be greater than the end date.";
                return $resultado;
            }
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "Incorrect date format";
            return $resultado;
        }

        // verificar number
        if ($number !== '') {
            $invoice = $this->getDoctrine()->getRepository(Invoice::class)
                ->findOneBy(['number' => $number, 'project' => $project_id]);
            if ($invoice != null) {
                $resultado['success'] = false;
                $resultado['error'] = "The invoice number is in use, please try entering another one.";
                return $resultado;
            }
        } else {
            // number
            $invoices = $this->getDoctrine()->getRepository(Invoice::class)->ListarInvoicesDeProject($project_id);
            $number = 1;
            if (!empty($invoices)) {
                $number = intval($invoices[0]->getNumber()) + 1;
            }
        }

        $entity = new Invoice();

        $entity->setNumber($number);

        $entity->setStartDate($start_date);
        $entity->setEndDate($end_date);

        $entity->setNotes($notes);
        $entity->setPaid($paid);

        if ($project_id != '') {
            $project = $this->getDoctrine()->getRepository(Project::class)
                ->find($project_id);
            $entity->setProject($project);
        }

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        // items
        $this->SalvarItems($entity, $items);

        $em->flush();

        // salvar en la cola
        $this->SalvarInvoiceQuickbook($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Invoice";
        $log_descripcion = "The invoice #$number is added";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        // exportar
        $url = '';
        if ($exportar == 1) {
            $invoice_id = $entity->getInvoiceId();
            $url = $this->ExportarExcel($invoice_id);
        }
        $resultado['url'] = $url;

        return $resultado;
    }

    /**
     * SalvarInvoiceQuickbook
     * @param Invoice $entity
     * @return void
     */
    public function SalvarInvoiceQuickbook($entity)
    {
        $em = $this->getDoctrine()->getManager();

        $invoice_id = $entity->getInvoiceId();

        $sync_queue_qbwc = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
            ->findOneBy(['tipo' => 'invoice', 'entidadId' => $invoice_id]);
        $is_new_sync_queue_qbwc = false;
        if ($sync_queue_qbwc == null) {
            $sync_queue_qbwc = new SyncQueueQbwc();
            $is_new_sync_queue_qbwc = true;
        }

        $sync_queue_qbwc->setEstado('pendiente');

        if ($is_new_sync_queue_qbwc) {
            $sync_queue_qbwc->setTipo('invoice');
            $sync_queue_qbwc->setEntidadId($invoice_id);
            $sync_queue_qbwc->setIntentos(0);

            $sync_queue_qbwc->setCreatedAt(new \DateTime());

            $em->persist($sync_queue_qbwc);
        }

    }

    /**
     * SalvarItems
     * @param array $items
     * @param Invoice $entity
     * @return void
     */
    public function SalvarItems($entity, $items)
    {
        $em = $this->getDoctrine()->getManager();

        //items

        foreach ($items as $value) {

            $invoice_item_entity = null;

            if (is_numeric($value->invoice_item_id)) {
                $invoice_item_entity = $this->getDoctrine()->getRepository(InvoiceItem::class)
                    ->find($value->invoice_item_id);
            }

            $is_new_item = false;
            if ($invoice_item_entity == null) {
                $invoice_item_entity = new InvoiceItem();
                $is_new_item = true;
            }

            $invoice_item_entity->setQuantityFromPrevious($value->quantity_from_previous);
            $invoice_item_entity->setUnpaidFromPrevious($value->unpaid_from_previous);
            $invoice_item_entity->setQuantity($value->quantity);
            $invoice_item_entity->setPrice($value->price);

            if ($value->project_item_id != '') {
                $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)->find($value->project_item_id);
                $invoice_item_entity->setProjectItem($project_item_entity);
            }


            if ($is_new_item) {
                $invoice_item_entity->setInvoice($entity);

                $em->persist($invoice_item_entity);
            }
        }
    }


    /**
     * ListarInvoices: Listar los invoices
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarInvoices($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin)
    {
        $resultado = $this->getDoctrine()->getRepository(Invoice::class)
            ->ListarInvoicesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $invoice_id = $value->getInvoiceId();

            $total = $this->getDoctrine()->getRepository(InvoiceItem::class)
                ->TotalInvoice($invoice_id);

            $data[] = array(
                "id" => $invoice_id,
                "number" => $value->getNumber(),
                "company" => $value->getProject()->getCompany()->getName(),
                "projectNumber" => $value->getProject()->getProjectNumber(),
                "project" => $value->getProject()->getDescription(),
                "startDate" => $value->getStartDate()->format('m/d/Y'),
                "endDate" => $value->getEndDate()->format('m/d/Y'),
                "notes" => $this->truncate($value->getNotes(), 50),
                "total" => $total,
                "createdAt" => $value->getCreatedAt()->format('m/d/Y'),
                "paid" => $value->getPaid() ? 1 : 0
            );
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}