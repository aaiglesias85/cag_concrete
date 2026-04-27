<?php

namespace App\Service\Base;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BaseFileLogService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ContainerBagInterface $containerBag,
    ) {
    }

    public function writelog($txt, $filename = 'weblog.txt')
    {
        global $path_logs;

        $datetime = date('Y-m-d H:i:s', time());
        $txt = $datetime."\t---\t".$txt."\n";

        if ('' != $path_logs) {
            $filename = $path_logs.$filename;
        }

        $fp = fopen($filename, 'a');
        if ($fp) {
            fputs($fp, $txt, strlen($txt));
            fclose($fp);
        } else {
            exit("Error IO: writing file '{$filename}'");
        }

        $this->logger->info($txt);
    }

    /**
     * Escribe siempre en public/{filename} (ruta absoluta vía kernel.project_dir).
     */
    public function writelogPublic(string $txt, string $filename = 'weblog.txt'): void
    {
        $dir = rtrim((string) $this->containerBag->get('kernel.project_dir'), '/\\').DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR;
        $fullPath = $dir.$filename;
        $datetime = date('Y-m-d H:i:s', time());
        $line = $datetime."\t---\t".$txt."\n";
        $fp = @fopen($fullPath, 'a');
        if ($fp) {
            fputs($fp, $line, strlen($line));
            fclose($fp);
        } else {
            $this->logger->warning('writelogPublic: no se pudo escribir '.$fullPath);
        }
        $this->logger->info($line);
    }

    public function HacerUrl($tex)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $tex);
        $slug = preg_replace_callback("/[^a-zA-Z0-9\/_|+ \-\.\$]/", function ($c) {
            return '';
        }, $slug);
        $slug = strtolower(trim($slug, '-'));
        $slug = preg_replace_callback("/[^a-zA-Z0-9\/_|+ \-\.\$]/", function ($c) {
            return '-';
        }, $slug);

        return $slug;
    }

    public function getSize($archivo)
    {
        $size = 0;
        if (is_file($archivo)) {
            $size = filesize($archivo);
        }

        return $size;
    }

    public function upload(UploadedFile $file, $dir, $aceptedExtensions = [])
    {
        $fileName = '';

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if ('' == $extension) {
            $extension = 'msg';
        }

        $extension_valida = true;
        if (!empty($aceptedExtensions)) {
            $aceptedExtensions = array_map('strtolower', $aceptedExtensions);
            $extension_valida = in_array($extension, $aceptedExtensions);
        }

        if ($extension_valida) {
            $safeFilename = $this->HacerUrl($originalFilename);
            $fileName = $safeFilename.'.'.$extension;

            $i = 1;
            while (file_exists($dir.$fileName)) {
                $fileName = $safeFilename.$i.'.'.$extension;
                ++$i;
            }

            try {
                $file->move($dir, $fileName);
            } catch (\Exception $e) {
                throw new \Exception("Error moving file to '$dir': ".$e->getMessage());
            }
        } else {
            throw new \Exception("Extension not allowed: .$extension. Allowed: ".implode(', ', $aceptedExtensions));
        }

        return $fileName;
    }
}
