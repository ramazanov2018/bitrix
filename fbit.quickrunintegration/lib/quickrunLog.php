<?php
namespace Fbit\Quickrunintegration;
use  \Bitrix\Main\Type\DateTime;


class quickrunLog
{
    static $ogPath = '/quickrun_exchange_log/';
    static $daysAgo = "-10 day";

    function SaveLog($ClassName, $log = ''){

        $date = new DateTime();

        $today = $date->format("d-m-Y");

        $delete = $date->add(self::$daysAgo)->format("d-m-Y");
        $DeletePath = $_SERVER['DOCUMENT_ROOT'].self::$ogPath.'_'.$ClassName.$delete.'.log';

        self::DeleteLog($DeletePath);

        $Path = $_SERVER['DOCUMENT_ROOT'].self::$ogPath.'_'.$ClassName.$today.'.log';
        pre($Path);
        $rv = file_put_contents($Path, $log . PHP_EOL, FILE_APPEND);
        pre(var_dump($rv));
    }

    function DeleteLog($Path){
        if (file_exists($Path)) {
           unlink($Path);
        }
    }

    function GetLog($className){
       $directory = $_SERVER['DOCUMENT_ROOT'].self::$ogPath;
       $iterator = new \DirectoryIterator($directory);
       foreach ($iterator as $fileinfo) {
           if ($fileinfo->isFile()) {
               $filePath = self::$ogPath.$fileinfo->getFilename();
               if (strpos($filePath, $className)) {
                   echo '<a target="_blank" href="'.$filePath.'">'.$fileinfo->getFilename().'</a><br/>';
               }
           }
       }
    }

}

