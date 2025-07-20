<?php

namespace App\Utils;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Item;
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

        $cleanXml = trim($xmlResponse);
        $cleanXml = preg_replace('/<\?qbxml.*?\?>/i', '', $cleanXml);
        $cleanXml = preg_replace('/^[\x00-\x1F\x7F\xFE\xFF]+/', '', $cleanXml);

        $xml = simplexml_load_string($cleanXml);
        if (!$xml) {
            $this->writeLog("Error al parsear XML.");
            return;
        }

        $em = $this->getDoctrine()->getManager();

        // 1. Procesar facturas como antes
        $invoiceRets = $xml->xpath('//InvoiceRet');
        foreach ($invoiceRets as $ret) {
            $txnId = (string)$ret->TxnID;
            $editSequence = (string)$ret->EditSequence;

            $this->writeLog("Procesando Invoice: TxnID={$txnId}, EditSequence={$editSequence}");
            $this->writeLog(var_export($ret, true));

            $items = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
                ->findBy(['tipo' => 'invoice', 'estado' => 'enviado'], ['id' => 'ASC']);

            if (!empty($items) && $txnId && $editSequence) {
                $item = $items[0];
                $item->setEstado('sincronizado');

                $invoice = $this->getDoctrine()->getRepository(Invoice::class)->find($item->getEntidadId());
                if ($invoice !== null) {
                    $invoice->setTxnId($txnId);
                    $invoice->setEditSequence($editSequence);
                    $this->writeLog("Actualizado entidad invoice ID={$item->getEntidadId()}");

                    foreach ($ret->InvoiceLineRet as $lineRet) {
                        $txnLineId = (string)$lineRet->TxnLineID;
                        $itemFullName = (string)$lineRet->ItemRef->FullName ?? null;

                        $invoiceItems = $this->getDoctrine()->getRepository(InvoiceItem::class)
                            ->ListarItems($item->getEntidadId());
                        $matched = null;
                        foreach ($invoiceItems as $invoiceItem) {
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

        // 2. Procesar respuesta de creación o modificación de ítems
        $itemRets = array_merge(
            $xml->xpath('//ItemServiceRet') ?: []
        );
        foreach ($itemRets as $itemRet) {
            $listId = (string)$itemRet->ListID;
            $editSequence = (string)$itemRet->EditSequence;
            $name = (string)$itemRet->Name;

            $this->writeLog("Procesando Item creado o modificado: Name={$name}, ListID={$listId}, EditSequence={$editSequence}");

            $queueItems = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
                ->findBy(['tipo' => 'item', 'estado' => 'enviado'], ['id' => 'ASC']);

            if (!empty($queueItems) && $listId && $editSequence) {
                /** @var SyncQueueQbwc $syncItem */
                $syncItem = $queueItems[0];
                $syncItem->setEstado('sincronizado');

                /** @var Item $item */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($syncItem->getEntidadId());
                if ($item !== null) {
                    $item->setTxnId($listId);
                    $item->setEditSequence($editSequence);

                    $this->writeLog("Actualizado Item ID={$item->getItemId()} con ListID y EditSequence");
                } else {
                    $this->writeLog("No se encontró entidad Item con ID={$syncItem->getEntidadId()}");
                }
            }
        }


        // 3. Procesar ItemQueryRs
        $itemTypes = ['ItemServiceRet', 'ItemInventoryRet', 'ItemNonInventoryRet', 'ItemOtherChargeRet'];
        $quickbooksItems = [];
        foreach ($itemTypes as $itemType) {
            $items = $xml->xpath("//{$itemType}");
            foreach ($items as $item) {
                $listId = (string)$item->ListID;
                $name = (string)$item->Name;
                $desc = (string)($item->SalesOrPurchase->Desc ?? '');
                $price = (float)($item->SalesOrPurchase->Price ?? 0.0);

                $quickbooksItems[] = [
                    'type' => $itemType,
                    'list_id' => $listId,
                    'name' => $name,
                    'description' => $desc,
                    'price' => $price,
                ];
            }
        }
        $this->writeLog("Ítems recibidos desde QuickBooks:\n" . var_export($quickbooksItems, true));

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
                case 'item':
                    $this->writeLog("Generando XML para tipo: {$tipo} ID: {$entidadId}");
                    $qbxml = $this->generateItemQBXML($entidadId);
                    $this->writeLog($qbxml);
                    break;
            }

            if ($qbxml !== '') {
                $item->setEstado('enviado');
                $this->getDoctrine()->getManager()->flush();
            }
        } else {
            // si no hay nada listar los items
            // $this->writeLog("Generando XML para listar items");
            // $qbxml = $this->generateListarItemsQBXML();
            // $this->writeLog($qbxml);
        }

        return $qbxml;
    }

    private function generateItemQBXML(int $itemId): string
    {
        $item = $this->getDoctrine()->getRepository(Item::class)->find($itemId);
        /** @var Item $item */
        if (!$item) return '';

        $isModification = $item->getTxnId() && $item->getEditSequence() && $item->getUpdatedAt() > $item->getCreatedAt();

        $bodyXml = $isModification
            ? $this->generateItemModBodyQBXML($item)
            : $this->generateItemAddBodyQBXML($item);

        $qbxml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $qbxml .= "<?qbxml version=\"16.0\"?>\n";
        $qbxml .= "<QBXML>\n";
        $qbxml .= "  <QBXMLMsgsRq onError=\"stopOnError\">\n";
        $qbxml .= $bodyXml . "\n";
        $qbxml .= "  </QBXMLMsgsRq>\n";
        $qbxml .= "</QBXML>";

        return $qbxml;
    }

    private function generateItemAddBodyQBXML(Item $item): string
    {
        $unit = $item->getUnit();
        $accountName = "Construction Income";

        $description = $item->getDescription();
        $description = htmlspecialchars($description, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // Solo incluir si hay unidad
        $unitXml = '';
        if ($unit !== null && $unit->getDescription()) {
            $unitName = htmlspecialchars($unit->getDescription(), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $unitXml = <<<XML
                        <UnitOfMeasureSetRef>
                          <FullName>{$unitName}</FullName>
                        </UnitOfMeasureSetRef>
                        XML;
        }

        return <<<XML
                <ItemServiceAddRq>
                  <ItemServiceAdd>
                    <Name>{$description}</Name>
                    <IsActive>true</IsActive>
                    <SalesOrPurchase>
                      <Desc>{$description}</Desc>
                      <AccountRef>
                        <FullName>{$accountName}</FullName>
                      </AccountRef>
                    </SalesOrPurchase>
                  </ItemServiceAdd>
                </ItemServiceAddRq>
                XML;

        /*
        return <<<XML
                <ItemServiceAddRq>
                  <ItemServiceAdd>
                    <Name>{$description}</Name>
                    <IsActive>true</IsActive>
                    {$unitXml}
                    <SalesOrPurchase>
                      <Desc>{$description}</Desc>
                      <AccountRef>
                        <FullName>{$accountName}</FullName>
                      </AccountRef>
                    </SalesOrPurchase>
                  </ItemServiceAdd>
                </ItemServiceAddRq>
                XML;
        */
    }

    private function generateItemModBodyQBXML(Item $item): string
    {
        $unit = $item->getUnit();
        $accountName = "Construction Income";

        $description = htmlspecialchars($item->getDescription(), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $listId = $item->getTxnId();
        $editSequence = $item->getEditSequence();

        if (!$listId || !$editSequence) {
            return '';
        }

        // Solo incluir si hay unidad
        $unitXml = '';
        if ($unit !== null && $unit->getDescription()) {
            $unitName = htmlspecialchars($unit->getDescription(), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $unitXml = <<<XML
                        <UnitOfMeasureSetRef>
                          <FullName>{$unitName}</FullName>
                        </UnitOfMeasureSetRef>
                        XML;
        }

        return <<<XML
                <ItemServiceModRq>
                  <ItemServiceMod>
                    <ListID>{$listId}</ListID>
                    <EditSequence>{$editSequence}</EditSequence>
                    <Name>{$description}</Name>
                    <IsActive>true</IsActive>
                    <SalesOrPurchase>
                      <Desc>{$description}</Desc>
                      <AccountRef>
                        <FullName>{$accountName}</FullName>
                      </AccountRef>
                    </SalesOrPurchase>
                  </ItemServiceMod>
                </ItemServiceModRq>
                XML;

        /*
        return <<<XML
                <ItemServiceModRq>
                  <ItemServiceMod>
                    <ListID>{$listId}</ListID>
                    <EditSequence>{$editSequence}</EditSequence>
                    <Name>{$description}</Name>
                    <IsActive>true</IsActive>
                    {$unitXml}
                    <SalesOrPurchase>
                      <Desc>{$description}</Desc>
                      <AccountRef>
                        <FullName>{$accountName}</FullName>
                      </AccountRef>
                    </SalesOrPurchase>
                  </ItemServiceMod>
                </ItemServiceModRq>
                XML;
        */
    }


    private function generateListarItemsQBXML()
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $xml .= '<?qbxml version="16.0"?>' . "\n";
        $xml .= '<QBXML>';
        $xml .= '<QBXMLMsgsRq onError="stopOnError">';
        $xml .= '  <ItemQueryRq>';
        $xml .= '    <MaxReturned>1000</MaxReturned>';
        $xml .= '    <ActiveStatus>All</ActiveStatus>';
        $xml .= '  </ItemQueryRq>';
        $xml .= '</QBXMLMsgsRq>';
        $xml .= '</QBXML>';

        return $xml;
    }

    private function generateInvoiceQBXML(int $invoiceId): string
    {
        $invoice = $this->getDoctrine()->getRepository(Invoice::class)->find($invoiceId);
        /** @var Invoice $invoice */
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
