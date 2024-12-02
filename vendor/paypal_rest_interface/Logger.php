<?php
/**
 * File: Logger.php.
 * Author: Yosvel Reyes Fonfria <yosvel.fonfria@gmail.com>
 * Created At: 5/6/15 4:06 PM
 *
 */

class Logger
{

    static function write($text, $logFile='weblog.txt')
    {
        $now = new DateTime();

        $log_path = dirname(dirname(dirname(__FILE__))). "/public";

        $txt = $now->format('Y-m-d H:i:s') . "\t---\t" . $text . "\n";

        $path = $log_path . "/" . $logFile;

        $fp = fopen($path, "a");

        if($fp) {
            fputs($fp, $txt, strlen($txt));
            fclose($fp);
            return true;
        }

        error_log(sprintf('Failed to open the file %s for writing on %s at %d', $path, __FILE__, __LINE__), 0);
    }
}