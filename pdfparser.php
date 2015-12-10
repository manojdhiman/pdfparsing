<?php

class Pdfparser {

    public function createFDF($file, $info) {
        $data = "%FDF-1.2\n%����\n1 0 obj\n<< \n/FDF << /Fields [ ";
        foreach ($info as $field => $val) {
            if (is_array($val)) {
                $data.='<</T(' . $field . ')/V[';
                foreach ($val as $opt)
                    $data.='(' . trim($opt) . ')';
                $data.=']>>';
            } else {
                $data.='<</T(' . $field . ')/V(' . trim($val) . ')>>';
            }
        }
        $data.="] \n/F (" . $file . ") /ID [ <" . md5(time()) . ">\n] >>" .
                " \n>> \nendobj\ntrailer\n" .
                "<<\n/Root 1 0 R \n\n>>\n%%EOF\n";
        return $data;
    }

    public function createXFDF($file, $info, $enc = 'UTF-8') {
        $data = '<?xml version="1.0" encoding="' . $enc . '"?>' . "\n" .
                '<xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">' . "\n" .
                '<fields>' . "\n";
        foreach ($info as $field => $val) {
            $data.='<field name="' . $field . '">' . "\n";
            if (is_array($val)) {
                foreach ($val as $opt)
                    $data.='<value>' . htmlentities($opt) . '</value>' . "\n";
            } else {
                $data.='<value>' . htmlentities($val) . '</value>' . "\n";
            }
            $data.='</field>' . "\n";
        }
        $data.='</fields>' . "\n" .
                '<ids original="' . md5($file) . '" modified="' . time() . '" />' . "\n" .
                '<f href="' . $file . '" />' . "\n" .
                '</xfdf>' . "\n";
        return $data;
    }

}

?>
