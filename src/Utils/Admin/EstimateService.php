<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\County;
use App\Entity\District;
use App\Entity\Equation;
use App\Entity\Estimate;
use App\Entity\EstimateCompany;
use App\Entity\EstimateCounty;
use App\Entity\EstimateEstimator;
use App\Entity\EstimateProjectType;
use App\Entity\EstimateQuote;
use App\Entity\EstimateQuoteCompany;
use App\Entity\EstimateQuoteItem;
use App\Entity\EstimateQuoteItemNote;
use App\Entity\EstimateNoteItem;
use App\Entity\EstimateTemplateNote;
use App\Entity\Item;
use App\Entity\PlanDownloading;
use App\Entity\PlanStatus;
use App\Entity\ProjectStage;
use App\Entity\ProjectType;
use App\Entity\ProposalType;
use App\Entity\Usuario;
use App\Repository\EstimateCompanyRepository;
use App\Repository\EstimateCountyRepository;
use App\Repository\EstimateEstimatorRepository;
use App\Repository\EstimateProjectTypeRepository;
use App\Repository\EstimateQuoteCompanyRepository;
use App\Repository\EstimateQuoteItemRepository;
use App\Repository\EstimateQuoteItemNoteRepository;
use App\Repository\EstimateQuoteRepository;
use App\Repository\EstimateRepository;
use App\Repository\EstimateTemplateNoteRepository;
use App\Utils\Base;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EstimateService extends Base
{

   private MailerInterface $mailerQuotes;

   public function __construct(
      ContainerInterface    $container,
      MailerInterface       $mailer,
      ContainerBagInterface $containerBag,
      Security              $security,
      LoggerInterface       $logger,
      MailerInterface       $mailerQuotes
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->mailerQuotes = $mailerQuotes;
   }

   /**
    * EliminarCompany: Elimina un company en la BD
    * @param int $id Id
    * @author Marcel
    */
   public function EliminarCompany($id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(EstimateCompany::class)
         ->find($id);
      /**@var EstimateCompany $entity */
      if ($entity != null) {

         $estimate_name = $entity->getEstimate()->getName();
         $company_name = $entity->getCompany()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Company Estimate";
         $log_descripcion = "The company estimate is deleted: $estimate_name Company: $company_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * ListarQuotesDeEstimate: Lista las cuotas de un estimate
    */
   public function ListarQuotesDeEstimate($estimate_id)
   {
      /** @var EstimateQuoteRepository $estimateQuoteRepo */
      $estimateQuoteRepo = $this->getDoctrine()->getRepository(EstimateQuote::class);
      $quotes = $estimateQuoteRepo->ListarQuotesDeEstimate($estimate_id);
      $lista = [];
      foreach ($quotes as $q) {
         /** @var EstimateQuote $q */
         /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
         $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
         $items = $estimateQuoteItemRepo->ListarItemsDeQuote($q->getId());
         /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
         $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
         $companies = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($q->getId());
         $companies_data = [];
         foreach ($companies as $eqc) {
            $ec = $eqc->getEstimateCompany();
            if ($ec === null) continue;
            $companyName = $ec->getCompany() ? $ec->getCompany()->getName() : '';
            $email = $ec->getContact() ? $ec->getContact()->getEmail() : '';
            $companies_data[] = [
               'estimate_company_id' => $ec->getId(),
               'company' => $companyName,
               'email' => $email ?? '',
            ];
         }
         $lista[] = [
            'id' => $q->getId(),
            'name' => $q->getName(),
            'items_count' => count($items),
            'companies_count' => count($companies_data),
            'companies' => $companies_data,
         ];
      }
      return $lista;
   }

   /**
    * SalvarQuote: Crea o actualiza una cuota
    */
   public function SalvarQuote($estimate_id, $quote_id, $name)
   {
      $em = $this->getDoctrine()->getManager();
      $estimate = $this->getDoctrine()->getRepository(Estimate::class)->find($estimate_id);
      if ($estimate === null) {
         return ['success' => false, 'error' => 'Estimate not found'];
      }
      if ($quote_id !== '' && is_numeric($quote_id)) {
         $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
         if ($quote === null) {
            return ['success' => false, 'error' => 'Quote not found'];
         }
         $quote->setName($name);
         $em->flush();
         return ['success' => true, 'quote_id' => $quote->getId()];
      }
      $quote = new EstimateQuote();
      $quote->setEstimate($estimate);
      $quote->setName($name);
      $em->persist($quote);
      $em->flush();
      $this->SalvarLog('Add', 'Estimate Quote', 'Quote added: ' . $name);
      return ['success' => true, 'quote_id' => $quote->getId()];
   }

   /**
    * EliminarQuote: Elimina una cuota (cascade elimina ítems y companies)
    */
   public function EliminarQuote($quote_id)
   {
      if ($quote_id === '' || $quote_id === null || !is_numeric($quote_id)) {
         return ['success' => false, 'error' => 'Invalid quote'];
      }
      $em = $this->getDoctrine()->getManager();
      $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
      if ($quote === null) {
         return ['success' => false, 'error' => 'Quote not found'];
      }
      $name = $quote->getName();

      // Eliminar ítems de la quote (y sus notas estimate_quote_item_note)
      /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
      $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
      /** @var EstimateQuoteItemNoteRepository $eqinRepo */
      $eqinRepo = $this->getDoctrine()->getRepository(EstimateQuoteItemNote::class);
      $items = $estimateQuoteItemRepo->ListarItemsDeQuote($quote_id);
      foreach ($items as $estimate_item) {
         foreach ($eqinRepo->findByQuoteItemId($estimate_item->getId()) as $quoteItemNote) {
            $em->remove($quoteItemNote);
         }
         $em->remove($estimate_item);
      }

      // Eliminar companies de la quote
      /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
      $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
      $existing = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($quote_id);
      foreach ($existing as $eqc) {
         $em->remove($eqc);
      }

      $em->remove($quote);
      $em->flush();
      $this->SalvarLog('Delete', 'Estimate Quote', 'Quote deleted: ' . $name);
      return ['success' => true];
   }

   /**
    * CargarDatosQuote: Devuelve cuota con ítems y compañías asignadas
    */
   public function CargarDatosQuote($quote_id)
   {
      if ($quote_id === '' || $quote_id === null || !is_numeric($quote_id)) {
         return ['success' => false, 'error' => 'Invalid quote'];
      }
      $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
      if ($quote === null) {
         return ['success' => false, 'error' => 'Quote not found'];
      }
      /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
      $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
      $items = $estimateQuoteItemRepo->ListarItemsDeQuote($quote_id);
      $items_data = [];
      foreach ($items as $key => $value) {
         $items_data[] = $this->DevolverItemDeEstimate($value, $key);
      }
      /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
      $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
      $companies = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($quote_id);
      $companies_data = [];
      foreach ($companies as $eqc) {
         $ec = $eqc->getEstimateCompany();
         if ($ec === null) continue;
         $companyName = $ec->getCompany() ? $ec->getCompany()->getName() : '';
         $email = $ec->getContact() ? $ec->getContact()->getEmail() : '';
         $companies_data[] = [
            'id' => $eqc->getId(),
            'estimate_company_id' => $ec->getId(),
            'company' => $companyName,
            'email' => $email ?? '',
         ];
      }
      return [
         'success' => true,
         'quote' => [
            'id' => $quote->getId(),
            'name' => $quote->getName(),
            'estimate_id' => $quote->getEstimate()->getEstimateId(),
            'items' => $items_data,
            'companies' => $companies_data,
         ],
      ];
   }

   /**
    * SalvarQuoteCompanies: Asigna registros estimate_company a una cuota (reemplaza las actuales).
    * Cada estimate_company tiene contact_id → el email del contacto es a quien se envía.
    * @param string|array $estimate_company_ids IDs de estimate_company (coma o array)
    */
   public function SalvarQuoteCompanies($quote_id, $estimate_company_ids)
   {
      if ($quote_id === '' || $quote_id === null || !is_numeric($quote_id)) {
         return ['success' => false, 'error' => 'Invalid quote'];
      }
      $em = $this->getDoctrine()->getManager();
      $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
      if ($quote === null) {
         return ['success' => false, 'error' => 'Quote not found'];
      }
      /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
      $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
      $existing = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($quote_id);
      foreach ($existing as $eqc) {
         $em->remove($eqc);
      }
      $ids = is_array($estimate_company_ids) ? $estimate_company_ids : (strpos((string)$estimate_company_ids, ',') !== false ? explode(',', $estimate_company_ids) : [$estimate_company_ids]);
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      foreach ($ids as $eid) {
         $eid = trim((string)$eid);
         if ($eid === '') continue;
         $estimateCompany = $estimateCompanyRepo->find($eid);
         if ($estimateCompany !== null) {
            $eqc = new EstimateQuoteCompany();
            $eqc->setQuote($quote);
            $eqc->setEstimateCompany($estimateCompany);
            $em->persist($eqc);
         }
      }
      $em->flush();
      return ['success' => true];
   }

   /**
    * EliminarQuoteCompanies: Elimina todos los registros estimate_quote_company de una cuota
    */
   public function EliminarQuoteCompanies($quote_id)
   {
      if ($quote_id === '' || $quote_id === null || !is_numeric($quote_id)) {
         return ['success' => false, 'error' => 'Invalid quote'];
      }
      $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
      if ($quote === null) {
         return ['success' => false, 'error' => 'Quote not found'];
      }
      $em = $this->getDoctrine()->getManager();
      /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
      $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
      $existing = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($quote_id);
      foreach ($existing as $eqc) {
         $em->remove($eqc);
      }
      $em->flush();
      return ['success' => true];
   }

   /**
    * EnviarQuotes: Genera Excel por cuota y envía email a cada compañía asignada
    * @param string|array $quote_ids Id(s) de cuota (coma o array)
    */
   public function EnviarQuotes($quote_ids)
   {
      $ids = is_array($quote_ids) ? $quote_ids : explode(',', $quote_ids);
      $ids = array_map('trim', $ids);
      $ids = array_filter($ids, function ($id) { return $id !== '' && is_numeric($id); });
      $enviados = 0;
      $errores = [];
      $fromAddress = $this->getParameter('mailer_quotes_sender_address');
      $fromName = $this->getParameter('mailer_quotes_from_name') ?? '';
      $copyAddress = $this->getParameter('mailer_quotes_copy_address') ?? '';

      foreach ($ids as $quote_id) {
         $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
         if ($quote === null) {
            $errores[] = "Quote $quote_id not found";
            continue;
         }
         /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
         $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
         $quoteCompanies = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($quote_id);
         if (empty($quoteCompanies)) {
            $errores[] = "Quote \"{$quote->getName()}\" has no companies assigned";
            continue;
         }
         $pdfPath = $this->GenerarPdfQuote($quote_id);
         if ($pdfPath === null || !is_file($pdfPath)) {
            $errores[] = "Could not generate PDF for quote \"{$quote->getName()}\"";
            continue;
         }
         $quoteName = $quote->getName();
         $estimateName = $quote->getEstimate()->getName();
         foreach ($quoteCompanies as $eqc) {
            $ec = $eqc->getEstimateCompany();
            if ($ec === null) continue;
            $contact = $ec->getContact();
            $email = $contact ? $contact->getEmail() : '';
            if ($email === '' || $email === null) {
               $companyName = $ec->getCompany() ? $ec->getCompany()->getName() : 'Company';
               $errores[] = "Company \"{$companyName}\" has no contact email";
               continue;
            }
            $companyName = $ec->getCompany() ? $ec->getCompany()->getName() : '';
            $toName = $contact ? $contact->getName() : $companyName;
            try {
               $mensaje = (new TemplatedEmail())
                  ->from(new Address($fromAddress, $fromName))
                  ->to(new Address($email, $toName))
                  ->bcc(...($copyAddress !== '' ? [new Address($copyAddress)] : []))
                  ->subject("Quote: $quoteName - $estimateName")
                  ->htmlTemplate('mailing/mail_quote.html.twig')
                  ->context([
                     'quote_name' => $quoteName,
                     'estimate_name' => $estimateName,
                     'company_name' => $companyName,
                     'direccion_url' => $this->ObtenerURL(),
                  ])
                  ->attachFromPath($pdfPath, basename($pdfPath), 'application/pdf');
               $this->mailerQuotes->send($mensaje);
               $enviados++;
            } catch (\Throwable $e) {
               $errores[] = "Email to {$companyName} ({$email}): " . $e->getMessage();
            }
         }
         @unlink($pdfPath);
      }

      $success = $enviados > 0;
      $message = $enviados . ' email(s) sent.';
      if (!empty($errores)) {
         $message .= ' ' . implode(' ', $errores);
      }
      return ['success' => $success, 'message' => $message, 'enviados' => $enviados, 'errores' => $errores];
   }

   /**
    * Construye el spreadsheet de una quote desde plantilla (bid_letting o bid_bids).
    * Se usa para generar primero el Excel en memoria y luego convertirlo a PDF.
    * @return Spreadsheet|null
    */
   private function buildSpreadsheetForQuote($quote_id)
   {
      if ($quote_id === '' || $quote_id === null || !is_numeric($quote_id)) {
         return null;
      }
      $quote = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
      if ($quote === null) return null;
      $estimate = $quote->getEstimate();
      $proposalType = $estimate->getProposalType();
      $templateName = ($proposalType && (int)$proposalType->getTypeId() === 2) ? 'bid_letting.xlsx' : 'bid_bids.xlsx';
      $templatePath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . 'metronic8' . DIRECTORY_SEPARATOR . 'excel' . DIRECTORY_SEPARATOR . $templateName;
      if (!is_file($templatePath)) return null;

      /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
      $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
      $items = $estimateQuoteItemRepo->ListarItemsDeQuote($quote_id);

      $reader = IOFactory::createReader('Xlsx');
      $spreadsheet = $reader->load($templatePath);
      $sheet = $spreadsheet->getActiveSheet();

      $bidDeadlineStr = $estimate->getBidDeadline() ? $estimate->getBidDeadline()->format('m/d/Y') : '';
      // COMPLETION DATE en el PDF/Excel: Project End del estimate (no awarded date)
      $projectEndStr = $estimate->getProjectEnd() ? $estimate->getProjectEnd()->format('m/d/Y') : '';

      if ($templateName === 'bid_bids.xlsx') {
         $sheet->setCellValue('E3', $bidDeadlineStr);
         $sheet->setCellValue('E4', $estimate->getProjectId() ?? '');
         $sheet->setCellValue('E5', $estimate->getBidNo() ?? '');
         $sheet->setCellValue('E6', $estimate->getName() ?? '');
         $sheet->setCellValue('E8', $estimate->getLocation() ?? '');
         $sheet->setCellValue('E9', $projectEndStr);
      } else {
         $sheet->setCellValue('E3', $bidDeadlineStr);
         $sheet->setCellValue('E4', $estimate->getProjectId() ?? '');
         $sheet->setCellValue('E5', $estimate->getBidNo() ?? '');
         $sheet->setCellValue('E6', '');
         $sheet->setCellValue('E7', $estimate->getLocation() ?? '');
         $sheet->setCellValue('E8', $projectEndStr);
      }

      // Reducir tamaño de fuente y dar más ancho a columna D (labels) para que se vean en una sola línea en el PDF
      $sheet->getStyle('D1:D50')->getFont()->setSize(8);
      $sheet->getColumnDimension('D')->setWidth(24); // ancho en caracteres para que "PROPOSAL NO" etc. quepa en una línea
      $sheet->getStyle('D1:D50')->getAlignment()->setWrapText(false);

      $firstDataRow = ($templateName === 'bid_letting.xlsx') ? 11 : 12;
      $totalItemRows = 27; // filas reservadas en el template para ítems
      $fila = $firstDataRow;
      $sumTotal = 0.0;
      /** @var EstimateQuoteItemNoteRepository $eqinRepo */
      $eqinRepo = $this->getDoctrine()->getRepository(EstimateQuoteItemNote::class);

      foreach ($items as $row) {
         /** @var EstimateQuoteItem $row */
         $item = $row->getItem();
         $qty = (float) $row->getQuantity();
         $price = (float) $row->getPrice();
         $total = $qty * $price;
         $sumTotal += $total;
         $description = $item ? $item->getName() : '';
         $quoteItemNotes = $eqinRepo->findByQuoteItemId($row->getId());
         $noteDescriptions = [];
         foreach ($quoteItemNotes as $eqin) {
            $n = $eqin->getNoteItem();
            if ($n !== null && $n->getDescription() !== null && $n->getDescription() !== '') {
               $noteDescriptions[] = $n->getDescription();
            }
         }
         if (count($noteDescriptions) > 0) {
            $description .= ' - ' . implode(' - ', $noteDescriptions);
         }
         $unit = ($item && $item->getUnit()) ? $item->getUnit()->getDescription() : '';
         $sheet->setCellValue('B' . $fila, $description);
         $sheet->mergeCells('B' . $fila . ':C' . $fila);
         $sheet->setCellValue('D' . $fila, $unit);
         $sheet->setCellValue('E' . $fila, $qty);
         $sheet->setCellValue('F' . $fila, $price);
         $sheet->setCellValue('G' . $fila, $total);
         $fila++;
      }
      $numItems = count($items);

      // Quitar solo las filas vacías de las 27 de ítems (las que quedaron sin usar)
      if ($numItems < $totalItemRows) {
         $filasVacias = $totalItemRows - $numItems;
         $sheet->removeRow($firstDataRow + $numItems, $filasVacias);
      }

      // Fila de sumatoria: en el template es la primera fila después de las 27 de ítems; tras quitar filas vacías queda en firstDataRow + numItems (solo una celda con el total)
      $filaSumatoria = $firstDataRow + $numItems;
      $sheet->setCellValue('G' . $filaSumatoria, $sumTotal);

      // Template notes del estimate: letting → B41, bids → B42; tras removeRow las filas bajaron, restar filasVacias.
      $templateNotes = $this->ListarTemplateNotesDeEstimate($estimate->getEstimateId());
      $filasVacias = ($numItems < $totalItemRows) ? ($totalItemRows - $numItems) : 0;
      $notesRowBase = ($templateName === 'bid_letting.xlsx') ? 41 : 42;
      $notesRow = $notesRowBase - $filasVacias;
      $notesCell = 'B' . $notesRow;
      if (!empty($templateNotes)) {
         $notesText = implode("\n", array_map(function ($n) {
            return (string) ($n['description'] ?? '');
         }, $templateNotes));
         $sheet->setCellValue($notesCell, $notesText);
         $sheet->mergeCells('B' . $notesRow . ':G' . $notesRow);
         $sheet->getStyle('B' . $notesRow . ':G' . $notesRow)->getAlignment()->setWrapText(true);
      }

      // Ocultar solo las filas vacías al final del sheet (después de la última con contenido). No tocamos las filas en blanco entre total y TERMS AND CONDITIONS.
      $highestRow = $sheet->getHighestDataRow();
      $highestCol = $sheet->getHighestDataColumn();
      $maxCol = Coordinate::columnIndexFromString($highestCol);
      $lastRowWithContent = $filaSumatoria;
      for ($r = $filaSumatoria + 1; $r <= $highestRow; $r++) {
         $hasContent = false;
         for ($c = 1; $c <= $maxCol; $c++) {
            $colStr = Coordinate::stringFromColumnIndex($c);
            $addr = $colStr . $r;
            if ($sheet->getCellCollection()->has($addr)) {
               $val = $sheet->getCell($addr)->getCalculatedValue();
               if ($val !== null && trim((string) $val) !== '') {
                  $hasContent = true;
                  break;
               }
            }
         }
         if ($hasContent) {
            $lastRowWithContent = $r;
         }
      }
      // Ocultar solo desde la fila siguiente a la última con contenido hasta el final
      for ($r = $lastRowWithContent + 1; $r <= $highestRow; $r++) {
         $sheet->getRowDimension($r)->setVisible(false);
      }

      // PDF vía PhpSpreadsheet→HTML→Mpdf: no sobrescribir PageSetup de la plantilla (Carta vertical, fit 1×1, márgenes).
      // Las plantillas incluyen columnas vacías hasta O/Z; ocultarlas evita una tabla HTML demasiado ancha y escala incorrecta.
      $lastColIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
      for ($ci = 8; $ci <= $lastColIndex; $ci++) {
         $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($ci))->setVisible(false);
      }

      return $spreadsheet;
   }

   /**
    * Genera el PDF de una quote: construye el Excel en memoria y lo convierte a PDF con Mpdf.
    * @return string|null Ruta al archivo .pdf generado
    */
   private function GenerarPdfQuote($quote_id)
   {
      $spreadsheet = $this->buildSpreadsheetForQuote($quote_id);
      if ($spreadsheet === null) {
         return null;
      }
      $projectDir = $this->getParameter('kernel.project_dir');
      $dir = $projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'excel';
      if (!is_dir($dir)) {
         mkdir($dir, 0777, true);
      }
      $filename = 'quote_' . $quote_id . '_' . date('YmdHis') . '.pdf';
      $path = $dir . DIRECTORY_SEPARATOR . $filename;

      $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
      $writer->setPreCalculateFormulas(true); // para que columnas con fórmulas (p. ej. G y G39) muestren el valor en el PDF
      $tempDir = $projectDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'phpsppdf';
      if (!is_dir($tempDir)) {
         @mkdir($tempDir, 0777, true);
      }
      if (is_dir($tempDir) && method_exists($writer, 'setTempDir')) {
         $writer->setTempDir($tempDir);
      }
      $writer->save($path);
      return is_file($path) ? $path : null;
   }

   /**
    * ExportarExcelQuote: Genera el PDF de una cuota (desde el mismo contenido que el Excel) y devuelve la URL para descarga.
    * @param int|string $quote_id
    * @return string|null URL del archivo PDF generado o null
    */
   public function ExportarExcelQuote($quote_id)
   {
      $path = $this->GenerarPdfQuote($quote_id);
      if ($path === null || !is_file($path)) {
         return null;
      }
      return $this->ObtenerURL() . 'uploads/excel/' . basename($path);
   }

   /**
    * AgregarItem
    * @param $item_id
    * @param $item_name
    * @param $unit_id
    * @param $quantity
    * @param $price
    * @param $yield_calculation
    * @param $equation_id
    * @return array
    */
   public function AgregarItem($estimate_item_id, $estimate_id, $quote_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id, $note_ids = [], $code = null, $contract_name = null)
   {
      $resultado = [];

      $em = $this->getDoctrine()->getManager();

      $codeCatalog = $this->normalizeNullableTrimmedString($code);
      $contractNameCatalog = $this->normalizeNullableTrimmedString($contract_name);

      // validar si existe el mismo item en la misma cuota (puede repetirse en otra quote)
      if ($item_id !== '') {
         $quote_id_check = ($quote_id !== '' && $quote_id !== null && is_numeric($quote_id)) ? $quote_id : null;
         if ($quote_id_check !== null) {
            /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
            $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
            $estimate_item = $estimateQuoteItemRepo->BuscarItemEstimateEnQuote($estimate_id, $quote_id_check, $item_id);
            if (!empty($estimate_item) && (string)$estimate_item_id !== (string)$estimate_item[0]->getId()) {
               $resultado['success'] = false;
               $resultado['error'] = "The item already exists in the project estimate";
               return $resultado;
            }
         }
      } else {

         //Verificar description
         $item = $this->getDoctrine()->getRepository(Item::class)
            ->findOneBy(['description' => $item_name]);
         if ($item_id == '' && $item != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The item name is in use, please try entering another one.";
            return $resultado;
         }
      }


      $estimate_entity = $this->getDoctrine()->getRepository(Estimate::class)->find($estimate_id);
      if ($estimate_entity != null) {
         /** @var EstimateQuoteRepository $estimateQuoteRepo */
         $estimateQuoteRepo = $this->getDoctrine()->getRepository(EstimateQuote::class);
         $quotes = $estimateQuoteRepo->ListarQuotesDeEstimate($estimate_id);
         $default_quote = !empty($quotes) ? $quotes[0] : null;
         $quote_created_in_request = false;
         if ($default_quote === null) {
            $default_quote = new EstimateQuote();
            $default_quote->setName('Quote 1');
            $default_quote->setEstimate($estimate_entity);
            $em->persist($default_quote);
            $em->flush();
            $quote_created_in_request = true;
         }
         // Si se envió quote_id y es válido, usar esa cuota para el ítem nuevo
         if ($quote_id !== '' && is_numeric($quote_id)) {
            $quote_entity = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quote_id);
            if ($quote_entity !== null && $quote_entity->getEstimate()->getEstimateId() == (int) $estimate_id) {
               $default_quote = $quote_entity;
            }
         }

         $estimate_item_entity = null;

         if (is_numeric($estimate_item_id)) {
            $estimate_item_entity = $this->getDoctrine()->getRepository(EstimateQuoteItem::class)
               ->find($estimate_item_id);
         }

         $is_new_estimate_item = false;
         if ($estimate_item_entity == null) {
            $estimate_item_entity = new EstimateQuoteItem();
            $is_new_estimate_item = true;
         }

         $estimate_item_entity->setYieldCalculation($yield_calculation);

         $price = $price !== "" ? $price : NULL;
         $estimate_item_entity->setPrice($price);

         $estimate_item_entity->setQuantity($quantity);

         $equation_entity = null;
         if ($equation_id != '') {
            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
            $estimate_item_entity->setEquation($equation_entity);
         }

         $is_new_item = false;
         if ($item_id != '') {
            $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($item_id);
         } else {
            // add new item
            $new_item_data = json_encode([
               'item' => $item_name,
               'price' => $price,
               'yield_calculation' => $yield_calculation,
               'unit_id' => $unit_id,
            ]);
            $item_entity = $this->AgregarNewItem(json_decode($new_item_data), $equation_entity);

            $is_new_item = true;
         }

         $estimate_item_entity->setItem($item_entity);
         $estimate_item_entity->setCode($codeCatalog);
         $estimate_item_entity->setContractName($contractNameCatalog);

         if ($is_new_estimate_item && $default_quote !== null) {
            $estimate_item_entity->setQuote($default_quote);
            $em->persist($estimate_item_entity);
         }

         $em->flush(); // obtener id del quote item antes de asociar notas

         // Notas: reemplazar asociaciones (many-to-many)
         $noteIds = is_array($note_ids) ? $note_ids : (is_string($note_ids) && $note_ids !== '' ? array_filter(array_map('intval', explode(',', $note_ids))) : []);
         /** @var EstimateQuoteItemNoteRepository $eqinRepo */
         $eqinRepo = $this->getDoctrine()->getRepository(EstimateQuoteItemNote::class);
         foreach ($eqinRepo->findByQuoteItemId($estimate_item_entity->getId()) as $existingNote) {
            $em->remove($existingNote);
         }
         $estimateNoteItemRepo = $this->getDoctrine()->getRepository(EstimateNoteItem::class);
         foreach ($noteIds as $nid) {
            if ($nid <= 0) continue;
            $noteEntity = $estimateNoteItemRepo->find($nid);
            if ($noteEntity !== null) {
               $eqin = new EstimateQuoteItemNote();
               $eqin->setQuoteItem($estimate_item_entity);
               $eqin->setNoteItem($noteEntity);
               $em->persist($eqin);
            }
         }
         $em->flush();

         $resultado['success'] = true;

         // devolver item
         $item = $this->DevolverItemDeEstimate($estimate_item_entity);
         $resultado['item'] = $item;
         $resultado['is_new_item'] = $is_new_item;
         if ($quote_created_in_request && $default_quote !== null) {
            $resultado['quote_created'] = ['id' => $default_quote->getId(), 'name' => $default_quote->getName()];
         }
      } else {
         $resultado['success'] = false;
         $resultado['error'] = 'The project not exist';
      }

      return $resultado;
   }

   /**
    * EliminarItem: Elimina un item en la BD
    * @param int $estimate_item_id Id
    * @author Marcel
    */
   public function EliminarItem($estimate_item_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(EstimateQuoteItem::class)
         ->find($estimate_item_id);
      /**@var EstimateQuoteItem $entity */
      if ($entity != null) {

         $item_name = $entity->getItem()->getName();

         // Eliminar notas del ítem (estimate_quote_item_note)
         /** @var EstimateQuoteItemNoteRepository $eqinRepo */
         $eqinRepo = $this->getDoctrine()->getRepository(EstimateQuoteItemNote::class);
         foreach ($eqinRepo->findByQuoteItemId($entity->getId()) as $quoteItemNote) {
            $em->remove($quoteItemNote);
         }

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Estimate Item";
         $log_descripcion = "The item: $item_name of the project estimate is deleted";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * CambiarStage: Cambia stage del estiamte en la BD
    * @param int $estimate_id Id
    * @author Marcel
    */
   public function CambiarStage($estimate_id, $stage_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Estimate::class)
         ->find($estimate_id);
      /** @var Estimate $entity */
      if ($entity != null) {

         $name = $entity->getName();

         $entity->setStage(NULL);
         if ($stage_id != '') {
            $project_stage = $this->getDoctrine()->getRepository(ProjectStage::class)
               ->find($stage_id);
            $entity->setStage($project_stage);
            $this->AutoSetStageDate($entity, (int) $stage_id);
         }

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Estimate";
         $log_descripcion = "The estimate is modified: $name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * CargarDatosEstimate: Carga los datos de un estimate
    *
    * @param int $estimate_id Id
    *
    * @author Marcel
    */
   public function CargarDatosEstimate($estimate_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Estimate::class)
         ->find($estimate_id);
      /** @var Estimate $entity */
      if ($entity != null) {

         $arreglo_resultado['project_id'] = $entity->getProjectId();
         $arreglo_resultado['name'] = $entity->getName();
         $arreglo_resultado['bidDeadline'] = $entity->getBidDeadline() ? $entity->getBidDeadline()->format('m/d/Y H:i') : "";
         $arreglo_resultado['priority'] = $entity->getPriority();
         $arreglo_resultado['bidNo'] = $entity->getBidNo();
         $arreglo_resultado['workHour'] = $entity->getWorkHour();
         $arreglo_resultado['phone'] = $entity->getPhone();
         $arreglo_resultado['email'] = $entity->getEmail();

         $arreglo_resultado['jobWalk'] = $entity->getJobWalk() ? $entity->getJobWalk()->format('m/d/Y H:i') : "";
         $arreglo_resultado['rfiDueDate'] = $entity->getRfiDueDate() ? $entity->getRfiDueDate()->format('m/d/Y H:i') : "";
         $arreglo_resultado['projectStart'] = $entity->getProjectStart() ? $entity->getProjectStart()->format('m/d/Y H:i') : "";
         $arreglo_resultado['projectEnd'] = $entity->getProjectEnd() ? $entity->getProjectEnd()->format('m/d/Y H:i') : "";
         $arreglo_resultado['submittedDate'] = $entity->getSubmittedDate() ? $entity->getSubmittedDate()->format('m/d/Y H:i') : "";
         $arreglo_resultado['awardedDate'] = $entity->getAwardedDate() ? $entity->getAwardedDate()->format('m/d/Y H:i') : "";
         $arreglo_resultado['lostDate'] = $entity->getLostDate() ? $entity->getLostDate()->format('m/d/Y H:i') : "";
         $arreglo_resultado['location'] = $entity->getLocation();
         $arreglo_resultado['sector'] = $entity->getSector();
         $arreglo_resultado['bidDescription'] = $entity->getBidDescription();
         $arreglo_resultado['bidInstructions'] = $entity->getBidInstructions();
         $arreglo_resultado['planLink'] = $entity->getPlanLink();
         $arreglo_resultado['quoteReceived'] = $entity->getQuoteReceived();

         $arreglo_resultado['stage_id'] = $entity->getStage() != null ? $entity->getStage()->getStageId() : '';
         $arreglo_resultado['proposal_type_id'] = $entity->getProposalType() != null ? $entity->getProposalType()->getTypeId() : '';
         $arreglo_resultado['status_id'] = $entity->getStatus() != null ? $entity->getStatus()->getStatusId() : '';

         $county_ids = $this->ListarCountiesId($estimate_id);
         $arreglo_resultado['county_ids'] = $county_ids;
         $county_id = count($county_ids) > 0 ? $county_ids[0] : ($entity->getCountyObj() ? $entity->getCountyObj()->getCountyId() : null);
         $arreglo_resultado['county_id'] = $county_id;

         $arreglo_resultado['district_id'] = $entity->getDistrict() != null ? $entity->getDistrict()->getDistrictId() : '';
         $arreglo_resultado['plan_downloading_id'] = $entity->getPlanDownloading() != null ? $entity->getPlanDownloading()->getPlanDownloadingId() : '';

         // estimators ids
         $estimators_id = $this->ListarEstimatorsId($estimate_id);
         $arreglo_resultado['estimators_id'] = $estimators_id;

         // project types ids
         $project_types_id = $this->ListarProjectTypesId($estimate_id);
         $arreglo_resultado['project_types_id'] = $project_types_id;

         // quotes con items y companies (agrupados)
         $quotes = $this->ListarQuotesDeEstimate($estimate_id);
         $arreglo_resultado['quotes'] = $quotes;

         // items planos (con quote_id) para la tabla agrupada en front
         $items = $this->ListarItemsDeEstimate($estimate_id);
         $arreglo_resultado['items'] = $items;

         // companys
         $companys = $this->ListarCompanys($estimate_id);
         $arreglo_resultado['companys'] = $companys;

         // template notes (notas tipo template asociadas al estimate)
         $template_notes = $this->ListarTemplateNotesDeEstimate($estimate_id);
         $arreglo_resultado['template_notes'] = $template_notes;

         $resultado['success'] = true;
         $resultado['estimate'] = $arreglo_resultado;
      }

      return $resultado;
   }

   // listar los companys del estimate
   private function ListarCompanys($estimate_id)
   {
      $companys = [];

      /** @var EstimateCompanyRepository $estimateCompanyRepo */
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      $estimate_companys = $estimateCompanyRepo->ListarCompanysDeEstimate($estimate_id);
      foreach ($estimate_companys as $key => $estimate_company) {


         $contact_id = "";
         $contact = "";
         $email = "";
         $phone = "";
         if ($estimate_company->getContact()) {
            $contact_id = $estimate_company->getContact()->getContactId();
            $contact = $estimate_company->getContact()->getName();
            $email = $estimate_company->getContact()->getEmail();
            $phone = $estimate_company->getContact()->getPhone();
         }

         // contacts
         $contacts = $this->ListarContactsDeCompany($estimate_company->getCompany()->getCompanyId());


         $companys[] = [
            'id' => $estimate_company->getId(),
            'company_id' => $estimate_company->getCompany()->getCompanyId(),
            'company' => $estimate_company->getCompany()->getName(),
            'contact_id' => $contact_id,
            'contact' => $contact,
            'email' => $email,
            'phone' => $phone,
            'contacts' => $contacts,
            'bidDeadline' => $estimate_company->getBidDeadline()
               ? $estimate_company->getBidDeadline()->format('m/d/Y H:i')
               : '',
            'tag' => $estimate_company->getTag() ?? '',
            'address' => $estimate_company->getAddress() ?? '',
            'posicion' => $key,
         ];
      }

      return $companys;
   }

   /**
    * ListarTemplateNotesDeEstimate: Notas tipo template asociadas al estimate
    *
    * @param int $estimate_id
    * @return array
    */
   public function ListarTemplateNotesDeEstimate($estimate_id)
   {
      /** @var EstimateTemplateNoteRepository $repo */
      $repo = $this->getDoctrine()->getRepository(EstimateTemplateNote::class);
      $list = $repo->findByEstimateId((int) $estimate_id);
      $out = [];
      foreach ($list as $key => $etn) {
         $n = $etn->getNoteItem();
         $out[] = [
            'id' => $etn->getId(),
            'estimate_note_item_id' => $n ? $n->getId() : null,
            'description' => $n ? $n->getDescription() : '',
            'posicion' => $key,
         ];
      }
      return $out;
   }

   /**
    * AgregarTemplateNote: Asocia una nota tipo template al estimate
    *
    * @param int $estimate_id
    * @param int $estimate_note_item_id
    * @return array
    */
   public function AgregarTemplateNote($estimate_id, $estimate_note_item_id)
   {
      $em = $this->getDoctrine()->getManager();
      $estimate = $this->getDoctrine()->getRepository(Estimate::class)->find($estimate_id);
      $noteItem = $this->getDoctrine()->getRepository(EstimateNoteItem::class)->find($estimate_note_item_id);
      if ($estimate === null || $noteItem === null) {
         return ['success' => false, 'error' => 'Estimate or note not found'];
      }
      if ($noteItem->getType() !== 'template') {
         return ['success' => false, 'error' => 'Note must be of type template'];
      }
      /** @var EstimateTemplateNoteRepository $repo */
      $repo = $this->getDoctrine()->getRepository(EstimateTemplateNote::class);
      $existing = $repo->findOneBy(['estimate' => $estimate, 'noteItem' => $noteItem]);
      if ($existing !== null) {
         return ['success' => false, 'error' => 'This template note is already added'];
      }
      $etn = new EstimateTemplateNote();
      $etn->setEstimate($estimate);
      $etn->setNoteItem($noteItem);
      $em->persist($etn);
      $em->flush();
      return ['success' => true, 'id' => $etn->getId(), 'description' => $noteItem->getDescription(), 'estimate_note_item_id' => $noteItem->getId()];
   }

   /**
    * EliminarTemplateNote: Quita una nota template del estimate
    *
    * @param int $id estimate_template_note.id
    * @return array
    */
   public function EliminarTemplateNote($id)
   {
      $em = $this->getDoctrine()->getManager();
      $entity = $this->getDoctrine()->getRepository(EstimateTemplateNote::class)->find($id);
      if ($entity === null) {
         return ['success' => false, 'error' => 'Record not found'];
      }
      $em->remove($entity);
      $em->flush();
      return ['success' => true];
   }

   /**
    * ListarItemsDeEstimate
    * @param $estimate_id
    * @return array
    */
   public function ListarItemsDeEstimate($estimate_id)
   {
      $items = [];

      /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
      $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
      $lista = $estimateQuoteItemRepo->ListarItemsDeEstimate($estimate_id);
      foreach ($lista as $key => $value) {

         $item = $this->DevolverItemDeEstimate($value, $key);
         $items[] = $item;
      }

      return $items;
   }

   /**
    * DevolverItemDeEstimate
    * @param EstimateQuoteItem $value
    * @return array
    */
   public function DevolverItemDeEstimate($value, $key = -1)
   {
      $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

      $quantity = $value->getQuantity();
      $price = $value->getPrice();
      $total = $quantity * $price;

      /** @var EstimateQuoteItemNoteRepository $eqinRepo */
      $eqinRepo = $this->getDoctrine()->getRepository(EstimateQuoteItemNote::class);
      $quoteItemNotes = $eqinRepo->findByQuoteItemId($value->getId());
      $notes = [];
      $note_ids = [];
      foreach ($quoteItemNotes as $eqin) {
         $n = $eqin->getNoteItem();
         if ($n !== null) {
            $notes[] = $n->getDescription() ?? '';
            $note_ids[] = $n->getId();
         }
      }

      return [
         'estimate_item_id' => $value->getId(),
         'quote_id' => $value->getQuote() !== null ? $value->getQuote()->getId() : null,
         "item_id" => $value->getItem()->getItemId(),
         "code" => $value->getCode(),
         "contract_name" => $value->getContractName(),
         "item" => $value->getItem()->getName(),
         "unit" => $value->getItem()->getUnit() != null ? $value->getItem()->getUnit()->getDescription() : '',
         "quantity" => $quantity,
         "price" => $price,
         "total" => $total,
         "yield_calculation" => $value->getYieldCalculation(),
         "yield_calculation_name" => $yield_calculation_name,
         "equation_id" => $value->getEquation() != null ? $value->getEquation()->getEquationId() : '',
         "notes" => $notes,
         "note_ids" => $note_ids,
         "posicion" => $key
      ];
   }

   // listar los estimators del estimate
   private function ListarEstimatorsId($estimate_id)
   {
      $ids = [];

      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $estimate_estimators = $estimateEstimatorRepo->ListarUsuariosDeEstimate($estimate_id);
      foreach ($estimate_estimators as $estimate_estimator) {
         $ids[] = $estimate_estimator->getUser()->getUsuarioId();
      }

      return $ids;
   }

   // listar los project types del estimate
   private function ListarProjectTypesId($estimate_id)
   {
      $ids = [];

      /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
      $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
      $estimate_project_types = $estimateProjectTypeRepo->ListarTypesDeEstimate($estimate_id);
      foreach ($estimate_project_types as $estimate_project_type) {
         $ids[] = $estimate_project_type->getType()->getTypeId();
      }

      return $ids;
   }

   /**
    * @return int[]
    */
   private function ListarCountiesId($estimate_id): array
   {
      $ids = [];

      /** @var EstimateCountyRepository $estimateCountyRepo */
      $estimateCountyRepo = $this->getDoctrine()->getRepository(EstimateCounty::class);
      foreach ($estimateCountyRepo->ListarCountiesDeEstimate($estimate_id) as $estimateCounty) {
         $c = $estimateCounty->getCounty();
         if ($c !== null) {
            $ids[] = $c->getCountyId();
         }
      }

      return $ids;
   }

   /**
    * Texto para listado / calendario: condados asociados (tabla + legacy county_id).
    */
   private function DescripcionCountiesParaListado(Estimate $value): string
   {
      $estimate_id = $value->getEstimateId();
      /** @var EstimateCountyRepository $estimateCountyRepo */
      $estimateCountyRepo = $this->getDoctrine()->getRepository(EstimateCounty::class);
      $rows = $estimateCountyRepo->ListarCountiesDeEstimate($estimate_id);
      $names = [];
      foreach ($rows as $ec) {
         $c = $ec->getCounty();
         if ($c !== null) {
            $names[] = $c->getDescription();
         }
      }
      if (count($names) > 0) {
         return implode(', ', $names);
      }
      if (method_exists($value, 'getCountyObj') && $value->getCountyObj()) {
         return $value->getCountyObj()->getDescription();
      }
      if (method_exists($value, 'getCounty') && $value->getCounty()) {
         return (string) $value->getCounty();
      }

      return '';
   }

   /**
    * EliminarEstimate: Elimina un rol en la BD
    * @param int $estimate_id Id
    * @author Marcel
    */
   public function EliminarEstimate($estimate_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Estimate::class)
         ->find($estimate_id);
      /**@var Estimate $entity */
      if ($entity != null) {

         // eliminar informacion relacionada
         $this->EliminarInformacionRelacionada($estimate_id);

         $estimate_descripcion = $entity->getName();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Estimate";
         $log_descripcion = "The estimate is deleted: $estimate_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarEstimates: Elimina los estimates seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarEstimates($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $estimate_id) {
            if ($estimate_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Estimate::class)
                  ->find($estimate_id);
               /**@var Estimate $entity */
               if ($entity != null) {

                  // eliminar informacion relacionada
                  $this->EliminarInformacionRelacionada($estimate_id);

                  $estimate_descripcion = $entity->getName();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Estimate";
                  $log_descripcion = "The estimate is deleted: $estimate_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The estimates could not be deleted, because they are associated with a invoice";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected estimates because they are associated with a invoice";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   // eliminar informacion relacionada
   private function EliminarInformacionRelacionada($estimate_id)
   {
      $em = $this->getDoctrine()->getManager();

      // 1) Quote items (y sus notas estimate_quote_item_note se eliminan por CASCADE en BD o explícitamente)
      /** @var EstimateQuoteItemRepository $estimateQuoteItemRepo */
      $estimateQuoteItemRepo = $this->getDoctrine()->getRepository(EstimateQuoteItem::class);
      /** @var EstimateQuoteItemNoteRepository $eqinRepo */
      $eqinRepo = $this->getDoctrine()->getRepository(EstimateQuoteItemNote::class);
      $estimate_items = $estimateQuoteItemRepo->ListarItemsDeEstimate($estimate_id);
      foreach ($estimate_items as $estimate_item) {
         foreach ($eqinRepo->findByQuoteItemId($estimate_item->getId()) as $quoteItemNote) {
            $em->remove($quoteItemNote);
         }
         $em->remove($estimate_item);
      }

      // 2) Quote companies y quotes del estimate
      $quotes = $this->ListarQuotesDeEstimate($estimate_id);
      /** @var EstimateQuoteCompanyRepository $estimateQuoteCompanyRepo */
      $estimateQuoteCompanyRepo = $this->getDoctrine()->getRepository(EstimateQuoteCompany::class);
      foreach ($quotes as $q) {
         $quoteId = $q['id'];
         $existing = $estimateQuoteCompanyRepo->ListarCompaniesDeQuote($quoteId);
         foreach ($existing as $eqc) {
            $em->remove($eqc);
         }
         $quoteEntity = $this->getDoctrine()->getRepository(EstimateQuote::class)->find($quoteId);
         if ($quoteEntity !== null) {
            $em->remove($quoteEntity);
         }
      }

      // 3) estimators
      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $estimates_estimators = $estimateEstimatorRepo->ListarUsuariosDeEstimate($estimate_id);
      foreach ($estimates_estimators as $estimate_estimator) {
         $em->remove($estimate_estimator);
      }

      // 4) counties (estimate_county)
      /** @var EstimateCountyRepository $estimateCountyRepo */
      $estimateCountyRepo = $this->getDoctrine()->getRepository(EstimateCounty::class);
      foreach ($estimateCountyRepo->ListarCountiesDeEstimate($estimate_id) as $estimateCounty) {
         $em->remove($estimateCounty);
      }

      // 5) project types
      /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
      $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
      $estimates_project_types = $estimateProjectTypeRepo->ListarTypesDeEstimate($estimate_id);
      foreach ($estimates_project_types as $estimate_project_type) {
         $em->remove($estimate_project_type);
      }

      // 6) companys (estimate_company)
      /** @var EstimateCompanyRepository $estimateCompanyRepo */
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      $companys = $estimateCompanyRepo->ListarCompanysDeEstimate($estimate_id);
      foreach ($companys as $company) {
         $em->remove($company);
      }
   }

   /**
    * ActualizarEstimate: Actuializa los datos del rol en la BD
    * @param int $estimate_id Id
    * @author Marcel
    */
   public function ActualizarEstimate(
      $estimate_id,
      $project_id,
      $name,
      $bidDeadline,
      $county_ids,
      $priority,
      $bidNo,
      $workHour,
      $phone,
      $email,
      $stage_id,
      $proposal_type_id,
      $status_id,
      $district_id,
      $project_types_id,
      $estimators_id,
      $jobWalk,
      $rfiDueDate,
      $projectStart,
      $projectEnd,
      $submittedDate,
      $awardedDate,
      $lostDate,
      $location,
      $sector,
      $plan_downloading_id,
      $bidDescription,
      $bidInstructions,
      $planLink,
      $quoteReceived,
      $companys
   ) {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Estimate::class)
         ->find($estimate_id);
      /** @var Estimate $entity */
      if ($entity != null) {

         //Verificar nombre
         $estimate = $this->getDoctrine()->getRepository(Estimate::class)
            ->findOneBy(['name' => $name]);
         if ($estimate != null && $estimate_id != $estimate->getEstimateId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The project estimate name is in use, please try entering another one.";
            return $resultado;
         }

         $entity->setProjectId($project_id);
         $entity->setName($name);
         $entity->setPriority($priority);
         $entity->setBidNo($bidNo);
         $entity->setWorkHour($workHour);
         $entity->setPhone($phone);
         $entity->setEmail($email);
         $entity->setLocation($location);
         $entity->setSector($sector);

         $entity->setBidDescription($bidDescription);
         $entity->setBidInstructions($bidInstructions);
         $entity->setPlanLink($planLink);
         $entity->setQuoteReceived($quoteReceived);

         $entity->setBidDeadline(NULL);
         if ($bidDeadline != '') {
            $bidDeadline = \DateTime::createFromFormat('m/d/Y H:i', $bidDeadline);
            $entity->setBidDeadline($bidDeadline);
         }

         $entity->setStage(NULL);
         if ($stage_id != '') {
            $project_stage = $this->getDoctrine()->getRepository(ProjectStage::class)
               ->find($stage_id);
            $entity->setStage($project_stage);
           $this->AutoSetStageDate($entity, (int) $stage_id);
         }

         $entity->setProposalType(NULL);
         if ($proposal_type_id != '') {
            $proposal_type = $this->getDoctrine()->getRepository(ProposalType::class)
               ->find($proposal_type_id);
            $entity->setProposalType($proposal_type);
         }

         $entity->setStatus(NULL);
         if ($status_id != '') {
            $plan_status = $this->getDoctrine()->getRepository(PlanStatus::class)
               ->find($status_id);
            $entity->setStatus($plan_status);
         }

         $entity->setCountyObj(NULL);

         $entity->setDistrict(NULL);
         if ($district_id != '') {
            $district = $this->getDoctrine()->getRepository(District::class)
               ->find($district_id);
            $entity->setDistrict($district);
         }

         $entity->setJobWalk(NULL);
         if ($jobWalk != '') {
            $jobWalk = \DateTime::createFromFormat('m/d/Y H:i', $jobWalk);
            $entity->setJobWalk($jobWalk);
         }

         $entity->setRfiDueDate(NULL);
         if ($rfiDueDate != '') {
            $rfiDueDate = \DateTime::createFromFormat('m/d/Y H:i', $rfiDueDate);
            $entity->setRfiDueDate($rfiDueDate);
         }

         $entity->setProjectStart(NULL);
         if ($projectStart != '') {
            $projectStart = \DateTime::createFromFormat('m/d/Y H:i', $projectStart);
            $entity->setProjectStart($projectStart);
         }

         $entity->setProjectEnd(NULL);
         if ($projectEnd != '') {
            $projectEnd = \DateTime::createFromFormat('m/d/Y H:i', $projectEnd);
            $entity->setProjectEnd($projectEnd);
         }

         $entity->setSubmittedDate(NULL);
         if ($submittedDate != '') {
            $submittedDate = \DateTime::createFromFormat('m/d/Y H:i', $submittedDate);
            $entity->setSubmittedDate($submittedDate);
         }

         $entity->setAwardedDate(NULL);
         if ($awardedDate != '') {
            $awardedDate = \DateTime::createFromFormat('m/d/Y H:i', $awardedDate);
            $entity->setAwardedDate($awardedDate);
         }

         $entity->setLostDate(NULL);
         if ($lostDate != '') {
            $lostDate = \DateTime::createFromFormat('m/d/Y H:i', $lostDate);
            $entity->setLostDate($lostDate);
         }

         $entity->setPlanDownloading(NULL);
         if ($plan_downloading_id != '') {
            $plan_downloading = $this->getDoctrine()->getRepository(PlanDownloading::class)
               ->find($plan_downloading_id);
            $entity->setPlanDownloading($plan_downloading);
         }

         // save project types
         $this->SalvarProjectTypes($entity, $project_types_id, false);

         // save estimators
         $this->SalvarEstimators($entity, $estimators_id, false);

         // counties
         $this->SalvarCounties($entity, $county_ids, false);

         // companys
         $this->SalvarCompanys($entity, $companys);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Estimate";
         $log_descripcion = "The estimate is modified: $name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['estimate_id'] = $entity->getEstimateId();

         return $resultado;
      }
   }

   /**
    * SalvarCompanys
    * @param $companys
    * @param Estimate $entity
    * @return void
    */
   public function SalvarCompanys($entity, $companys)
   {
      $em = $this->getDoctrine()->getManager();

      if (!empty($companys)) {
         foreach ($companys as $value) {

            $estimate_company_entity = null;

            if (is_numeric($value->id)) {
               $estimate_company_entity = $this->getDoctrine()->getRepository(EstimateCompany::class)
                  ->find($value->id);
            }

            $is_new_estimate_company = false;
            if ($estimate_company_entity == null) {
               $estimate_company_entity = new EstimateCompany();
               $is_new_estimate_company = true;
            }

            if ($value->company_id != '') {
               $company = $this->getDoctrine()->getRepository(Company::class)
                  ->find($value->company_id);
               $estimate_company_entity->setCompany($company);
            }

            if ($value->contact_id != '') {
               $contact = $this->getDoctrine()->getRepository(CompanyContact::class)
                  ->find($value->contact_id);
               $estimate_company_entity->setContact($contact);
            }

            $tag = $value->tag ?? null;
            $estimate_company_entity->setTag($tag !== '' && $tag !== null ? (string) $tag : null);

            $address = $value->address ?? null;
            $estimate_company_entity->setAddress($address !== '' && $address !== null ? (string) $address : null);

            $estimate_company_entity->setBidDeadline(null);
            $bidDeadlineRaw = $value->bidDeadline ?? '';
            if ($bidDeadlineRaw !== '' && $bidDeadlineRaw !== null) {
               $bidDt = \DateTime::createFromFormat('m/d/Y H:i', (string) $bidDeadlineRaw);
               if ($bidDt !== false) {
                  $estimate_company_entity->setBidDeadline($bidDt);
               }
            }

            if ($is_new_estimate_company) {
               $estimate_company_entity->setEstimate($entity);

               $em->persist($estimate_company_entity);
            }
         }
      }
   }

   /**
    * SalvarEstimate: Guarda los datos de estimate en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarEstimate(
      $project_id,
      $name,
      $bidDeadline,
      $county_ids,
      $priority,
      $bidNo,
      $workHour,
      $phone,
      $email,
      $stage_id,
      $proposal_type_id,
      $status_id,
      $district_id,
      $project_types_id,
      $estimators_id,
      $jobWalk,
      $rfiDueDate,
      $projectStart,
      $projectEnd,
      $submittedDate,
      $awardedDate,
      $lostDate,
      $location,
      $sector,
      $plan_downloading_id,
      $bidDescription,
      $bidInstructions,
      $planLink,
      $quoteReceived,
      $companys
   ) {
      $em = $this->getDoctrine()->getManager();

      //Verificar nombre
      $estimate = $this->getDoctrine()->getRepository(Estimate::class)
         ->findOneBy(['name' => $name]);
      if ($estimate != null) {
         $resultado['success'] = false;
         $resultado['error'] = "The project estimate name is in use, please try entering another one.";
         return $resultado;
      }

      $entity = new Estimate();

      $entity->setProjectId($project_id);
      $entity->setName($name);
      $entity->setPriority($priority);
      $entity->setBidNo($bidNo);
      $entity->setWorkHour($workHour);
      $entity->setPhone($phone);
      $entity->setEmail($email);
      $entity->setLocation($location);
      $entity->setSector($sector);

      $entity->setBidDescription($bidDescription);
      $entity->setBidInstructions($bidInstructions);
      $entity->setPlanLink($planLink);
      $entity->setQuoteReceived($quoteReceived);

      if ($bidDeadline != '') {
         $bidDeadline = \DateTime::createFromFormat('m/d/Y H:i', $bidDeadline);
         $entity->setBidDeadline($bidDeadline);
      }

      if ($stage_id != '') {
         $project_stage = $this->getDoctrine()->getRepository(ProjectStage::class)
            ->find($stage_id);
         $entity->setStage($project_stage);
         $this->AutoSetStageDate($entity, (int) $stage_id);
      }

      if ($proposal_type_id != '') {
         $proposal_type = $this->getDoctrine()->getRepository(ProposalType::class)
            ->find($proposal_type_id);
         $entity->setProposalType($proposal_type);
      }

      if ($status_id != '') {
         $plan_status = $this->getDoctrine()->getRepository(PlanStatus::class)
            ->find($status_id);
         $entity->setStatus($plan_status);
      }

      if ($district_id != '') {
         $district = $this->getDoctrine()->getRepository(District::class)
            ->find($district_id);
         $entity->setDistrict($district);
      }

      if ($jobWalk != '') {
         $jobWalk = \DateTime::createFromFormat('m/d/Y H:i', $jobWalk);
         $entity->setJobWalk($jobWalk);
      }

      if ($rfiDueDate != '') {
         $rfiDueDate = \DateTime::createFromFormat('m/d/Y H:i', $rfiDueDate);
         $entity->setRfiDueDate($rfiDueDate);
      }

      $entity->setProjectStart(null);
      if ($projectStart != '') {
         $projectStart = \DateTime::createFromFormat('m/d/Y H:i', $projectStart);
         $entity->setProjectStart($projectStart);
      }

      $entity->setProjectEnd(null);
      if ($projectEnd != '') {
         $projectEnd = \DateTime::createFromFormat('m/d/Y H:i', $projectEnd);
         $entity->setProjectEnd($projectEnd);
      }

      if ($submittedDate != '') {
         $submittedDate = \DateTime::createFromFormat('m/d/Y H:i', $submittedDate);
         $entity->setSubmittedDate($submittedDate);
      }

      if ($awardedDate != '') {
         $awardedDate = \DateTime::createFromFormat('m/d/Y H:i', $awardedDate);
         $entity->setAwardedDate($awardedDate);
      }

      if ($lostDate != '') {
         $lostDate = \DateTime::createFromFormat('m/d/Y H:i', $lostDate);
         $entity->setLostDate($lostDate);
      }

      if ($plan_downloading_id != '') {
         $plan_downloading = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->find($plan_downloading_id);
         $entity->setPlanDownloading($plan_downloading);
      }

      $em->persist($entity);

      // save project types
      $this->SalvarProjectTypes($entity, $project_types_id);

      // save estimators
      $this->SalvarEstimators($entity, $estimators_id);

      // counties (N) + primer county en estimate.county_id
      $this->SalvarCounties($entity, $county_ids, true);

      // companys
      $this->SalvarCompanys($entity, $companys);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Project Estimate";
      $log_descripcion = "The project estimate is added: $name";
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['estimate_id'] = $entity->getEstimateId();

      return $resultado;
   }

   /**
    * AutoSetStageDate: asigna automáticamente la fecha según el stage
    *
    * @param Estimate $entity
    * @param int $stage_id
    */
   private function AutoSetStageDate(Estimate $entity, int $stage_id): void
   {
      $now = new \DateTime();

      if ($stage_id === 6) {
         $entity->setSubmittedDate($now);
      }

      if ($stage_id === 7) {
         $entity->setAwardedDate($now);
      }

      if ($stage_id === 8) {
         $entity->setLostDate($now);
      }
   }

   // salvar estimators
   public function SalvarEstimators($entity, $estimators_id, $is_new = true)
   {
      $em = $this->getDoctrine()->getManager();

      // eliminar anteriores
      if (!$is_new) {
         /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
         $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
         $estimates_estimators = $estimateEstimatorRepo->ListarUsuariosDeEstimate($entity->getEstimateId());
         foreach ($estimates_estimators as $estimate_estimator) {
            $em->remove($estimate_estimator);
         }
      }

      if ($estimators_id !== '') {

         $estimators_id = explode(',', $estimators_id);

         foreach ($estimators_id as $estimator_id) {
            $user_entity = $this->getDoctrine()->getRepository(Usuario::class)
               ->find($estimator_id);
            if ($user_entity !== null) {
               $estimate_estimator = new EstimateEstimator();

               $estimate_estimator->setEstimate($entity);
               $estimate_estimator->setUser($user_entity);

               $em->persist($estimate_estimator);
            }
         }
      }
   }

   // salvar project types
   public function SalvarProjectTypes($entity, $project_types_id, $is_new = true)
   {
      $em = $this->getDoctrine()->getManager();

      // eliminar anteriores
      if (!$is_new) {
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         $estimates_project_types = $estimateProjectTypeRepo->ListarTypesDeEstimate($entity->getEstimateId());
         foreach ($estimates_project_types as $estimate_project_type) {
            $em->remove($estimate_project_type);
         }
      }

      if ($project_types_id !== '') {

         $project_types_id = explode(',', $project_types_id);

         foreach ($project_types_id as $project_type_id) {
            $project_type_entity = $this->getDoctrine()->getRepository(ProjectType::class)
               ->find($project_type_id);
            if ($project_type_entity !== null) {
               $estimate_project_type = new EstimateProjectType();

               $estimate_project_type->setEstimate($entity);
               $estimate_project_type->setType($project_type_entity);

               $em->persist($estimate_project_type);
            }
         }
      }
   }

   /**
    * SalvarCounties: tabla estimate_county + county_id principal (primer condado) para compatibilidad.
    *
    * @param string|array|null $counties_id IDs separados por coma o array
    */
   public function SalvarCounties(Estimate $entity, $counties_id, bool $is_new = true): void
   {
      $em = $this->getDoctrine()->getManager();

      if (!$is_new) {
         /** @var EstimateCountyRepository $estimateCountyRepo */
         $estimateCountyRepo = $this->getDoctrine()->getRepository(EstimateCounty::class);
         foreach ($estimateCountyRepo->ListarCountiesDeEstimate($entity->getEstimateId()) as $ec) {
            $em->remove($ec);
         }
      }

      $entity->setCountyObj(null);

      if ($counties_id === null || $counties_id === '') {
         return;
      }

      $ids = is_array($counties_id) ? $counties_id : explode(',', (string) $counties_id);
      $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));

      if (count($ids) === 0) {
         return;
      }

      $first = true;
      foreach ($ids as $cid) {
         $county = $this->getDoctrine()->getRepository(County::class)->find($cid);
         if ($county === null) {
            continue;
         }
         $ec = new EstimateCounty();
         $ec->setEstimate($entity);
         $ec->setCounty($county);
         $em->persist($ec);
         if ($first) {
            $entity->setCountyObj($county);
            $first = false;
         }
      }
   }


   /**
    * ListarEstimates: Listar los estimates
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarEstimates(
      $start,
      $limit,
      $sSearch,
      $iSortCol_0,
      $sSortDir_0,
      $stage_id,
      $project_type_id,
      $proposal_type_id,
      $county_id,
      $status_id,
      $district_id,
      $fecha_inicial,
      $fecha_fin
   ) {
      $arreglo_resultado = array();
      $cont = 0;

      // listar
      $lista = [];
      if ($project_type_id === "") {
         /** @var EstimateRepository $estimateRepo */
         $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
         $lista = $estimateRepo->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $stage_id, $proposal_type_id, $county_id, $status_id, $district_id, $fecha_inicial, $fecha_fin);
      } else {
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         $estimates_project_type = $estimateProjectTypeRepo->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $stage_id, $proposal_type_id, $county_id, $status_id, $district_id, $project_type_id, $fecha_inicial, $fecha_fin);
         foreach ($estimates_project_type as $estimate_project_type) {
            $lista[] = $estimate_project_type->getEstimate();
         }
      }


      foreach ($lista as $value) {
         $estimate_id = $value->getEstimateId();

         $acciones = $this->ListarAcciones($estimate_id);

         $bidDeadline = $value->getBidDeadline() ? $value->getBidDeadline()->format('m/d/Y H:i') : "Not set";


         $project_id = $value->getProjectId();
         $project_number = $project_id;

         $proposal_number = $value->getBidNo();
         // companies
         $companies = $this->ListarCompaniesParaListado($value);

         // estimators
         $estimators = $this->ListarEstimatorsParaListado($estimate_id);

         $county_name = $this->DescripcionCountiesParaListado($value);

         // stage
         $stage = $this->DevolverStageParaListado($estimate_id, $value->getStage());

         // name
         $name = $value->getName() . ($value->getQuoteReceived()
            ? ' <i class="fa fa-check-circle" style="color: green;" title="Quote received"></i>'
            : '');

         $arreglo_resultado[$cont] = array(
            "id" => $estimate_id,
            "name" => $name,
            "proposal_number" => $proposal_number,
            "project_id" => $project_number,
            "county" => $county_name,
            "company" => $companies,
            "bidDeadline" => $bidDeadline,
            "estimators" => $estimators,
            "stage" => $stage,
            "acciones" => $acciones
         );


         $cont++;
      }

      return $arreglo_resultado;
   }

   // listar los companies para el listado
   private function ListarCompaniesParaListado(Estimate $estimate)
   {
      $companies = [];

      /** @var EstimateCompanyRepository $estimateCompanyRepo */
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      $estimate_companys = $estimateCompanyRepo->ListarCompanysDeEstimate($estimate->getEstimateId());
      foreach ($estimate_companys as $estimate_company) {
         $companies[] = $estimate_company->getCompany()->getName();
      }

      if (count($companies) === 0) {
         return '';
      }

      $primerNombre = htmlspecialchars($companies[0], ENT_QUOTES, 'UTF-8');
      $primerNombre_truncado = $this->truncate($primerNombre, 30);
      $html = '<div class="d-inline-flex align-items-center" style="gap: 8px;">';

      $restantes = array_slice($companies, 1);

      // Estilo base para los badges
      $estiloBase = 'padding: 3px 9px; font-size: 11px;cursor:pointer; color: #FFF;';

      // Si hay más de una empresa, agregar borde izquierdo rojo al primer badge
      $estiloPrincipal = $estiloBase;
      if (count($restantes) > 0) {
         $estiloPrincipal .= ' border-left: 3px solid red;';
      }

      // Badge principal
      $html .= '<span class="badge badge-primary" style="' . $estiloPrincipal . '" title="' . $primerNombre . '">' . $primerNombre_truncado . '</span>';

      if (count($restantes) > 0) {
         // Badges del popover
         $contenidoPopover = implode('', array_map(function ($c) use ($estiloBase) {
            $c = htmlspecialchars($c, ENT_QUOTES, 'UTF-8');
            return '<div class="mb-1"><span class="badge badge-primary" style="' . $estiloBase . '">' . $c . '</span></div>';
         }, $restantes));

         $dataContent = htmlspecialchars($contenidoPopover, ENT_QUOTES, 'UTF-8');

         $html .= '<span class="badge bg-primary popover-company"
                        data-bs-toggle="popover"
                        data-bs-html="true"
                        data-bs-content="' . $dataContent . '"
                        style="' . $estiloBase . '">+' . count($restantes) . '</span>';
      }

      $html .= '</div>';

      return $html;
   }


   // devolver stage stages
   private function DevolverStageParaListado($estimate_id, ?ProjectStage $stage)
   {
      $html = "";

      if ($stage !== null) {
         $stage_id = $stage->getStageId();
         $descripcion = $stage->getDescription();
         $color = $stage->getColor();

         $html = <<<HTML
                <span class="change-stage" data-id="{$estimate_id}" data-stage="{$stage_id}" style="
                    background-color: {$color};
                    color: white;
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-size: 12px;
                    font-weight: bold;
                    display: inline-block;
                    font-family: Arial, sans-serif;
                    cursor: pointer;
                ">
                    {$descripcion}
                </span>
                HTML;
      }

      return $html;
   }

   // listar los estimators para el listado
   private function ListarEstimatorsParaListado($estimate_id)
   {
      $estimators = [];

      // listar
      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $lista = $estimateEstimatorRepo->ListarUsuariosDeEstimate($estimate_id);
      foreach ($lista as $value) {
         $nombre = $value->getUser()->getNombreCompleto();
         $siglas = $this->generarAvatarHTML($nombre);

         $estimators[] = $siglas;
      }

      return implode(" ", $estimators);
   }

   private function generarAvatarHTML($nombreCompleto)
   {
      // Extraer iniciales
      $nombreCompleto = preg_replace('/\s+/', ' ', trim($nombreCompleto));
      $partes = explode(' ', $nombreCompleto);

      if (count($partes) < 2) return '';

      $inicialNombre = strtoupper(mb_substr($partes[0], 0, 1));
      $inicialApellido = strtoupper(mb_substr($partes[1], 0, 1));
      $iniciales = $inicialNombre . $inicialApellido;

      // Generar color aleatorio en formato hexadecimal
      $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

      // HTML con estilo en línea
      $html = <<<HTML
                <div style="
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    background-color: {$color};
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 14px;
                    font-family: Arial, sans-serif;
                    text-transform: uppercase;
                    cursor: pointer;
                " title="{$nombreCompleto}">
                    {$iniciales}
                </div>
                HTML;

      return $html;
   }

   /**
    * ListarEstimatesParaCalendario: eventos FullCalendar (bid deadline).
    *
    * @return array<int, array<string, mixed>>
    */
   public function ListarEstimatesParaCalendario(
      $search,
      $stage_id,
      $project_type_id,
      $proposal_type_id,
      $status_id,
      $county_id,
      $district_id,
      $fecha_inicial,
      $fecha_fin
   ): array {
      $sSearch = $search !== null && $search !== '' ? (string) $search : '';

      if ($project_type_id === '' || $project_type_id === null) {
         /** @var EstimateRepository $estimateRepo */
         $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
         $lista = $estimateRepo->ListarEstimatesParaCalendario(
            $sSearch,
            (string) $stage_id,
            (string) $proposal_type_id,
            (string) $status_id,
            (string) $county_id,
            (string) $district_id,
            (string) $fecha_inicial,
            (string) $fecha_fin
         );
      } else {
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         $lista = $estimateProjectTypeRepo->ListarEstimatesParaCalendario(
            $sSearch,
            (string) $stage_id,
            (string) $proposal_type_id,
            (string) $status_id,
            (string) $county_id,
            (string) $district_id,
            (string) $project_type_id,
            (string) $fecha_inicial,
            (string) $fecha_fin
         );
      }

      $events = [];
      foreach ($lista as $entity) {
         if (!$entity instanceof Estimate) {
            continue;
         }
         $bd = $entity->getBidDeadline();
         if ($bd === null) {
            continue;
         }

         $start = \DateTime::createFromInterface($bd);
         $end = clone $start;
         $end->modify('+30 minutes');

         $stage = $entity->getStage();
         $stageColor = $stage !== null ? $stage->getColor() : null;
         $stageName = $stage !== null ? (string) $stage->getDescription() : '';

         $countyLabel = $this->DescripcionCountiesParaListado($entity);

         $name = $entity->getName() ?? '';
         $projectId = $entity->getProjectId() ?? '';
         $title = trim($projectId !== '' ? $projectId . ' · ' . $name : $name);
         if ($entity->getQuoteReceived()) {
            $title .= ' ✓';
         }

         $ev = [
            'id' => (string) $entity->getEstimateId(),
            'title' => $title,
            'start' => $start->format('Y-m-d\TH:i:s'),
            'end' => $end->format('Y-m-d\TH:i:s'),
            'extendedProps' => [
               'projectId' => $projectId,
               'proposalNo' => $entity->getBidNo() ?? '',
               'stage' => $stageName,
               'county' => $countyLabel,
               'bidDeadline' => $start->format('m/d/Y H:i'),
               'quoteReceived' => (bool) $entity->getQuoteReceived(),
            ],
         ];

         if ($stageColor !== null && $stageColor !== '') {
            $ev['backgroundColor'] = $stageColor;
            $ev['borderColor'] = $stageColor;
         }

         if ($entity->getQuoteReceived()) {
            $ev['className'] = 'estimate-event-quote-received';
         }

         $events[] = $ev;
      }

      return $events;
   }

   /**
    * TotalEstimates: Total de estimates
    * @param string $sSearch Para buscar
    * @author Marcel
    */
   public function TotalEstimates($sSearch, $stage_id, $project_type_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin)
   {
      if ($project_type_id === '') {
         /** @var EstimateRepository $estimateRepo */
         $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
         return $estimateRepo->TotalEstimates($sSearch, $stage_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin);
      } else {
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         return $estimateProjectTypeRepo->TotalEstimates($sSearch, $stage_id, $proposal_type_id, $status_id, $county_id, $district_id, $project_type_id, $fecha_inicial, $fecha_fin);
      }
   }

   /**
    * ListarAcciones: Lista los permisos de un usuario de la BD
    *
    * @author Marcel
    */
   public function ListarAcciones($id)
   {
      $usuario = $this->getUser();
      $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 29);

      $acciones = '';

      if (count($permiso) > 0) {

         if ($permiso[0]['editar']) {
            $acciones .= '<a href="javascript:;" class="edit btn btn-icon btn-light-success btn-sm me-1" title="Edit record" data-id="' . $id . '"><i class="la la-edit fs-2"></i></a>';
         } else {

            $acciones .= '<a href="javascript:;" class="edit btn btn-icon btn-light-success btn-sm me-1" title="View record" data-id="' . $id . '"><i class="la la-eye fs-2"></i></a>';
         }

         if ($permiso[0]['eliminar']) {
            $acciones .= '<a href="javascript:;" class="delete btn btn-icon btn-light-danger btn-sm" title="Delete record" data-id="' . $id . '"><i class="la la-trash fs-2"></i></a>';
         }
      }

      return $acciones;
   }
}
