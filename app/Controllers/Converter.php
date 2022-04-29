<?php

namespace App\Controllers;

use Shuchkin\SimpleXLSXGen;

/**
 * XML TO XLSX Convertion controller
 * Author      : Chouaieb Bedoui
 * Date        : 28/04/2022
 * Email       : webm964@gmail.com
 * PHP version : 7.4.28
 */


class Converter extends \IceCat\Controller{

   
    


    protected function process($fileUploadResult){
        
        libxml_use_internal_errors(TRUE);
        $result = new \stdClass();

        $xmlDocument = simplexml_load_file($fileUploadResult->file);
        if ($xmlDocument === FALSE) {
            // if failed to load the xml file return an error
            $result->error = true;
            $result->msg = "There were errors while parsing the XML file.\n";
            foreach(libxml_get_errors() as $error) {
                $result->msg .= $error->message ."\n";
            }
        }else{
            //convert xml to a json object
            $jsonObject = json_encode($xmlDocument);
            //convert json object to an array
            $dataArray = json_decode($jsonObject, TRUE);
            $result->data = $dataArray;
            

            //xlsx output filename 
            $xlsx_file_name = 'result_xlsx_'.uniqid().'.xlsx';
            $xlsx_rows = [];
            $keys = array_keys($dataArray);
            $result->keys = $keys;
            $lines = $dataArray[$keys[0]];

            if(count($keys) == 1){
                foreach($lines as $line){
                    $column_names = array_keys($line);
                    $values = array_values($line);
                   

                    if($column_names[0] == '@attributes'){
                        //remove attributes if found
                        $keys = array_keys($values[0]);
                        if(count($keys) == 1){
                            $column_names[0] = $keys[0];
                            $values[0] = $values[0][$keys[0]];
                        }else{
                            $column_names[0] = implode(',',$keys);
                            $values[0] = implode(',',$values);
                        }
                       
                    }
                    
                    //add header as first row
                    if(!count($xlsx_rows)){
                        array_walk($column_names, function(&$value){
                          $value = strtoupper($value);
                        });
                        $xlsx_rows[]= $column_names;
                    }

                    //loop through nodes and add them as rows
                    $row = [];
                    
                    foreach($values as $value){
                        if(is_string($value)){
                            $row[] = $value;
                        }elseif(is_array($value)){
                            if(count($value)){
                                $row[] = '';//implode(',',$value);
                            }else{
                                $row[] = '';
                            }
                        }else{
                            $row[] = '';
                        }
                    }
                    

                    $xlsx_rows[] = $row;
                }

                //check if there is at least 2 rows a header and a data row
                if(count($xlsx_rows) >= 2){
                    $result->error = false;
                    $result->download_link = XLSX_DOWNLOAD.$xlsx_file_name;
                    
                    //save xlsx file   
                    \Shuchkin\SimpleXLSXGen::fromArray($xlsx_rows)->saveAs(XLSX_DIR.$xlsx_file_name);

                    //delete xml file we no longer need it
                    unlink($fileUploadResult->file);
                }else{
                    $result->error = true;
                    $result->msg = 'Your XML file appears to have no data !';
                }
            }else{
                $result->error = true;
                $result->msg = 'Unknown XML format , cannot be converted to Excel';
            }
            
            
            

        }


        echo json_encode($result);
    }

    public function upload(){
        $upload_result = $this->uploadXML();
        
        if($upload_result->error){
            echo json_encode($upload_result);
            die;
        }

        $this->process($upload_result);
    }
    protected function uploadXML(){
        $result = new \stdClass();
        if(!empty($_FILES[XML_FILE_UPLOAD_NAME]['name'])){
            //random file name to store
            $new_file_name = 'upload_xml_'.uniqid().'.xml';
            $upload_file = XML_DIR.$new_file_name;
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $allowed_types = array('xml' => 'text/xml');
            //check if file is really and xml file
            $file_allowed = array_search($finfo->file($_FILES[XML_FILE_UPLOAD_NAME]['tmp_name']), $allowed_types);
            if($file_allowed){
                    //all checks are good ,move file to xml folder
                    if(move_uploaded_file($_FILES[XML_FILE_UPLOAD_NAME]['tmp_name'], $upload_file)){
                        $result->file = $upload_file;
                        $result->error = false;
                    }else{
                        $result->error = true;
                        $result->msg = 'Error uploading XML file , please check uploads folder permissions';
                    }

            }else{
                $result->error = true;
                $result->msg = 'Only XML files are allowed';
            }
        
        }else{
            $result->error = true;
            $result->msg = 'No file to upload !';
        }
        return $result;
    }
}