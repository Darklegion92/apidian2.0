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
        else {
            return [
                'success' => false,
                'message' => 'No se han configurado los parametros IMAP en la empresa',
            ];
        }

        if(isset($request->last_event))
            $request->last_event = $request->last_event;
        else
            $request->last_event = 1;

        $responses = array();
        $responses_3 = array();
        $processed_emails = 0;
        $valid_subjects = 0;
        $batch_size = 50;
        $offset = 0;
        $has_more_emails = true;
        $all_subjects = array();
        $total_processed = 0;

        try {
            while ($has_more_emails) {
                // Consultar correos con límite y offset
                $inbox = imap_open($this->imap_mailbox_url, $this->imap_user, $this->imap_password);
                
                if (!$inbox) {
                    $error = imap_last_error();
                    throw new \Exception('No se pudo conectar al servidor IMAP: ' . $error);
                }

                if($request->only_unread)
                    $query_seen_unseen = 'UNSEEN SINCE "'.$request->start_date;
                else
                    $query_seen_unseen = 'SINCE "'.$request->start_date;

                if($request->end_date)
                    $query_before = '" BEFORE "'.$request->end_date.'"';
                else
                    $query_before = '"';

                $emails = imap_search($inbox, $query_seen_unseen.$query_before);
                if (!$emails) {
                    $has_more_emails = false;
                    imap_close($inbox);
                    continue;
                }

                // Obtener solo el lote actual
                $current_batch = array_slice($emails, $offset, $batch_size);
                if (empty($current_batch)) {
                    $has_more_emails = false;
                    imap_close($inbox);
                    continue;
                }

                foreach($current_batch as $email) {
                    $processed_emails++;
                    $overview = imap_fetch_overview($inbox, $email);
                    foreach($overview as $over) {

                        if(isset($over->subject)) {
                            // Decodificar el asunto MIME si está codificado
                            $decoded_subject = imap_mime_header_decode($over->subject);
                            $clean_subject = '';
                            foreach ($decoded_subject as $part) {
                                if ($part->charset === 'UTF-8' || $part->charset === 'default') {
                                    $clean_subject .= $part->text;
                                } else {
                                    $clean_subject .= mb_convert_encoding($part->text, 'UTF-8', $part->charset);
                                }
                            }
                            
                            // Limpiar el asunto de prefijos comunes
                            // Primero eliminar Fwd: RV: o RV: Fwd:
                            $clean_subject = preg_replace('/^(Fwd:\s*RV:|RV:\s*Fwd:)\s*/i', '', $clean_subject);
                            // Luego eliminar Fwd: o RV: individuales
                            $clean_subject = preg_replace('/^(Fwd:|RV:)\s*/i', '', $clean_subject);
                            
                            // Analizar los punto y coma
                            $parts = explode(';', $clean_subject);
                            $semicolon_positions = [];
                            $last_pos = 0;
                            while (($pos = strpos($clean_subject, ';', $last_pos)) !== false) {
                                $semicolon_positions[] = $pos;
                                $last_pos = $pos + 1;
                            }
                        }

                        if(isset($over->subject)) {
                            // Decodificar el asunto MIME si está codificado
                            $decoded_subject = imap_mime_header_decode($over->subject);
                            $clean_subject = '';
                            foreach ($decoded_subject as $part) {
                                if ($part->charset === 'UTF-8' || $part->charset === 'default') {
                                    $clean_subject .= $part->text;
                                } else {
                                    $clean_subject .= mb_convert_encoding($part->text, 'UTF-8', $part->charset);
                                }
                            }
                            
                            // Limpiar el asunto de prefijos comunes
                            // Primero eliminar Fwd: RV: o RV: Fwd:
                            $clean_subject = preg_replace('/^(Fwd:\s*RV:|RV:\s*Fwd:)\s*/i', '', $clean_subject);
                            // Luego eliminar Fwd: o RV: individuales
                            $clean_subject = preg_replace('/^(Fwd:|RV:)\s*/i', '', $clean_subject);
                            
                            // Eliminar el punto y coma final si existe
                            $clean_subject = rtrim($clean_subject, ';');
                            
                            if((substr_count($clean_subject, ";") == 4 or substr_count($clean_subject, ";") == 5) and 
                               (strpos($clean_subject, ";01;") or strpos($clean_subject, "; 01;") or 
                                strpos($clean_subject, ";01 ;") or strpos($clean_subject, "; 01 ;"))){
                                $valid_subjects++;
                                $current_subject = utf8_decode($this->fix_text_subjects($clean_subject));
                                $all_subjects[$current_subject] = "";
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
                                $processed_successfully = false;
                                foreach($attachments as $attachment){
                                    if($attachment['is_attachment']){
                                        $filename = $attachment['filename'];
                                        $attachment_file = $attachment['attachment'];
                                        if($attachment_file){
                                            $temp_dir = storage_path("received/temp/{$company->identification_number}");
                                            if(!$this->ensure_directory_exists($temp_dir)) {
                                                continue;
                                            }

                                            $sanitized_subject = $this->sanitize_filename($current_subject);
                                            $sanitized_filename = $this->sanitize_filename($filename);
                                            $full_path = $temp_dir . DIRECTORY_SEPARATOR . $sanitized_subject . "_" . $sanitized_filename;

                                            try {
                                                $gestor = fopen($full_path, 'w');
                                                if ($gestor === false) {
                                                    throw new \Exception('No se pudo abrir el archivo para escritura');
                                                }
                                                fwrite($gestor, $attachment_file);
                                                fclose($gestor);

                                                if (!$this->unzip_attachment($full_path)) {
                                                    continue;
                                                }

                                                $responses[$filename] = $this->execute_event($full_path);

                                                if($request->last_event == 3)
                                                    $responses_3[$filename] = $this->execute_event($full_path, $request->last_event);
                                                else
                                                    $response_3[$filename] = null;
                                                
                                                $processed_successfully = true;
                                                $total_processed++;
                                            } catch (\Exception $e) {
                                                continue;
                                            }
                                        }
                                    }
                                }
                                
                                if ($processed_successfully) {
                                    $all_subjects[$current_subject] = $filename;
                                    // Marcar el correo como leído y moverlo a la papelera
                                    imap_setflag_full($inbox, $email, "\\Seen");
                                    
                                    // Obtener lista de carpetas disponibles
                                    $folders = imap_list($inbox, $this->imap_mailbox_url, "*");
                                    
                                    // Intentar mover a diferentes nombres de carpeta de papelera
                                    $trash_folders = ['Trash', 'INBOX.Trash', 'INBOX/Trash', 'Papelera', 'INBOX.Papelera', 'INBOX/Papelera', 'Deleted Items', 'INBOX.Deleted Items', 'INBOX/Deleted Items', '[Gmail]/Papelera', '[Gmail]/Trash'];
                                    $moved = false;
                                    
                                    foreach ($trash_folders as $folder) {
                                        // Intentar primero con copy y delete
                                        if (imap_mail_copy($inbox, $email, $folder)) {
                                            // Marcar para eliminación
                                            imap_delete($inbox, $email);
                                            $moved = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Incrementar el offset para el siguiente lote
                $offset += $batch_size;
                
                // Expulsar los correos marcados para eliminación y cerrar la conexión
                imap_expunge($inbox);
                imap_close($inbox);
            }

            if (empty($all_subjects)) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron emails después del día ... '.$request->start_date,
                ];
            }

            $this->rmDir_rf(storage_path("received/temp/{$company->identification_number}"));
            $data = [];

            foreach ($all_subjects as $subject => $file) {
                if (!isset($responses[$file])) {
                    continue;
                }

                foreach ($responses[$file] as $xml_file_name => $file_data_1) {
                    if($request->last_event == 0){
                        $data[] = [
                            'subject' => $subject,
                            'xml_file_name' => $xml_file_name,
                            'base64_attacheddocument' => $request->base64_attacheddocument ? $file_data_1['base64_attacheddocument'] : null,
                        ];
                    }
                    else if($request->last_event == 1){
                        $data[] = [
                            'subject' => $subject,
                            'xml_file_name' => $xml_file_name,
                            'base64_attacheddocument' => $request->base64_attacheddocument ? $file_data_1['base64_attacheddocument'] : null,
                            'response_receipt_accknowledgment_1' => $file_data_1['response_execute_event'],
                        ];
                    }
                    else{
                        if (!isset($responses_3[$file])) {
                            continue;
                        }

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
                'message' => 'Se procesaron ' . $total_processed . ' correos en total',
                'data' => $data,
                'stats' => [
                    'total_processed' => $total_processed,
                    'valid_subjects' => $valid_subjects,
                    'total_emails' => $processed_emails
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'No se pudo realizar la conexión IMAP, revise parámetros de conexión y disponibilidad del buzón.... '.$e->getMessage()." ".imap_last_error(),
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

    private function fix_text_subjects($subject, $real_name = FALSE){
        $str = "";

        $subject_array = imap_mime_header_decode($subject);
        foreach($subject_array as $obj)
            if (mb_detect_encoding($obj->text, 'UTF-8', true) === 'UTF-8')
                $str .= $obj->text; // Mantén UTF-8
            else
                $str .= utf8_decode($obj->text); // Decodifica si es necesario
        if(!$real_name){
            // Sanitizar el nombre del archivo
            $str = $this->sanitize_filename($str);
            return str_replace(":", "-", str_replace("ñ", "n", str_replace("Ñ", "N", str_replace(" ", "_", $str))));
        }
        else{
            return $str;
        }
    }

    private function sanitize_filename($filename) {
        // Reemplazar caracteres especiales y acentos
        $filename = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', '¿', '?', '¡', '!', '(', ')', '[', ']', '{', '}', ';', ',', ':', ' '],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '_'],
            $filename
        );
        
        // Eliminar cualquier otro carácter que no sea alfanumérico, punto, guión o guión bajo
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Asegurarse de que el nombre no comience o termine con punto o guión
        $filename = trim($filename, '.-_');
        
        // Limitar la longitud del nombre de archivo
        $filename = substr($filename, 0, 255);
        
        return $filename;
    }

    private function unzip_attachment($zip_filename){
        $zip_directory = substr($zip_filename, 0, strlen($zip_filename) - 4);

        if(!is_dir($zip_directory))
            mkdir($zip_directory, 0777, true);
        $zip = new ZipArchive;
        $res = $zip->open($zip_filename);
        if($res === TRUE){
            try {
                $zip->extractTo($zip_directory);
                $zip->close();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        else {
            return false;
        }
    }

    private function ensure_directory_exists($path) {
        if (!is_dir($path)) {
            try {
                mkdir($path, 0777, true);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }
}
