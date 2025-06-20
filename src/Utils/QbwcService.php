<?php

namespace App\Utils;

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
    public function UpdateSyncQueueQbwc()
    {
        $em = $this->getDoctrine()->getManager();

        $items = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
            ->ListarOrdenados('enviado');
        foreach ($items as $item) {
            $item->setEstado('sincronizado');
        }

        $em->flush();

    }

    // generar el xml del request
    public function GenerarRequestQBXML()
    {
        $qbxml = "";

        $items = $this->getDoctrine()->getRepository(SyncQueueQbwc::class)
            ->ListarOrdenados('pendiente');
        if (!empty($items)) {

            $item = $items[0];

            switch ($item->getTipo()) {
                case 'factura':
                    $qbxml = $this->generateInvoiceAddQBXML($item->getEntidadId());
                    break;
            }

            // actualizar estado
            $em = $this->getDoctrine()->getManager();

            $item->setEstado('enviado');

            $em->flush();
        }

        return $qbxml;
    }

    private function generateInvoiceAddQBXML(int $id): string
    {
        // Simulado: consulta y construye QBXML con tus datos reales
        return '<?xml version="1.0"?><QBXML><QBXMLMsgsRq onError="stopOnError">
            <InvoiceAddRq><InvoiceAdd>
                <CustomerRef><FullName>Juan Pérez</FullName></CustomerRef>
                <TxnDate>2024-06-20</TxnDate>
                <RefNumber>INV-001</RefNumber>
                <BillAddress><Addr1>Av. Central 123</Addr1><City>Santiago</City></BillAddress>
                <InvoiceLineAdd>
                    <ItemRef><FullName>Servicio</FullName></ItemRef>
                    <Desc>Consultoría</Desc>
                    <Quantity>2</Quantity>
                    <Rate>50000</Rate>
                </InvoiceLineAdd>
            </InvoiceAdd></InvoiceAddRq>
        </QBXMLMsgsRq></QBXML>';
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