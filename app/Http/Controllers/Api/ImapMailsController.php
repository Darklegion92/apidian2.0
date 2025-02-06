<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\ImapMailsRequest;
use App\Http\Requests\Api\SendEventRequest;
use App\Traits\DocumentTrait;
use ZipArchive;

class ImapMailsController extends Controller
{
    use DocumentTrait;

    private $imap_server;
    private $imap_user;
    private $imap_password;
    private $imap_port;
    private $imap_encryption;
    private $imap_mailbox_url;

    function imap_receipt_acknowledgment(ImapMailsRequest $request){
        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        if($company->validate_imap_mail_server()){
            $this->imap_server = $company->imap_server;
            $this->imap_user = $company->imap_user;
            $this->imap_password = $company->imap_password;
            $this->imap_port = $company->imap_port;
            $this->imap_encryption = $company->imap_encryption;
            $this->imap_mailbox_url = "{{$this->imap_server}:{$this->imap_port}/imap/{$this->imap_encryption}/novalidate-cert}INBOX";
        }
        else
            return [
                'success' => false,
                'message' => 'No se han configurado los parametros IMAP en la empresa',
            ];

        if(isset($request->last_event))
            $request->last_event = $request->last_event;
        else
            $request->last_event = 1;

        $subjects = array();
        try{
            $inbox = imap_open($this->imap_mailbox_url, $this->imap_user, $this->imap_password);
            if($request->only_unread)
                $query_seen_unseen = 'UNSEEN SINCE "'.$request->start_date;
            else
                $query_seen_unseen = 'SINCE "'.$request->start_date;

            if($request->end_date)
                $query_before = '" BEFORE "'.$request->end_date.'"';
            else
                $query_before = '"';

            $emails = imap_search($inbox, $query_seen_unseen.$query_before);
            if($emails){
                $responses = array();
                $responses_3 = array();
                foreach($emails as $email){
                    $overview = imap_fetch_overview($inbox, $email);
                    foreach($overview as $over){
                        if(isset($over->subject) && (substr_count($over->subject, ";") == 4 or substr_count($over->subject, ";") == 5) and (strpos($over->subject, ";01;") or strpos($over->subject, "; 01;") or strpos($over->subject, ";01 ;") or strpos($over->subject, "; 01 ;"))){
                            $subjects[utf8_decode($this->fix_text_subjects($over->subject))] = "";
                            $structure = imap_fetchstructure ($inbox, $email);
                            $attachments = array();
                            if(isset($structure->parts) && count($structure->parts)){
                                for($i=0;$i<count($structure->parts);$i++){
                                    $attachments[$i] = array(
                                            'is_attachment' => false,
                                            'filename' => '',
                                            'name' => '',
                                            'attachment' => ''
                                    );
                                    if($structure->parts[$i]->ifdparameters){
                                        foreach($structure->parts[$i]->dparameters as $object){
                                            if(strtolower($object->attribute) == 'filename'){
                                                $attachments[$i]['is_attachment'] = true;
                                                $attachments[$i]['filename'] = $object->value;
                                            }
                                        }
                                    }
                                    if($structure->parts[$i]->ifparameters){
                                        foreach($structure->parts[$i]->parameters as $object){
                                            if(strtolower($object->attribute) == 'name'){
                                                $attachments[$i]['is_attachment'] = true;
                                                $attachments[$i]['name'] = $object->value;
                                            }
                                        }
                                    }
                                    if($attachments[$i]['is_attachment']){
                                        $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email, $i + 1);
                                        if ($structure->parts[$i]->encoding == 3) // 3 = BASE64
                                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                        else
                                            if($structure->parts[$i]->encoding == 4)  // 4 = QUOTED-PRINTABLE
                                                $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                    }
                                }
                            }

                            $filename = "";
                            foreach($attachments as $attachment){
                                if($attachment['is_attachment']){
                                    $filename = $attachment['filename'];
                                    $attachment_file = $attachment['attachment'];
                                    if($attachment_file){
                                        if(!is_dir(storage_path("received/temp/{$company->identification_number}")))
                                            mkdir(storage_path("received/temp/{$company->identification_number}"), 0777, true);
                                        $gestor = fopen(storage_path("received/temp/{$company->identification_number}/".utf8_decode($this->fix_text_subjects($over->subject))."_".$filename), 'w');
                                        fwrite($gestor, $attachment_file);
                                        fclose($gestor);
                                        $this->unzip_attachment(storage_path("received/temp/{$company->identification_number}/".utf8_decode($this->fix_text_subjects($over->subject))."_".$filename));
                                        $responses[$filename] = $this->execute_event(storage_path("received/temp/{$company->identification_number}/".utf8_decode($this->fix_text_subjects($over->subject))."_".$filename));
                                        if($request->last_event == 3)
                                            $responses_3[$filename] = $this->execute_event(storage_path("received/temp/{$company->identification_number}/".utf8_decode($this->fix_text_subjects($over->subject))."_".$filename), $request->last_event);
                                        else
                                            $response_3[$filename] = null;
                                    }
                                }
                            }
                            $subjects[utf8_decode($this->fix_text_subjects($over->subject))] = $filename;
                        }
                    }
                }
                $this->rmDir_rf(storage_path("received/temp/{$company->identification_number}"));
                $data = [];
                foreach ($subjects as $subject => $file) {
                    foreach ($responses[$file] as $xml_file_name => $file_data_1) {
                        if($request->last_event == 0){
                            $data[] = [
                                'subject' => $subject,
                                'xml_file_name' => $xml_file_name,
                                'base64_attacheddocument' => $request->base64_attacheddocument ? $file_data_1['base64_attacheddocument'] : null,
                            ];
                        }
                        else
                            if($request->last_event == 1){
                                $data[] = [
                                    'subject' => $subject,
                                    'xml_file_name' => $xml_file_name,
                                    'base64_attacheddocument' => $request->base64_attacheddocument ? $file_data_1['base64_attacheddocument'] : null,
                                    'response_receipt_accknowledgment_1' => $file_data_1['response_execute_event'],
                                ];
                            }
                            else{
                                foreach ($responses_3[$file] as $xml_file_name_3 => $file_data_3) {
                                    $data[] = [
                                        'subject' => $subject,
                                        'xml_file_name' => $xml_file_name,
                                        'base64_attacheddocument' => $request->base64_attacheddocument ? $file_data_1['base64_attacheddocument'] : null,
                                        'response_receipt_accknowledgment_1' => $file_data_1['response_execute_event'],
                                        'response_receipt_accknowledgment_3' => $file_data_3['response_execute_event'],
                                    ];
                                }
                        }
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Se encontraron los siguientes emails...',
                    'data' => $data,
                ];
            }
            else
                return [
                    'success' => false,
                    'message' => 'No se encontraron emails despues del dia ... '.$request->start_date,
                ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'No se pudo realizar la conexion IMAP, revise parametros de conexion y disponibilidad del buzon.... '.$e->getMessage()." ".imap_last_error(),
            ];
        }
    }

    private function execute_event($zip_filename, $event_id = 1){
        $zip_directory = substr($zip_filename, 0, strlen($zip_filename) - 4);

        $files = array_diff(scandir($zip_directory), array('..', '.'));
        $response = array();
        foreach($files as $file){
            $file = $zip_directory.DIRECTORY_SEPARATOR.$file;
            if(pathinfo(strtolower($file), PATHINFO_EXTENSION) == "xml"){
                $event = new SendEventController();
                $send = [
                    'event_id' => $event_id,
                    'sendmail' => true,
                    'sendmailtome' => true,
                    'allow_cash_documents' => true,
                    'base64_attacheddocument_name' => basename($file),
                    'base64_attacheddocument' => base64_encode(file_get_contents($file)),
                ];
                $data_send = json_encode($send);
                if($event_id != 0){
                    $r = new SendEventRequest($send);
                    $r = $event->sendevent($r);
                    $response[basename($file)] = [
                                    'base64_attacheddocument' => base64_encode(file_get_contents($file)),
                                    'response_execute_event' => $r,
                             ];
                }
                else{
                    $response[basename($file)] = [
                                   'base64_attacheddocument' => null,
                                   'response_execute_event' => null,
                             ];
                }
            }
        }
        return $response;
    }

    private function unzip_attachment($zip_filename){
        $zip_directory = substr($zip_filename, 0, strlen($zip_filename) - 4);

        if(!is_dir($zip_directory))
            mkdir($zip_directory, 0777, true);
        $zip = new ZipArchive;
        $res = $zip->open($zip_filename);
        if($res === TRUE){
          $zip->extractTo($zip_directory);
          $zip->close();
          return true;
        }
        else
          return false;
    }

    private function fix_text_subjects($subject, $real_name = FALSE){
        $str = "";

        $subject_array = imap_mime_header_decode($subject);
        foreach($subject_array as $obj)
            if (mb_detect_encoding($obj->text, 'UTF-8', true) === 'UTF-8')
                $str .= $obj->text; // Mantén UTF-8
            else
                $str .= utf8_decode($obj->text); // Decodifica si es necesario
        if(!$real_name){
            return str_replace(":", "-", str_replace("ñ", "n", str_replace("Ñ", "N", str_replace(" ", "_", $str))));
        }
        else{
            return $str;
        }
    }
}
