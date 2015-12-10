public function Formanalysis($pdfname) {
        if(!$this->is_super_admin())
        {
            $this->redirect("permissiondenied");
        }
        $pdfname = str_replace(" ", "", $pdfname);
            
        $ext = pathinfo($pdfname,PATHINFO_EXTENSION);
        if($ext == "")
        {
            $pdfname = $pdfname.".pdf";
        }
        
        $pdffile = Yii::app()->getBaseUrl(true) . '/uploads/forms/' . $pdfname;
        exec("pdftk " . $pdffile . " dump_data_fields 2>&1", $output, $retval);
        if (is_array($output) && !empty($output)) {
            //got an error for some pdf if these are secure 
            if (strpos($output[0], 'Error') !== false) {
                $pdf = Yii::getPathOfAlias('webroot') . '/uploads/forms/' . $pdfname;
                $pdfunsafe = Yii::getPathOfAlias('webroot') . '/uploads/forms/unsafe-' . $pdfname;
                //echo "pdftk ".$pdffile." input_pw foopass output ".$unsafepdf;
                exec("qpdf --decrypt " . $pdf . " " . $pdfunsafe);
               // chmod($pdfunsafe, 0777);
                $pdffile = Yii::app()->getBaseUrl(true) . '/uploads/forms/unsafe-' . $pdfname;
                exec("pdftk " . $pdffile . " dump_data_fields 2>&1", $outputunsafe, $retval);
                
                if(is_dir($pdf))
                {
                    unlink($pdf);
                    rename($pdfunsafe, $pdf);
                }                                

                return $outputunsafe;
                //$response=array('0'=>'error','error'=>$output[0]);
                //return $response;
            }
        }
        //if (strpos($output[0],'Error') !== false){ echo  "error to run" ; }   // this is the option to handle error 
        return $output;
    }
