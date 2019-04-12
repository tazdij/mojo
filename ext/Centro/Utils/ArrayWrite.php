<?php

namespace Ext\Centro\Utils;


class ArrayWrite {
    public static function SaveAs($filename, $array, $merge_array = FALSE) {
        // If merge is true, try and read the file first, and merge contents
        //      fail if error
        // otherwise obliterate file and write array
        
        if (!$merge_array) {
            $f = fopen($filename, 'w+');
            $arrString = var_export($array, TRUE);
            fwrite($f, '<?php' . "\n\n" . 'return ');
            fwrite($f, $arrString);
            fwrite($f, ';');
            fclose($f);
        } else {
            // TODO: Read file, then merge arrays, and save
            throw new \Exception('ArrayWrite SaveAs with merge_array TRUE is not supported yet.');
        }
    }
}
