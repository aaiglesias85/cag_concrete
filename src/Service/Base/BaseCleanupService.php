<?php

namespace App\Service\Base;

use App\Entity\DataTrackingAttachment;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\EstimateEstimator;
use App\Entity\InvoiceAttachment;
use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemNotes;
use App\Entity\InvoiceNotes;
use App\Entity\Log;
use App\Entity\Notification;
use App\Entity\PermisoUsuario;
use App\Entity\ProjectItemHistory;
use App\Entity\ReminderRecipient;
use App\Entity\SyncQueueQbwc;
use App\Entity\UserQbwcToken;
use Doctrine\Persistence\ManagerRegistry;

class BaseCleanupService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
    ) {
    }

    public function EliminarInformacionDeUsuario($usuario_id): void
    {
        $em = $this->doctrine->getManager();

        /** @var \App\Repository\PermisoUsuarioRepository $permisoUsuarioRepo */
        $permisoUsuarioRepo = $this->doctrine->getRepository(PermisoUsuario::class);
        $permisos_usuario = $permisoUsuarioRepo->ListarPermisosUsuario($usuario_id);
        foreach ($permisos_usuario as $permiso_usuario) {
            $em->remove($permiso_usuario);
        }

        /** @var \App\Repository\LogRepository $logRepo */
        $logRepo = $this->doctrine->getRepository(Log::class);
        $logs = $logRepo->ListarLogsDeUsuario($usuario_id);
        foreach ($logs as $log) {
            $em->remove($log);
        }

        /** @var \App\Repository\NotificationRepository $notificationRepo */
        $notificationRepo = $this->doctrine->getRepository(Notification::class);
        $notificaciones = $notificationRepo->ListarNotificationsDeUsuario($usuario_id);
        foreach ($notificaciones as $notificacion) {
            $em->remove($notificacion);
        }

        /** @var \App\Repository\ReminderRecipientRepository $reminderRecipientRepo */
        $reminderRecipientRepo = $this->doctrine->getRepository(ReminderRecipient::class);
        $reminders = $reminderRecipientRepo->ListarRemindersDeUsuario($usuario_id);
        foreach ($reminders as $reminder) {
            $em->remove($reminder);
        }

        /** @var \App\Repository\EstimateEstimatorRepository $estimateEstimatorRepo */
        $estimateEstimatorRepo = $this->doctrine->getRepository(EstimateEstimator::class);
        $estimates = $estimateEstimatorRepo->ListarEstimatesDeUsuario($usuario_id);
        foreach ($estimates as $estimate) {
            $em->remove($estimate);
        }

        /** @var \App\Repository\UserQbwcTokenRepository $userQbwcTokenRepo */
        $userQbwcTokenRepo = $this->doctrine->getRepository(UserQbwcToken::class);
        $qbwc_tokens = $userQbwcTokenRepo->ListarTokensDeUsuario($usuario_id);
        foreach ($qbwc_tokens as $qbwc_token) {
            $em->remove($qbwc_token);
        }

        /** @var \App\Repository\ProjectItemHistoryRepository $historyRepo */
        $historyRepo = $this->doctrine->getRepository(ProjectItemHistory::class);
        $historial = $historyRepo->ListarHistorialDeUsuario($usuario_id);
        foreach ($historial as $historial_item) {
            $em->remove($historial_item);
        }
    }

    public function EliminarInformacionRelacionadaDataTracking($data_tracking_id): void
    {
        $em = $this->doctrine->getManager();

        /** @var \App\Repository\DataTrackingConcVendorRepository $dataTrackingConcVendorRepo */
        $dataTrackingConcVendorRepo = $this->doctrine->getRepository(DataTrackingConcVendor::class);
        $conc_vendors = $dataTrackingConcVendorRepo->ListarConcVendor($data_tracking_id);
        foreach ($conc_vendors as $conc_vendor) {
            $em->remove($conc_vendor);
        }

        /** @var \App\Repository\DataTrackingItemRepository $dataTrackingItemRepo */
        $dataTrackingItemRepo = $this->doctrine->getRepository(DataTrackingItem::class);
        $items = $dataTrackingItemRepo->ListarItems($data_tracking_id);
        foreach ($items as $item) {
            $em->remove($item);
        }

        /** @var \App\Repository\DataTrackingLaborRepository $dataTrackingLaborRepo */
        $dataTrackingLaborRepo = $this->doctrine->getRepository(DataTrackingLabor::class);
        $data_tracking_labors = $dataTrackingLaborRepo->ListarLabor($data_tracking_id);
        foreach ($data_tracking_labors as $data_tracking_labor) {
            $em->remove($data_tracking_labor);
        }

        /** @var \App\Repository\DataTrackingMaterialRepository $dataTrackingMaterialRepo */
        $dataTrackingMaterialRepo = $this->doctrine->getRepository(DataTrackingMaterial::class);
        $data_tracking_materials = $dataTrackingMaterialRepo->ListarMaterials($data_tracking_id);
        foreach ($data_tracking_materials as $data_tracking_material) {
            $em->remove($data_tracking_material);
        }

        /** @var \App\Repository\DataTrackingSubcontractRepository $dataTrackingSubcontractRepo */
        $dataTrackingSubcontractRepo = $this->doctrine->getRepository(DataTrackingSubcontract::class);
        $subcontract_items = $dataTrackingSubcontractRepo->ListarSubcontracts($data_tracking_id);
        foreach ($subcontract_items as $subcontract_item) {
            $em->remove($subcontract_item);
        }

        $dir = 'uploads/datatracking/';
        /** @var \App\Repository\DataTrackingAttachmentRepository $dataTrackingAttachmentRepo */
        $dataTrackingAttachmentRepo = $this->doctrine->getRepository(DataTrackingAttachment::class);
        $attachments = $dataTrackingAttachmentRepo->ListarAttachmentsDeDataTracking($data_tracking_id);
        foreach ($attachments as $attachment) {
            $file_eliminar = $attachment->getFile();
            if ('' != $file_eliminar && is_file($dir.$file_eliminar)) {
                unlink($dir.$file_eliminar);
            }

            $em->remove($attachment);
        }
    }

    public function EliminarInformacionDeInvoice($invoice_id): void
    {
        $em = $this->doctrine->getManager();

        /** @var \App\Repository\InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->doctrine->getRepository(InvoiceItem::class);
        $items = $invoiceItemRepo->ListarItems($invoice_id);
        foreach ($items as $item) {
            /** @var \App\Repository\InvoiceItemNotesRepository $invoiceItemNotesRepo */
            $invoiceItemNotesRepo = $this->doctrine->getRepository(InvoiceItemNotes::class);
            $notes = $invoiceItemNotesRepo->ListarNotesDeItemInvoice($item->getId());
            foreach ($notes as $note) {
                $em->remove($note);
            }

            $em->remove($item);
        }

        /** @var \App\Repository\SyncQueueQbwcRepository $syncQueueQbwcRepo */
        $syncQueueQbwcRepo = $this->doctrine->getRepository(SyncQueueQbwc::class);
        $quickbooks = $syncQueueQbwcRepo->ListarRegistrosDeEntidadId('invoice', $invoice_id);
        foreach ($quickbooks as $quickbook) {
            $em->remove($quickbook);
        }

        /** @var \App\Repository\InvoiceNotesRepository $invoiceNotesRepo */
        $invoiceNotesRepo = $this->doctrine->getRepository(InvoiceNotes::class);
        $notes = $invoiceNotesRepo->ListarNotesDeInvoice($invoice_id);
        foreach ($notes as $note) {
            $em->remove($note);
        }

        $dir = 'uploads/invoice/';
        /** @var \App\Repository\InvoiceAttachmentRepository $invoiceAttachmentRepo */
        $invoiceAttachmentRepo = $this->doctrine->getRepository(InvoiceAttachment::class);
        $attachments = $invoiceAttachmentRepo->ListarAttachmentsDeInvoice($invoice_id);
        foreach ($attachments as $attachment) {
            $file_eliminar = $attachment->getFile();
            if ('' != $file_eliminar && is_file($dir.$file_eliminar)) {
                unlink($dir.$file_eliminar);
            }

            $em->remove($attachment);
        }
    }
}
