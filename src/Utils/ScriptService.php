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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class ScriptService extends Base
{

    /**
     * CronAjustePrecio
     */
    public function CronAjustePrecio()
    {
        $em = $this->getDoctrine()->getManager();
        $fechaActual = $this->ObtenerFechaActual();

        // Ajustes activos para hoy
        $ajustes = $this->getDoctrine()
            ->getRepository(ProjectPriceAdjustment::class)
            ->ListarAjustesDeFecha($fechaActual);

        foreach ($ajustes as $ajuste) {
            $project = $ajuste->getProject();
            $projectId = $project->getProjectId();

            $percent = $ajuste->getPercent();       // p.ej. 2 => +2%
            if ($percent === 0.0) {
                continue; // nada que ajustar
            }

            // 1 + (2 / 100) = 1.02
            $factor = 1 + ($percent / 100);

            $items = $this->getDoctrine()
                ->getRepository(ProjectItem::class)
                ->ListarItemsDeProject($projectId);

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
                        $item->getItem()->getDescription(),
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
        $reminders = $this->getDoctrine()->getRepository(Reminder::class)
            ->ListarRemindersRangoFecha($fecha_actual, $fecha_actual, 1);
        foreach ($reminders as $reminder) {
            $reminder_id = $reminder->getReminderId();


            $asunto = $reminder->getSubject();
            $contenido = $reminder->getBody();

            // construir email
            $mensaje = (new TemplatedEmail())
                ->from(new Address($direccion_from, $from_name));

            // to
            $reminder_usuarios = $this->getDoctrine()->getRepository(ReminderRecipient::class)
                ->ListarUsuariosDeReminder($reminder_id);
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

            $project_item = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->BuscarItemProject($project_id, $item_id);
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
            $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->ListarProjectsDeItem($item_id);
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

        $from = $this->ObtenerFechaActual();
        // sumar 7 dias
        $to = new \DateTime();
        $to->modify('+7 days');
        $to = $to->format('Y-m-d');

        // listar projects
        $projects = $this->getDoctrine()->getRepository(Project::class)
            ->ListarProjectsParaNotificacionesDueDate($from, $to);

        // generar notificaciones
        $permisos_usuario = $this->getDoctrine()->getRepository(PermisoUsuario::class)
            ->ListarPermisosFuncion(9);
        foreach ($permisos_usuario as $permiso) {
            if ($permiso->getVer()) {
                foreach ($projects as $project) {
                    $notificacion = new Notification();

                    $dueDate = $project->getDueDate() != '' ? $project->getDueDate()->format('m/d/Y') : '';
                    $content = "Project " . $project->getProjectNumber() . " - " . $project->getName() . " is close to its due date " . $dueDate;
                    $notificacion->setContent($content);

                    $notificacion->setReaded(false);
                    $notificacion->setProject($project);
                    $notificacion->setUsuario($permiso->getUsuario());
                    $notificacion->setCreatedAt(new \DateTime());

                    $em->persist($notificacion);
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
            $items = $this->getDoctrine()->getRepository(DataTrackingItem::class)->ListarItems($data_tracking->getId());
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
}