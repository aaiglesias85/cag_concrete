<?php

namespace App\Service\Base;

use App\Entity\Log;
use App\Entity\Usuario;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class BaseApplicationLogService
{
    public function __construct(
        private readonly ManagerRegistry $doctrine,
        private readonly Security $security,
        private readonly BaseTextNormalizationService $textNormalization,
    ) {
    }

    public function SalvarLog($operacion, $categoria, $descripcion): void
    {
        $usuario = $this->security->getUser();

        if (null != $usuario) {
            $em = $this->doctrine->getManager();

            $entity = new Log();

            $entity->setOperacion($operacion);
            $entity->setCategoria($categoria);
            $entity->setDescripcion($this->textNormalization->normalizarTextoParaDb($descripcion));

            $entity->setIp($this->resolveClientIp());

            $entity->setUsuario($usuario instanceof Usuario ? $usuario : null);

            $entity->setFecha(new \DateTime());

            $em->persist($entity);
            $em->flush();
        }
    }

    private function resolveClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return (string) $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return (string) $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    }
}
