<?php

namespace App\Utils\Admin;

use App\Entity\Reminder;
use App\Entity\ReminderRecipient;
use App\Entity\Usuario;
use App\Repository\ReminderRecipientRepository;
use App\Repository\ReminderRepository;
use App\Utils\Base;

class ReminderService extends Base
{
   /**
    * CargarDatosReminder: Carga los datos de un reminder
    *
    * @param int $reminder_id Id
    *
    * @author Marcel
    */
   public function CargarDatosReminder($reminder_id)
   {
      $resultado = array();
      $arreglo_resultado = array();

      $entity = $this->getDoctrine()->getRepository(Reminder::class)
         ->find($reminder_id);
      /** @var Reminder $entity */
      if ($entity != null) {

         $arreglo_resultado['subject'] = $entity->getSubject();
         $arreglo_resultado['body'] = $entity->getBody();
         $arreglo_resultado['status'] = $entity->getStatus();
         $arreglo_resultado['day'] = $entity->getDay()->format('m/d/Y');

         // destinatarios
         $destinatarios = $this->ListarDestinatarios($reminder_id);
         $arreglo_resultado['destinatarios'] = $destinatarios;

         $resultado['success'] = true;
         $resultado['reminder'] = $arreglo_resultado;
      }

      return $resultado;
   }

   // listar los usuarios del reminder
   private function ListarDestinatarios($reminder_id)
   {
      $destinatarios = [];

      /** @var ReminderRecipientRepository $reminderRecipientRepo */
      $reminderRecipientRepo = $this->getDoctrine()->getRepository(ReminderRecipient::class);
      $reminder_usuarios = $reminderRecipientRepo->ListarUsuariosDeReminder($reminder_id);
      foreach ($reminder_usuarios as $reminder_usuario) {
         $destinatarios[] = [
            'usuario_id' => $reminder_usuario->getUser()->getUsuarioId(),
            'nombre' => $reminder_usuario->getUser()->getNombreCompleto(),
            'email' => $reminder_usuario->getUser()->getEmail(),
         ];
      }

      return $destinatarios;
   }

   /**
    * EliminarReminder: Elimina un rol en la BD
    * @param int $reminder_id Id
    * @author Marcel
    */
   public function EliminarReminder($reminder_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Reminder::class)
         ->find($reminder_id);
      /**@var Reminder $entity */
      if ($entity != null) {

         // eliminar informacion relacionada
         $this->EliminarInformacionRelacionada($reminder_id);

         $reminder_descripcion = $entity->getSubject();


         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Reminder";
         $log_descripcion = "The reminder is deleted: $reminder_descripcion";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * EliminarReminders: Elimina los reminders seleccionados en la BD
    * @param int $ids Ids
    * @author Marcel
    */
   public function EliminarReminders($ids)
   {
      $em = $this->getDoctrine()->getManager();

      if ($ids != "") {
         $ids = explode(',', $ids);
         $cant_eliminada = 0;
         $cant_total = 0;
         foreach ($ids as $reminder_id) {
            if ($reminder_id != "") {
               $cant_total++;
               $entity = $this->getDoctrine()->getRepository(Reminder::class)
                  ->find($reminder_id);
               /** @var Reminder $entity */
               if ($entity != null) {

                  // eliminar informacion relacionada
                  $this->EliminarInformacionRelacionada($reminder_id);

                  $reminder_descripcion = $entity->getSubject();

                  $em->remove($entity);
                  $cant_eliminada++;

                  //Salvar log
                  $log_operacion = "Delete";
                  $log_categoria = "Reminder";
                  $log_descripcion = "The reminder is deleted: $reminder_descripcion";
                  $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
               }
            }
         }
      }
      $em->flush();

      if ($cant_eliminada == 0) {
         $resultado['success'] = false;
         $resultado['error'] = "The reminders could not be deleted, because they are associated with a invoice";
      } else {
         $resultado['success'] = true;

         $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected reminders because they are associated with a invoice";
         $resultado['message'] = $mensaje;
      }

      return $resultado;
   }

   // eliminar informacion relacionada
   private function EliminarInformacionRelacionada($reminder_id)
   {
      $em = $this->getDoctrine()->getManager();

      // usuarios
      /** @var ReminderRecipientRepository $reminderRecipientRepo */
      $reminderRecipientRepo = $this->getDoctrine()->getRepository(ReminderRecipient::class);
      $reminder_usuarios = $reminderRecipientRepo->ListarUsuariosDeReminder($reminder_id);
      foreach ($reminder_usuarios as $reminder_usuario) {
         $em->remove($reminder_usuario);
      }
   }

   /**
    * ActualizarReminder: Actuializa los datos del rol en la BD
    * @param int $reminder_id Id
    * @author Marcel
    */
   public function ActualizarReminder($reminder_id, $day, $subject, $body, $status, $usuarios_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(Reminder::class)
         ->find($reminder_id);
      /** @var Reminder $entity */
      if ($entity != null) {

         $entity->setSubject($subject);
         $entity->setBody($body);
         $entity->setStatus($status);

         if ($day != '') {
            $day = \DateTime::createFromFormat('m/d/Y', $day);
            $entity->setDay($day);
         }

         // salvar destinatario
         $this->SalvarDestinatarios($entity, $usuarios_id, false);

         $em->flush();

         //Salvar log
         $log_operacion = "Update";
         $log_categoria = "Reminder";
         $log_descripcion = "The reminder is modified: $subject, Date: " . $day->format('m/d/Y');
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
         $resultado['reminder_id'] = $entity->getReminderId();

         return $resultado;
      }
   }

   /**
    * SalvarReminder: Guarda los datos de reminder en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarReminder($day, $subject, $body, $status, $usuarios_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = new Reminder();

      $entity->setSubject($subject);
      $entity->setBody($body);
      $entity->setStatus($status);

      if ($day != '') {
         $day = \DateTime::createFromFormat('m/d/Y', $day);
         $entity->setDay($day);
      }

      $em->persist($entity);

      // salvar contactos
      $this->SalvarDestinatarios($entity, $usuarios_id);

      $em->flush();

      //Salvar log
      $log_operacion = "Add";
      $log_categoria = "Reminder";
      $log_descripcion = "The reminder is added: $subject, Date: " . $day->format('m/d/Y');
      $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

      $resultado['success'] = true;
      $resultado['reminder_id'] = $entity->getReminderId();

      return $resultado;
   }


   // salvar destinatarios
   public function SalvarDestinatarios($entity, $usuarios_id, $is_new = true)
   {
      $em = $this->getDoctrine()->getManager();

      // eliminar anteriores
      if (!$is_new) {
         /** @var ReminderRecipientRepository $reminderRecipientRepo */
         $reminderRecipientRepo = $this->getDoctrine()->getRepository(ReminderRecipient::class);
         $reminder_usuarios = $reminderRecipientRepo->ListarUsuariosDeReminder($entity->getReminderId());
         foreach ($reminder_usuarios as $reminder_usuario) {
            $em->remove($reminder_usuario);
         }
      }

      $usuarios_id = $usuarios_id !== '' ? explode(',', $usuarios_id) : [];

      if (!empty($usuarios_id)) {
         foreach ($usuarios_id as $usuario_id) {
            $usuario_entity = $this->getDoctrine()->getRepository(Usuario::class)
               ->find($usuario_id);
            if ($usuario_entity !== null) {
               $reminder_usuario_entity = new ReminderRecipient();

               $reminder_usuario_entity->setReminder($entity);
               $reminder_usuario_entity->setUser($usuario_entity);

               $em->persist($reminder_usuario_entity);
            }
         }
      }
   }


   /**
    * ListarReminders: Listar los reminders
    *
    * @param int $start Inicio
    * @param int $limit Limite
    * @param string $sSearch Para buscar
    *
    * @author Marcel
    */
   public function ListarReminders($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin)
   {
      /** @var ReminderRepository $reminderRepo */
      $reminderRepo = $this->getDoctrine()->getRepository(Reminder::class);
      $resultado = $reminderRepo->ListarRemindersConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin);

      $data = [];

      foreach ($resultado['data'] as $value) {
         $reminder_id = $value->getReminderId();

         // destinatarios
         $destinatarios = $this->ListarDestinatariosEmail($reminder_id);

         $destinatarios_html = '';
         foreach (array_chunk($destinatarios, 3) as $grupo) {
            $destinatarios_html .= implode(', ', $grupo) . '<br>';
         }

         $data[] = array(
            "id" => $reminder_id,
            "day" => $value->getDay()->format('m/d/Y'),
            "subject" => $value->getSubject(),
            "body" => $value->getBody(),
            "status" => $value->getStatus() ? 1 : 0,
            "destinatarios" => $destinatarios_html,
         );
      }

      return [
         'data' => $data,
         'total' => $resultado['total'], // ya viene con el filtro aplicado
      ];
   }

   // listar los destinatarios
   private function ListarDestinatariosEmail($reminder_id)
   {
      $emails = [];

      /** @var ReminderRecipientRepository $reminderRecipientRepo */
      $reminderRecipientRepo = $this->getDoctrine()->getRepository(ReminderRecipient::class);
      $reminder_usuarios = $reminderRecipientRepo->ListarUsuariosDeReminder($reminder_id);
      foreach ($reminder_usuarios as $reminder_usuario) {
         $emails[] = $reminder_usuario->getUser()->getEmail();
      }

      return $emails;
   }
}
