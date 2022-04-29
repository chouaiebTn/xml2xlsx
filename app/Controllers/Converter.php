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
        
        //disable libxml errors and allow user to fetch error information as needed
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
            $xlsx_file_name = str_replace('{ID}',uniqid(),XLS_FILE_DOWNLO_NAME);
            $xlsx_rows = [];
            $keys = array_keys($dataArray);
            $result->keys = $keys;
            $lines = $dataArray[$keys[0]];

            if(count($keys) == 1){
                $xlsx_header = [];
                //extract column names 
                $first_line = $lines[0];
                $first_line_keys = array_keys($first_line);
                $first_line_values = array_values($first_line);
                $attributes_values = [];
                $attributes_keys   = [];
                //if the top node have attribute(s)
                if($first_line_keys[0] == '@attributes'){
                    unset($first_line_keys[0]);
                    $attributes_pairs  = $first_line_values[0];
                    $attributes_keys   = array_keys($attributes_pairs);
                }

                $xlsx_header = array_merge($attributes_keys,$first_line_keys);
                array_walk($xlsx_header, function(&$value){
                          $value = ucwords(strtolower($value));
                        });

                $xlsx_rows[] = $xlsx_header;


                foreach($lines as $line){
                    $row = [];
                    foreach($line as $key => $value){

                        if($key == '@attributes'){
                            $attributes_values  = array_values($value);
                             foreach($attributes_values as $value){
                                $row[] = $value;
                             }
                         }else{
                            if(is_string($value)){
                                $row[] = $value;
                            }elseif(is_array($value)){
                                if(!empty($value)){
                                    $row[] = implode(',',$value);
                                }else{
                                    $row[] = '';
                                }
                            }else{
                                $row[] = '';
                            }
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