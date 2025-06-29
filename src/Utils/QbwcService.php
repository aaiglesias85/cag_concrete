<?php

namespace App\Utils;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\SyncQueueQbwc;
use App\Entity\UserQbwcToken;
use App\Entity\Usuario;
use QuickBooks_QBXML_Object_Invoice;
use QuickBooks_QBXML_Object_Invoice_InvoiceLine;

class QbwcService extends Base
{
    public function BuscarSesion($token)
    {
        return $this->getDoctrine()->getRepository(UserQbwcToken::class)
            ->BuscarToken($token);
    }

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

    public function UpdateSyncQueueQbwc(string $xmlResponse): void
    {
        $this->writeLog("Recibido XML bruto:\n" . $xmlResponse);

        // Limpiar encabezados no válidos
        $cleanXml = trim($xmlResponse);
        $cleanXml = preg_replace('/<\?qbxml.*?\?>/i', '', $cleanXml);
        $cleanXml = preg_replace('/^[\x00-\x1F\x7F\xFE\xFF]+/', '', $cleanXml); // limpieza extra

        // Cargar XML
        $xml = simplexml_load_string($cleanXml);
        if (!$xml) {
            $this->writeLog("Error al parsear XML.");
            return;
        }

        $responseTypes = [
            'invoice' => '//InvoiceRet',
        ];

        $em = $this->getDoctrine()->getManager();

        foreach ($responseTypes as $tipo => $xpath) {
            $nodes = $xml->xpath($xpath);
            if (!$nodes || count($nodes) === 0) {
                $this->writeLog("No se encontraron nodos para tipo: {$tipo}");
                continue;
            }

            foreach ($nodes as $ret) {
                $txnId = (string)$ret->TxnID;
                $editSequence = (string)$ret->EditSequence;

                $this->writeLog("Procesando {$tipo}: TxnID={$txnId}, EditSequence={$editSequence}");
                $this->writeLog(var_export($ret, true));

                $item = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
                    ->findOneBy(['tipo' => strtolower($tipo), 'estado' => 'enviado'], ['id' => 'ASC']);
                /** @var SyncQueueQbwc $item */
                if ($item && $txnId && $editSequence) {
                    $item->setEstado('sincronizado');

                    $entityClass = match ($tipo) {
                        'invoice' => Invoice::class,
                        default => null,
                    };

                    if ($entityClass) {
                        $entity = $this->getDoctrine()->getRepository($entityClass)->find($item->getEntidadId());
                        if ($entity !== null) {
                            $entity->setTxnId($txnId);
                            $entity->setEditSequence($editSequence);
                            $this->writeLog("Actualizado entidad {$tipo} ID={$item->getEntidadId()}");

                            // Procesar cada InvoiceLineRet
                            if($tipo == 'invoice'){
                                foreach ($ret->InvoiceLineRet as $lineRet) {
                                    $txnLineId = (string)$lineRet->TxnLineID;
                                    $itemFullName = (string)$lineRet->ItemRef->FullName ?? null;

                                    // Buscar el InvoiceItem correspondiente (por nombre o posición)
                                    $invoiceItems = $this->getDoctrine()->getRepository(InvoiceItem::class)
                                        ->ListarItems($item->getEntidadId());
                                    $matched = null;
                                    foreach ($invoiceItems as $invoiceItem) {
                                        // Puedes mejorar el match aquí según cómo relates ProjectItem con FullName
                                        if ($invoiceItem->getProjectItem()->getItem()->getDescription() === $itemFullName) {
                                            $matched = $invoiceItem;
                                            break;
                                        }
                                    }

                                    if ($matched) {
                                        $matched->setTxnId($txnLineId);
                                        $this->writeLog("Actualizado InvoiceItem ID={$matched->getId()} con TxnLineID={$txnLineId}");
                                    } else {
                                        $this->writeLog("No se encontró InvoiceItem para TxnLineID={$txnLineId}");
                                    }
                                }
                            }

                        }
                    }
                }
            }
        }

        $em->flush();
    }

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
                    $this->writeLog($qbxml);
                    break;
            }

            if ($qbxml !== '') {
                $item->setEstado('enviado');
                $this->getDoctrine()->getManager()->flush();
            }
        }

        return $qbxml;
    }

    private function generateInvoiceQBXML(int $invoiceId): string
    {
        $invoice = $this->getDoctrine()->getRepository(Invoice::class)->find($invoiceId);
        /** @var Invoice $invoice  */
        if (!$invoice) return '';

        $isModification = $invoice->getTxnId() && $invoice->getEditSequence() && $invoice->getUpdatedAt() > $invoice->getCreatedAt();

        $bodyXml = $isModification
            ? $this->generateInvoiceModBodyQBXML($invoice)
            : $this->generateInvoiceAddBodyQBXML($invoice);

        $qbxml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $qbxml .= "<?qbxml version=\"16.0\"?>\n";
        $qbxml .= "<QBXML>\n";
        $qbxml .= "  <QBXMLMsgsRq onError=\"stopOnError\">\n";
        $qbxml .= $bodyXml . "\n";
        $qbxml .= "  </QBXMLMsgsRq>\n";
        $qbxml .= "</QBXML>";

        return $qbxml;
    }

    private function generateInvoiceAddBodyQBXML(Invoice $invoice): string
    {
        $project = $invoice->getProject();
        $company = $project->getCompany();

        $qbInvoice = new QuickBooks_QBXML_Object_Invoice();
        $qbInvoice->setCustomerFullName($company->getName());
        $qbInvoice->setTxnDate($invoice->getStartDate()->format('Y-m-d'));
        $qbInvoice->setRefNumber($invoice->getNumber());

        if ($invoice->getNotes()) {
            $qbInvoice->setMemo($invoice->getNotes());
        }

        if ($company->getAddress()) {
            $qbInvoice->setBillAddress($company->getAddress());
        }

        $items = $this->getDoctrine()->getRepository(InvoiceItem::class)->ListarItems($invoice->getInvoiceId());

        foreach ($items as $item) {
            $projectItem = $item->getProjectItem();
            $itemName = trim($projectItem->getItem()->getDescription());

            $line = new QuickBooks_QBXML_Object_Invoice_InvoiceLine();
            $line->setItemFullName($itemName);
            $line->setDesc('Detalle generado desde sistema');
            $line->setQuantity($item->getQuantity());
            $line->setRate($item->getPrice());
            $qbInvoice->addInvoiceLine($line);
        }

        return $qbInvoice->asQBXML('InvoiceAddRq');
    }

    private function generateInvoiceModBodyQBXML(Invoice $invoice): string
    {
        $project = $invoice->getProject();
        $company = $project->getCompany();

        $qbInvoice = new QuickBooks_QBXML_Object_Invoice();
        $qbInvoice->setCustomerFullName($company->getName());
        $qbInvoice->setTxnDate($invoice->getStartDate()->format('Y-m-d'));
        $qbInvoice->setRefNumber($invoice->getNumber());
        $qbInvoice->setTransactionID($invoice->getTxnId());
        $qbInvoice->setEditSequence($invoice->getEditSequence());

        if ($invoice->getNotes()) {
            $qbInvoice->setMemo($invoice->getNotes());
        }

        if ($company->getAddress()) {
            $qbInvoice->setBillAddress($company->getAddress());
        }

        $items = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarItems($invoice->getInvoiceId());
        foreach ($items as $item) {
            $projectItem = $item->getProjectItem();
            $itemName = trim($projectItem->getItem()->getDescription());

            $line = new QuickBooks_QBXML_Object_Invoice_InvoiceLine();

            $line->set('TxnLineID', $item->getTxnId() ?? -1);
            $line->setItemFullName($itemName);
            $line->setDesc('Detalle generado desde sistema');
            $line->setQuantity($item->getQuantity());
            $line->setRate($item->getPrice());

            $qbInvoice->addInvoiceLine($line);
        }

        return $qbInvoice->asQBXML('InvoiceModRq');
    }

    public function SalvarToken($usuario, $token)
    {
        $em = $this->getDoctrine()->getManager();

        $user_qbwc_token = new UserQbwcToken();
        $user_qbwc_token->setToken($token);
        $user_qbwc_token->setUser($usuario);

        $em->persist($user_qbwc_token);
        $em->flush();
    }

    public function AutenticarLogin($email, $pass)
    {
        $usuario = $this->getDoctrine()->getRepository(Usuario::class)
            ->BuscarUsuarioPorEmail($email);

        if ($usuario != null && $this->VerificarPassword($pass, $usuario->getContrasenna())) {
            if ($usuario->getHabilitado() == 1) {
                return $usuario;
            }
        }

        return null;
    }
}
