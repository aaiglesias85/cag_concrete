<?php

namespace App\Utils;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\SyncQueueQbwc;
use App\Entity\UserQbwcToken;
use App\Entity\Usuario;

class QbwcService extends Base
{

    // eliminar token
    public function EliminarToken($token)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $this->getDoctrine()->getRepository(UserQbwcToken::class)
            ->BuscarToken($token);
        if ($session !== null) {
            $em->remove($session);
            $em->flush();
        }
    }

    // actualizar el estado en la cola
    public function UpdateSyncQueueQbwc(string $xmlResponse): void
    {
        $xml = simplexml_load_string($xmlResponse);
        $xml->registerXPathNamespace('qb', 'http://developer.intuit.com/');

        // Definir los tipos y sus XPaths para las respuestas de QuickBooks
        $responseTypes = [
            'invoice' => '//InvoiceRet',
            // Puedes agregar más tipos aquí, ejemplo:
            // 'customer' => '//CustomerRet',
            // 'item' => '//ItemServiceRet',
        ];

        $em = $this->getDoctrine()->getManager();

        $this->writeLog("Recibido XML response: " . $xmlResponse);

        foreach ($responseTypes as $tipo => $xpath) {
            $nodes = $xml->xpath($xpath);
            if (!$nodes || count($nodes) === 0) {
                continue;
            }

            foreach ($nodes as $ret) {
                $txnId = (string)$ret->TxnID;
                $editSequence = (string)$ret->EditSequence;

                $this->writeLog("Procesando {$tipo}: TxnID={$txnId}, EditSequence={$editSequence}");

                // Buscar el próximo item en la cola con estado "enviado"
                $item = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
                    ->findOneBy(['tipo' => strtolower($tipo), 'estado' => 'enviado'], ['id' => 'ASC']);

                if ($item && $txnId && $editSequence) {
                    $item->setEstado('sincronizado');

                    // Actualizar la entidad vinculada con TxnID y EditSequence
                    $entityClass = match ($tipo) {
                        'invoice' => Invoice::class,
                        default => null,
                    };

                    if ($entityClass) {
                        $entity = $this->getDoctrine()->getRepository($entityClass)->find($item->getEntidadId());
                        if ($entity !== null) {
                            $entity->setTxnId($txnId);
                            $entity->setEditSequence($editSequence);
                        }
                    }
                }
            }
        }

        // Guardar todos los cambios en la base de datos
        $em->flush();
    }

    // generar el xml del request
    public function GenerarRequestQBXML(): string
    {
        $qbxml = "";

        $items = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
            ->ListarOrdenados('pendiente');

        if (!empty($items)) {
            $item = $items[0];
            $tipo = $item->getTipo();
            $entidadId = $item->getEntidadId();

            switch ($tipo) {
                case 'invoice':
                    $this->writeLog("Generando XML para tipo: {$tipo} ID: {$entidadId}");

                    $qbxml = $this->generateInvoiceQBXML($entidadId);

                    $this->writeLog("XML generado: " . $qbxml);
                    break;
                // puedes agregar más tipos en el futuro aquí
            }

            // actualizar estado si se generó correctamente
            if ($qbxml !== '') {
                $item->setEstado('enviado');
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return $qbxml;
    }

    // invoice qbxml
    private function generateInvoiceQBXML(int $invoiceId): string
    {
        $invoice = $this->getDoctrine()->getRepository(Invoice::class)->find($invoiceId);
        if (!$invoice) return '';

        // Si ya tiene TxnID y EditSequence → es modificación
        if ($invoice->getTxnId() && $invoice->getEditSequence() && $invoice->getUpdatedAt() > $invoice->getCreatedAt()) {
            return $this->generateInvoiceModQBXML($invoice);
        }

        return $this->generateInvoiceAddQBXML($invoice);
    }
    private function generateInvoiceAddQBXML(Invoice $invoice): string
    {
        $project = $invoice->getProject();
        $company = $project->getCompany();
        $companyName = trim($company?->getName() ?? '');

        $xml = new \SimpleXMLElement('<QBXML></QBXML>');
        $msgsRq = $xml->addChild('QBXMLMsgsRq');
        $msgsRq->addAttribute('onError', 'stopOnError');
        $invoiceAddRq = $msgsRq->addChild('InvoiceAddRq');
        $invoiceAdd = $invoiceAddRq->addChild('InvoiceAdd');

        $invoiceAdd->addChild('CustomerRef')->addChild('FullName', htmlspecialchars($companyName));
        $invoiceAdd->addChild('TxnDate', $invoice->getStartDate()->format('Y-m-d'));
        $invoiceAdd->addChild('RefNumber', htmlspecialchars($invoice->getNumber()));

        if ($invoice->getNotes()) {
            $invoiceAdd->addChild('Memo', htmlspecialchars($invoice->getNotes()));
        }

        if ($company->getAddress()) {
            $billAddress = $invoiceAdd->addChild('BillAddress');
            $billAddress->addChild('Addr1', htmlspecialchars($company->getAddress()));
        }

        $invoiceItems = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarItems($invoice->getInvoiceId());

        foreach ($invoiceItems as $item) {
            $projectItem = $item->getProjectItem();
            $itemName = trim($projectItem->getItem()->getDescription());

            $line = $invoiceAdd->addChild('InvoiceLineAdd');
            $line->addChild('ItemRef')->addChild('FullName', htmlspecialchars($itemName));
            $line->addChild('Desc', 'Detalle generado desde sistema');
            $line->addChild('Quantity', number_format($item->getQuantity(), 2, '.', ''));
            $line->addChild('Rate', number_format($item->getPrice(), 2, '.', ''));
        }

        return "<?xml version=\"1.0\"?>\n" . $xml->asXML();
    }
    private function generateInvoiceModQBXML(Invoice $invoice): string
    {
        $project = $invoice->getProject();
        $company = $project->getCompany();
        $companyName = trim($company?->getName() ?? '');

        $xml = new \SimpleXMLElement('<QBXML></QBXML>');
        $msgsRq = $xml->addChild('QBXMLMsgsRq');
        $msgsRq->addAttribute('onError', 'stopOnError');
        $invoiceModRq = $msgsRq->addChild('InvoiceModRq');
        $invoiceMod = $invoiceModRq->addChild('InvoiceMod');

        $invoiceMod->addChild('TxnID', $invoice->getTxnId());
        $invoiceMod->addChild('EditSequence', $invoice->getEditSequence());

        $invoiceMod->addChild('CustomerRef')->addChild('FullName', htmlspecialchars($companyName));
        $invoiceMod->addChild('TxnDate', $invoice->getStartDate()->format('Y-m-d'));
        $invoiceMod->addChild('RefNumber', htmlspecialchars($invoice->getNumber()));

        if ($invoice->getNotes()) {
            $invoiceMod->addChild('Memo', htmlspecialchars($invoice->getNotes()));
        }

        if ($company->getAddress()) {
            $billAddress = $invoiceMod->addChild('BillAddress');
            $billAddress->addChild('Addr1', htmlspecialchars($company->getAddress()));
        }

        $invoiceItems = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarItems($invoice->getInvoiceId());

        foreach ($invoiceItems as $item) {
            $projectItem = $item->getProjectItem();
            $itemName = trim($projectItem->getItem()->getDescription());

            $line = $invoiceMod->addChild('InvoiceLineMod');
            $line->addChild('ItemRef')->addChild('FullName', htmlspecialchars($itemName));
            $line->addChild('Desc', 'Detalle actualizado');
            $line->addChild('Quantity', number_format($item->getQuantity(), 2, '.', ''));
            $line->addChild('Rate', number_format($item->getPrice(), 2, '.', ''));
        }

        return "<?xml version=\"1.0\"?>\n" . $xml->asXML();
    }


    /**
     * SalvarToken: Salvar token
     *
     * @param Usuario $usuario
     * @param string $token
     * @author Marcel
     */
    public function SalvarToken($usuario, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $user_qbwc_token = new UserQbwcToken();

        $user_qbwc_token->setToken($token);
        $user_qbwc_token->setUser($usuario);

        $em->persist($user_qbwc_token);

        $em->flush();
    }

    /**
     * AutenticarLogin: Chequear el login
     *
     * @param string $email Email
     * @param string $pass Pass
     * @author Marcel
     */
    public function AutenticarLogin($email, $pass)
    {
        // primero busco el usuario
        $usuario = $this->getDoctrine()->getRepository(Usuario::class)
            ->BuscarUsuarioPorEmail($email);

        /** @var Usuario $usuario */
        if ($usuario != null && $this->VerificarPassword($pass, $usuario->getContrasenna())) {
            if ($usuario->getHabilitado() == 1) {
                return $usuario;
            }
        }

        return null;
    }

}