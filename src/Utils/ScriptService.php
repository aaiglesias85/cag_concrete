<?php

namespace App\Utils;

use App\Entity\ConcreteVendor;
use App\Entity\County;
use App\Entity\DataTracking;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Estimate;
use App\Entity\EstimateCompany;
use App\Entity\Item;
use App\Entity\Notification;
use App\Entity\PermisoUsuario;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Entity\ProjectPriceAdjustment;
use App\Entity\Reminder;
use App\Entity\ReminderRecipient;
use App\Entity\Usuario;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Repository\NotificationRepository;
use App\Repository\ProjectRepository;
use App\Repository\UsuarioRepository;

class ScriptService extends Base
{

   /**
    * CronAjustePrecioConcreteVendor
    * - Sube el precio cuando hayan pasado N periodos desde updatedAtConcreteQuotePrice.
    * - Si updatedAtConcreteQuotePrice es null, usa startDate (o createdAt).
    */
   public function CronAjustePrecioConcreteVendor()
   {
      $em  = $this->getDoctrine()->getManager();
      $now = new \DateTimeImmutable('now');

      $this->writelog("CronAjustePrecioConcreteVendor: " . $now->format('Y-m-d H:i:s'));

      $projects = $em->getRepository(Project::class)->findAll();

      foreach ($projects as $project) {
         $price      = $project->getConcreteQuotePrice();             // decimal
         $escalator  = $project->getConcreteQuotePriceEscalator();    // decimal (fijo)
         $everyN     = $project->getConcreteTimePeriodEveryN();       // int
         $unit       = $project->getConcreteTimePeriodUnit();         // 'day'|'month'|'year'

         if (!$price || !$escalator || !$everyN || !$unit) {
            continue; // faltan datos
         }

         // Ancla para contar periodos
         $last = $project->getUpdatedAtConcreteQuotePrice();
         if (!$last) {
            continue;
         }

         // Normalizar a inmutable
         $last = $last instanceof \DateTimeImmutable ? $last : \DateTimeImmutable::createFromMutable(clone $last);

         // Intervalo segun unidad
         $step = $this->makeInterval($unit, (int) $everyN);
         if (!$step) {
            continue; // unidad desconocida
         }

         // ¿Cuántos periodos completos pasaron?
         [$periods, $lastBoundary] = $this->periodsPassed($last, $now, $step);

         if ($periods <= 0) {
            continue; // nada que hacer
         }

         // Aplicar incrementos (fijo). Si fuera %: $price *= pow(1+$escalator, $periods);
         $newPrice = round($price + ($escalator * $periods), 2);

         $project->setConcreteQuotePrice($newPrice);

         // Importante: mover updatedAt al último límite alcanzado (no a "ahora")
         $project->setUpdatedAtConcreteQuotePrice(\DateTime::createFromImmutable($lastBoundary));

         $this->writelog(sprintf(
            'Project %s: +%s x %d → %s (last=%s)',
            (string) $project->getProjectId(),
            number_format($escalator, 2, '.', ''),
            $periods,
            number_format($newPrice, 2, '.', ''),
            $lastBoundary->format('Y-m-d')
         ));
      }

      $em->flush();
   }

   /** Devuelve DateInterval según unidad/eachN */
   private function makeInterval(string $unit, int $n): ?\DateInterval
   {
      $n = max(1, $n);
      switch (strtolower($unit)) {
         case 'day':
         case 'days':
            return new \DateInterval('P' . $n . 'D');
         case 'month':
         case 'months':
            return new \DateInterval('P' . $n . 'M');
         case 'year':
         case 'years':
            return new \DateInterval('P' . $n . 'Y');
         default:
            return null;
      }
   }

   /**
    * Cuenta periodos completos desde $from hasta $to y devuelve:
    *  - [0] cantidad de periodos
    *  - [1] última frontera alcanzada (from + k*step <= to)
    *
    * @return array{0:int,1:\DateTimeImmutable}
    */
   private function periodsPassed(\DateTimeImmutable $from, \DateTimeImmutable $to, \DateInterval $step): array
   {
      if ($to <= $from) {
         return [0, $from];
      }
      $count  = 0;
      $cursor = $from;
      while (true) {
         $next = $cursor->add($step);
         if ($next > $to) {
            break;
         }
         $cursor = $next;
         $count++;
         if ($count > 1000) {
            break;
         } // guarda por si acaso
      }
      return [$count, $cursor];
   }


   /**
    * DefinirItemPrincipal
    */
   public function DefinirItemPrincipal()
   {
      $em = $this->getDoctrine()->getManager();

      $projects = $this->getDoctrine()->getRepository(Project::class)->findAll();
      foreach ($projects as $project) {

         /** @var \App\Repository\ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $items = $projectItemRepo->ListarItemsDeProject($project->getProjectId());

         // itemId => ya tiene principal
         $seenByItem = [];
         foreach ($items as $pi) {
            $itemId = $pi->getItem()->getItemId();
            if (!isset($seenByItem[$itemId])) {
               $pi->setPrincipal(true);
               $seenByItem[$itemId] = true;
            } else {
               $pi->setPrincipal(false);
            }
         }
      }

      $em->flush();
   }

   /**
    * CronAjustePrecio
    */
   public function CronAjustePrecio()
   {
      $em = $this->getDoctrine()->getManager();
      $fechaActual = $this->ObtenerFechaActual();

      $this->writelog("CronAjustePrecio: " . $fechaActual);

      // Ajustes activos para hoy
      /** @var \App\Repository\ProjectPriceAdjustmentRepository $projectPriceAdjustmentRepo */
      $projectPriceAdjustmentRepo = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class);
      $ajustes = $projectPriceAdjustmentRepo->ListarAjustesDeFecha($fechaActual);

      foreach ($ajustes as $ajuste) {
         $project = $ajuste->getProject();
         $projectId = $project->getProjectId();

         $percent = $ajuste->getPercent();       // p.ej. 2 => +2%
         if ($percent === 0.0) {
            continue; // nada que ajustar
         }

         // 1 + (2 / 100) = 1.02
         $factor = 1 + ($percent / 100);

         /** @var \App\Repository\ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $items = $projectItemRepo->ListarItemsDeProject($projectId);

         $notas = []; // acumular notas por proyecto

         foreach ($items as $item) {
            $precioOld = (float)$item->getPrice();
            $precioNew = round($precioOld * $factor, 2);  // 2 decimales típico de moneda

            // evitar escribir si no cambia (o si el precio es nulo)
            if ($precioNew === $precioOld) {
               continue;
            }

            $item->setPrice($precioNew);
            $item->setPriceOld($precioOld);

            $notas[] = [
               'notes' => sprintf(
                  'Change Price Item: %s, Percent: %s%%, Previous Price: %.2f, New Price: %.2f',
                  $item->getItem()->getName(),
                  // mostrar 2 decimales como máximo, sin ceros sobrantes
                  rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.'),
                  $precioOld,
                  $precioNew
               ),
               'date' => new \DateTime()
            ];
         }

         if (!empty($notas)) {
            $this->SalvarNotesUpdate($project, $notas); // guardar una sola vez por proyecto
         }
      }

      $em->flush();
   }


   /**
    * DefinirCompanyEstimate
    */
   public function DefinirCompanyEstimate()
   {
      $em = $this->getDoctrine()->getManager();

      // estimates
      $estimates = $this->getDoctrine()->getRepository(Estimate::class)->findAll();
      foreach ($estimates as $estimate) {
         $company = $estimate->getCompany();
         $contact = $estimate->getContact();

         if ($company !== null || $contact !== null) {
            $estimate_company = new EstimateCompany();

            $estimate_company->setCompany($company);
            $estimate_company->setContact($contact);
            $estimate_company->setEstimate($estimate);

            $em->persist($estimate_company);
         }
      }

      $em->flush();
   }

   /**
    * DefinirCountyProjectEstimate
    */
   public function DefinirCountyProjectEstimate()
   {
      $em = $this->getDoctrine()->getManager();

      // projects
      $projects = $this->getDoctrine()->getRepository(Project::class)->findAll();
      foreach ($projects as $project) {
         $county = $project->getCounty();
         $county_entity = $this->CrearCounty($county);
         $project->setCountyObj($county_entity);
      }

      // estimates
      $estimates = $this->getDoctrine()->getRepository(Estimate::class)->findAll();
      foreach ($estimates as $estimate) {
         $county = $estimate->getCounty();
         $county_entity = $this->CrearCounty($county);
         $estimate->setCountyObj($county_entity);
      }

      $em->flush();
   }

   private function CrearCounty($descripcion)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $county = $this->getDoctrine()->getRepository(County::class)
         ->findOneBy(['description' => $descripcion]);
      if ($county == null) {
         $county = new County();
         $county->setDescription($descripcion);
         $county->setStatus(1);
         $em->persist($county);
         $em->flush();
      }


      return $county;
   }

   /**
    * CronReminders
    */
   public function CronReminders()
   {

      //Enviar email
      $direccion_url = $this->ObtenerURL();
      $direccion_from = $this->getParameter('mailer_sender_address');
      $from_name = $this->getParameter('mailer_from_name');

      $fecha_actual = $this->ObtenerFechaActual('m/d/Y');

      // listar reminders
      /** @var \App\Repository\ReminderRepository $reminderRepo */
      $reminderRepo = $this->getDoctrine()->getRepository(Reminder::class);
      $reminders = $reminderRepo->ListarRemindersRangoFecha($fecha_actual, $fecha_actual, 1);
      foreach ($reminders as $reminder) {
         $reminder_id = $reminder->getReminderId();


         $asunto = $reminder->getSubject();
         $contenido = $reminder->getBody();

         // construir email
         $mensaje = (new TemplatedEmail())
            ->from(new Address($direccion_from, $from_name));

         // to
         /** @var \App\Repository\ReminderRecipientRepository $reminderRecipientRepo */
         $reminderRecipientRepo = $this->getDoctrine()->getRepository(ReminderRecipient::class);
         $reminder_usuarios = $reminderRecipientRepo->ListarUsuariosDeReminder($reminder_id);
         foreach ($reminder_usuarios as $reminder_usuario) {
            $mensaje->addTo(new Address($reminder_usuario->getUser()->getEmail(), $reminder_usuario->getUser()->getNombreCompleto()));
         }

         // resto mensaje
         $mensaje
            ->subject($asunto)
            ->htmlTemplate('mailing/mail.html.twig')
            ->context([
               'direccion_url' => $direccion_url,
               'asunto' => $asunto,
               'contenido' => $contenido,
            ]);

         $this->mailer->send($mensaje);
      }
   }

   /**
    * DefinirConcreteVendorDataTracking
    */
   public function DefinirConcreteVendorDataTracking()
   {
      $em = $this->getDoctrine()->getManager();

      // listar datatracking
      $data_trackings_conc_vendor = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)->findAll();
      foreach ($data_trackings_conc_vendor as $data_tracking_conc_vendor) {

         $conc_vendor = $data_tracking_conc_vendor->getConcVendor();
         $concrete_vendor = $this->SalvarConcreteVendor($conc_vendor);
         if ($concrete_vendor) {
            $data_tracking_conc_vendor->setConcVendor(NULL);
            $data_tracking_conc_vendor->setConcreteVendor($concrete_vendor);
         }
      }

      $em->flush();
   }

   private function SalvarConcreteVendor($name)
   {
      $em = $this->getDoctrine()->getManager();

      //Verificar name
      $entity = $this->getDoctrine()->getRepository(ConcreteVendor::class)
         ->findOneBy(['name' => $name]);
      if ($entity === null) {

         $entity = new ConcreteVendor();

         $entity->setName($name);
         $entity->setPhone("");
         $entity->setAddress("");
         $entity->setContactName("");
         $entity->setContactEmail("");

         $em->persist($entity);
         $em->flush();
      }
      return $entity;
   }

   /**
    * DefinirSubcontractorDatatrackingProjectItem
    */
   public function DefinirSubcontractorDatatrackingProjectItem()
   {
      $em = $this->getDoctrine()->getManager();

      // listar datatracking subcontractor
      $datatrackings_subcontractors = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class)
         ->findAll();
      foreach ($datatrackings_subcontractors as $datatracking_subcontractor) {
         $project_id = $datatracking_subcontractor->getDataTracking()->getProject()->getProjectId();
         $item_id = $datatracking_subcontractor->getItem() ? $datatracking_subcontractor->getItem()->getItemId() : '';

         /** @var \App\Repository\ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $project_item = $projectItemRepo->BuscarItemProject($project_id, $item_id);
         if (!empty($project_item)) {
            $datatracking_subcontractor->setProjectItem($project_item[0]);
            $datatracking_subcontractor->setItem(null);
         } else {
            $em->remove($datatracking_subcontractor);
         }
      }

      $em->flush();
   }

   /**
    * DefinirYieldCalculationItem
    */
   public function DefinirYieldCalculationItem()
   {
      $em = $this->getDoctrine()->getManager();

      // listar items
      $items = $this->getDoctrine()->getRepository(Item::class)
         ->findAll();
      foreach ($items as $item) {
         $item_id = $item->getItemId();

         $yield_calculation = $item->getYieldCalculation();
         $equation = $item->getEquation();

         // actualizar en proyectos
         /** @var \App\Repository\ProjectItemRepository $projectItemRepo */
         $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);
         $project_items = $projectItemRepo->ListarProjectsDeItem($item_id);
         foreach ($project_items as $project_item) {
            $project_item->setYieldCalculation($yield_calculation);
            $project_item->setEquation($equation);
         }
      }

      $em->flush();
   }

   /**
    * DefinirNotificacionesDueDate
    */
   public function DefinirNotificacionesDueDate()
   {
      $em = $this->getDoctrine()->getManager();

      /** @var ProjectRepository $projectRepo */
      $projectRepo = $this->getDoctrine()->getRepository(Project::class);
      /** @var UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      /** @var NotificationRepository $notificationRepo */
      $notificationRepo = $this->getDoctrine()->getRepository(Notification::class);

      $usuarios = $usuarioRepo->ListarUsuariosRoles([3, 4]);
      if (empty($usuarios)) {
         return;
      }

      $baseDate = \DateTimeImmutable::createFromFormat('Y-m-d', $this->ObtenerFechaActual()) ?: new \DateTimeImmutable('today');
      $avisos = [10, 5];

      $direccion_url = $this->ObtenerURL();
      $direccion_from = $this->getParameter('mailer_sender_address');
      $from_name = $this->getParameter('mailer_from_name');

      foreach ($avisos as $diasAntes) {
         // Calcular la fecha objetivo: hoy + X días (el due date que queremos encontrar)
         $objetivo = $baseDate->modify(sprintf('+%d days', $diasAntes));
         $objetivoStr = $objetivo->format('Y-m-d');

         // Buscar proyectos cuyo due date sea exactamente la fecha objetivo
         $projects = $projectRepo->ListarProjectsParaNotificacionesDueDate($objetivoStr, $objetivoStr);

         if (empty($projects)) {
            continue;
         }

         foreach ($projects as $project) {
            $dueDate = $project->getDueDate();
            if (!$dueDate instanceof \DateTimeInterface) {
               continue;
            }

            // Convertir dueDate a DateTimeImmutable para comparación
            $dueDateImmutable = $dueDate instanceof \DateTimeImmutable
               ? $dueDate
               : \DateTimeImmutable::createFromMutable($dueDate instanceof \DateTime ? $dueDate : \DateTime::createFromInterface($dueDate));

            // Calcular diferencia en días
            $diff = $baseDate->diff($dueDateImmutable);
            $diasDiferencia = (int)$diff->days;

            // Si el due date está en el pasado, hacer el número negativo
            if ($dueDateImmutable < $baseDate) {
               $diasDiferencia = -$diasDiferencia;
            }

            // Validar que el due date esté exactamente a X días desde hoy (con tolerancia de ±1 día)
            // Esto asegura que solo notificamos sobre proyectos con due date próximo
            if (abs($diasDiferencia - $diasAntes) > 1) {
               continue;
            }

            // No notificar sobre proyectos muy vencidos (más de 30 días en el pasado)
            // Esto evita spam de notificaciones sobre proyectos antiguos
            if ($diasDiferencia < -30) {
               continue;
            }

            $content = $this->buildDueDateNotificationContent($project, $dueDate, $diasAntes);

            foreach ($usuarios as $usuario) {
               if (!$usuario->getEmail()) {
                  continue;
               }

               if ($this->dueDateNotificationExists($notificationRepo, $usuario, $project, $content)) {
                  continue;
               }

               $notificacion = new Notification();
               $notificacion->setContent($content);
               $notificacion->setReaded(false);
               $notificacion->setProject($project);
               $notificacion->setUsuario($usuario);
               $notificacion->setCreatedAt(new \DateTime());

               $em->persist($notificacion);

               $this->enviarRecordatorioDueDate(
                  $usuario,
                  $project,
                  $diasAntes,
                  $dueDate,
                  $direccion_url,
                  $direccion_from,
                  $from_name,
                  $content
               );

               $this->writelog("Enviado recordatorio due date a " . $usuario->getEmail() . " para el proyecto " . $project->getProjectId());
            }
         }
      }

      $em->flush();
   }


   /**
    * DefinirPendingDataTracking
    */
   public function DefinirPendingDataTracking()
   {
      $em = $this->getDoctrine()->getManager();

      // listar datatracking
      $data_trackings = $this->getDoctrine()->getRepository(DataTracking::class)
         ->findAll();
      foreach ($data_trackings as $data_tracking) {


         $pending = false;
         /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
         $dataTrackingItemRepo = $this->getDoctrine()->getRepository(DataTrackingItem::class);
         $items = $dataTrackingItemRepo->ListarItems($data_tracking->getId());
         foreach ($items as $value) {
            if ($value->getQuantity() == 0) {
               $pending = true;
            }
         }

         // pending
         $data_tracking->setPending($pending);
      }

      $em->flush();
   }

   private function dueDateNotificationExists(NotificationRepository $notificationRepo, Usuario $usuario, Project $project, string $content): bool
   {
      return (bool)$notificationRepo->findOneBy([
         'usuario' => $usuario,
         'project' => $project,
         'content' => $content,
      ]);
   }

   private function buildDueDateNotificationContent(Project $project, \DateTimeInterface $dueDate, int $diasAntes): string
   {
      $projectNumber = trim((string)$project->getProjectNumber());
      $projectName = trim((string)$project->getName());
      $label = trim($projectNumber . ($projectName ? ' - ' . $projectName : ''));

      if ($label === '') {
         $label = 'Project #' . $project->getProjectId();
      }

      return sprintf(
         '%s invoice due date %s (in %d days)',
         $label,
         $dueDate->format('m/d/Y'),
         $diasAntes
      );
   }

   private function enviarRecordatorioDueDate(
      Usuario $usuario,
      Project $project,
      int $diasAntes,
      \DateTimeInterface $dueDate,
      string $direccionUrl,
      string $emailFrom,
      string $fromName,
      string $contenido
   ): void {
      $projectNumber = trim((string)$project->getProjectNumber());
      $subject = sprintf(
         'Invoice due date reminder (%d days) - %s',
         $diasAntes,
         $projectNumber !== '' ? $projectNumber : 'Project #' . $project->getProjectId()
      );

      $mensaje = (new TemplatedEmail())
         ->from(new Address($emailFrom, $fromName))
         ->to(new Address($usuario->getEmail(), trim($usuario->getNombreCompleto())))
         ->subject($subject)
         ->htmlTemplate('mailing/invoice-due-date-reminder.html.twig')
         ->context([
            'asunto' => $subject,
            'direccion_url' => $direccionUrl,
            'usuario_nombre' => trim($usuario->getNombreCompleto()),
            'project_number' => $projectNumber,
            'project_name' => trim((string)$project->getName()),
            'project_id' => $project->getProjectId(),
            'mensaje_resumen' => $contenido,
            'due_date' => $dueDate->format('m/d/Y'),
            'dias_restantes' => $diasAntes,
         ]);

      $this->mailer->send($mensaje);
   }
}
