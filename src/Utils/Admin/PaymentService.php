<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceNotes;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;

use App\Entity\InvoiceAttachment;
use App\Utils\Base;

class PaymentService extends Base
{

    /**
     * EliminarArchivos: Elimina varios archivos en la BD
     *
     * @param $archivos
     * @return array
     */
    public function EliminarArchivos($archivos)
    {
        $resultado = array();

        $archivos = explode(',', $archivos);
        foreach ($archivos as $archivo) {
            //Eliminar archivo
            $dir = 'uploads/invoice/';
            if (is_file($dir . $archivo)) {
                unlink($dir . $archivo);
            }

            $em = $this->getDoctrine()->getManager();

            $archivo_entity = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
                ->findOneBy(array('file' => $archivo));
            if ($archivo_entity != null) {
                $em->remove($archivo_entity);
            }
        }

        $em->flush();

        $resultado['success'] = true;
        return $resultado;
    }

    /**
     * EliminarArchivo: Elimina un archivo en la BD
     *
     * @param $archivo
     * @return array
     */
    public function EliminarArchivo($archivo)
    {
        $resultado = array();

        //Eliminar archivo
        $dir = 'uploads/invoice/';
        if (is_file($dir . $archivo)) {
            unlink($dir . $archivo);
        }

        $em = $this->getDoctrine()->getManager();

        $archivo_entity = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
            ->findOneBy(array('file' => $archivo));
        if ($archivo_entity != null) {
            $em->remove($archivo_entity);
        }

        $em->flush();

        $resultado['success'] = true;
        return $resultado;
    }

    /**
     * EliminarNotes: Elimina un notes en la BD
     * @param int $notes_id Id
     * @author Marcel
     */
    public function EliminarNotes($notes_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(InvoiceNotes::class)
            ->find($notes_id);
        /**@var InvoiceNotes $entity */
        if ($entity != null) {
            $notes = $entity->getNotes();
            $invoice_number = $entity->getInvoice()->getNumber();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Invoice Notes";
            $log_descripcion = "The notes: $notes is delete from invoice #: $invoice_number";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarNotesDate: Elimina un notes en un rango de fechas en la BD
     * @param int $invoice_id Id
     * @author Marcel
     */
    public function EliminarNotesDate($invoice_id, $from, $to)
    {
        $em = $this->getDoctrine()->getManager();

        $invoice_entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $invoice_entity */
        if ($invoice_entity != null) {

            $invoice_number = $invoice_entity->getNumber();


            $notes = $this->getDoctrine()->getRepository(InvoiceNotes::class)
                ->ListarNotesDeInvoice($invoice_id, $from, $to);
            foreach ($notes as $entity) {
                $em->remove($entity);
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Invoice Notes";
            $log_descripcion = "The notes $from and $to is delete from invoice #: $invoice_number";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosNotes: Carga los datos de un notes
     *
     * @param int $notes_id Id
     *
     * @author Marcel
     */
    public function CargarDatosNotes($notes_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(InvoiceNotes::class)
            ->find($notes_id);
        /** @var InvoiceNotes $entity */
        if ($entity != null) {

            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['date'] = $entity->getDate()->format('m/d/Y');

            $resultado['success'] = true;
            $resultado['notes'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * SalvarNotes
     * @param $notes_id
     * @param $invoice_id
     * @param $notes
     * @param $date
     * @return array
     */
    public function SalvarNotes($notes_id, $invoice_id, $notes, $date)
    {

        $em = $this->getDoctrine()->getManager();

        $invoice_entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $invoice_entity */
        if ($invoice_entity != null) {

            $project_entity = $invoice_entity->getProject();
            $invoice_number = $invoice_entity->getNumber();

            $entity = null;
            $is_new = false;

            if (is_numeric($notes_id)) {
                $entity = $this->getDoctrine()->getRepository(InvoiceNotes::class)
                    ->find($notes_id);
            }

            if ($entity == null) {
                $entity = new InvoiceNotes();
                $is_new = true;
            }

            $entity->setNotes($notes);

            if ($date != '') {
                $date = \DateTime::createFromFormat('m/d/Y', $date);
                $entity->setDate($date);
            }

            $entity->setInvoice($invoice_entity);

            $log_operacion = "Add";
            $log_descripcion = "Notes '$notes' have been added to invoice #$invoice_number (Project: {$project_entity->getName()})";

            if ($is_new) {
                $em->persist($entity);
            } else {
                $log_operacion = "Update";
                $log_descripcion = "Notes '$notes' have been updated to invoice #$invoice_number (Project: {$project_entity->getName()})";
            }

            $em->flush();

            //Salvar log
            $log_categoria = "Invoice Notes";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The project not exist.";
        }

        return $resultado;

    }

    /**
     * ListarNotes: Listar los notes
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin)
    {
        $resultado = $this->getDoctrine()->getRepository(InvoiceNotes::class)
            ->ListarNotesConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $notes_id = $value->getId();

            $notes = $value->getNotes();
            $notes = mb_convert_encoding($notes, 'UTF-8', 'UTF-8');

            $data[] = array(
                "id" => $notes_id,
                "notes" => $notes,
                "date" => $value->getDate()->format('m/d/Y'),
            );
        }

        return [
            'data' => $data,
            'total' => $resultado['total'],
        ];
    }

    /**
     * CargarDatosPayment: Carga los datos de un invoice
     *
     * @param int $invoice_id Id
     *
     * @author Marcel
     */
    public function CargarDatosPayment($invoice_id)
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

            // archivos
            $archivos = $this->ListarArchivosDeInvoice($invoice_id);
            $arreglo_resultado['archivos'] = $archivos;

            $resultado['success'] = true;
            $resultado['invoice'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarArchivosDeInvoice
     * @param $invoice_id
     * @return array
     */
    public function ListarArchivosDeInvoice($invoice_id)
    {
        $archivos = [];

        $project_archivos = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
            ->ListarAttachmentsDeInvoice($invoice_id);
        foreach ($project_archivos as $key => $project_archivo) {
            $archivos[] = [
                'id' => $project_archivo->getId(),
                'name' => $project_archivo->getName(),
                'file' => $project_archivo->getFile(),
                'posicion' => $key
            ];
        }

        return $archivos;
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
     * ListarPaymentsDeInvoice
     * @param $invoice_id
     * @return array
     */
    public function ListarPaymentsDeInvoice($invoice_id)
    {
        $payments = [];

        $lista = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarItems($invoice_id);
        foreach ($lista as $key => $value) {

            $contract_qty = $value->getProjectItem()->getQuantity();
            $price = $value->getPrice();
            $contract_amount = $contract_qty * $price;

            $quantity_from_previous = $value->getQuantityFromPrevious();
            $unpaid_from_previous = $value->getUnpaidFromPrevious();

            $quantity = $value->getQuantity() + $value->getUnpaidFromPrevious();

            $quantity_completed = $quantity + $quantity_from_previous;

            $amount = $quantity * $price;

            $total_amount = $quantity_completed * $price;

            // payment
            $paid_qty = $value->getPaidQty();
            $paid_amount = $value->getPaidAmount();
            $paid_amount_total = $value->getPaidAmountTotal();

            $unpaid_qty = $quantity - $paid_qty;

            $payments[] = [
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
                "paid_qty" => $paid_qty,
                "paid_amount" => $paid_amount,
                "paid_amount_total" => $paid_amount_total,
                "unpaid_qty" => $unpaid_qty,
                "posicion" => $key
            ];
        }

        return $payments;
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
     * ActualizarPayment: Actuializa los datos del rol en la BD
     * @param int $invoice_id Id
     * @author Marcel
     */
    public function ActualizarPayment($invoice_id, $payments, $archivos)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Invoice::class)
            ->find($invoice_id);
        /** @var Invoice $entity */
        if ($entity != null) {
            
            $entity->setUpdatedAt(new \DateTime());

            // items
            $this->SalvarPayments($entity, $payments);

            // save archivos
            $this->SalvarArchivos($entity, $archivos);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Invoice";

            $number = $entity->getNumber();
            $log_descripcion = "The invoice #$number is modified";

            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarArchivos
     * @param $archivos
     * @param Project $entity
     * @return void
     */
    public function SalvarArchivos($entity, $archivos)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($archivos as $value) {

            $archivo_entity = null;

            if (is_numeric($value->id)) {
                $archivo_entity = $this->getDoctrine()->getRepository(InvoiceAttachment::class)
                    ->find($value->id);
            }

            $is_new_archivo = false;
            if ($archivo_entity == null) {
                $archivo_entity = new InvoiceAttachment();
                $is_new_archivo = true;
            }

            $archivo_entity->setName($value->name);
            $archivo_entity->setFile($value->file);

            if ($is_new_archivo) {
                $archivo_entity->setInvoice($entity);

                $em->persist($archivo_entity);
            }
        }
    }

    /**
     * SalvarPayments
     * @param array $payments
     * @param Invoice $entity
     * @return void
     */
    public function SalvarPayments($entity, $payments)
    {
        $invoice_id = $entity->getInvoiceId();

        //items
        $paid = true;
        foreach ($payments as $value) {

            $invoice_item_entity = $this->getDoctrine()->getRepository(InvoiceItem::class)
                ->BuscarItem($invoice_id, $value->project_item_id);
            if ($invoice_item_entity != null) {
                // payment
                $invoice_item_entity->setPaidQty($value->paid_qty);
                $invoice_item_entity->setPaidAmount($value->paid_amount);
                $invoice_item_entity->setPaidAmountTotal($value->paid_amount_total);
            }

            // si falta alguno no pago el invoice
            if ($value->paid_qty == 0 || $value->paid_amount == 0 || $value->paid_amount_total == 0) {
                $paid = false;
            }
        }

        // paid invoice
        if (!empty($payments) && !$entity->getPaid()) {
            $entity->setPaid($paid);
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
    public function ListarInvoices($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin, $paid)
    {
        $resultado = $this->getDoctrine()->getRepository(Invoice::class)
            ->ListarInvoicesParaPaymentsConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $project_id, $fecha_inicial, $fecha_fin, $paid);

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