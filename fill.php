public function actionFillpdfdata($data = null) {
        $pdfcode = Yii::app()->getRequest()->getQuery('ids');
        $test = Yii::app()->getRequest()->getQuery('type');
        $pdf_file = '';
        // allow for up to 25 different files to be created, based on the minute
        $min = date('i') % 25;

        $fdf_file = Yii::getPathOfAlias('webroot') . '/uploads/temp/posted-' . $min . '.fdf';
        // create directory if it is not exists
        $tmp_dir = Yii::getPathOfAlias('webroot') . '/uploads/temp';
        if (!is_dir($tmp_dir)) {
            mkdir($tmp_dir, 0777);
        }
        $model = Forms::model()->findAll(array(
            'condition' => 'frm_code=:code',
            'params' => array(':code' => $pdfcode),
        ));

        $pdf_to_fill = Yii::getPathOfAlias('webroot') . '/uploads/forms/' . $model[0]->frm_pdf;
        $datatofill = $model[0]->analyse_data;
        $datatofill = json_decode($datatofill, true);
        if ($test != 1) {
            $datatofill = $this->fill_with_tag($datatofill);           // function to replace with tag value
        } else {
            $datatofill = $data;
        }
        $fdf = new Pdfparser;
        $fdf = $fdf->createFDF($pdf_file, $datatofill);

        // Create a file for later use
        if ($fp = fopen($fdf_file, 'w')) {
            fwrite($fp, $fdf);
            $CREATED = TRUE;
        } else {
            echo 'Unable to create file: ' . $pdf_to_fill . '<br><br>';
            $CREATED = FALSE;
        }
        fclose($fp);
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $model[0]->frm_pdf . '"');
        passthru("pdftk " . $pdf_to_fill . " fill_form " . $fdf_file . " output - ");
        
        unlink($fdf_file);
        exit;
    }
    
    public function fill_with_tag($array) {
        $resultarray = array();
        if (is_array($array)) {
            foreach ($array as $pdftag => $tag) {
                $tag_val = $this->gettagvalue($tag);
                $resultarray[$pdftag] = $tag_val;
            }
        }
        return $resultarray;
    }
    
    public function gettagvalue($tag) {
        $model = new Forms;
        $tag = str_replace(array('<', '>'), '', $tag);
        $tag = Tagform::model()->findAll(array(
            'condition' => 'tag_name=:code',
            'params' => array(':code' => $tag),
        ));
        if (!empty($tag)) {
            //$user=Yii::app()->session['employee_id'];
            $user = Yii::app()->session['user_id'];                // change it here 
            $tag = $model->gettagvaluedb($tag[0], $user);
            return $tag;
        }
        return;
    }
