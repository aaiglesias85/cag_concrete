<?php

namespace App\Utils;

/**
 * Misma lógica de archivo que {@see Base::writelog} para clases que no heredan Base.
 * Por defecto escribe en weblog.txt (y con $path_logs igual que Base).
 */
final class OverridePaymentWritelog
{
   public static function writelog(string $txt, string $filename = 'weblog.txt'): void
   {
      return;
      global $path_logs;

      $datetime = date('Y-m-d H:i:s', time());
      $txt = $datetime . "\t---\t" . $txt . "\n";

      if ($path_logs != '') {
         $filename = $path_logs . $filename;
      }

      $fp = fopen($filename, 'a');
      if ($fp) {
         fputs($fp, $txt, strlen($txt));
         fclose($fp);
      } else {
         die("Error IO: writing file '{$filename}'");
      }
   }
}
