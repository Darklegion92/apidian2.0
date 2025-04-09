<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Company;
use App\Resolution;
use App\Document;
use App\DocumentPayroll;
use App\Municipality;
use App\Entity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JoinPDFsRequest;
use App\Http\Requests\Api\NextConsecutiveRequest;
use App\Http\Requests\Api\RutRequest;
use App\Http\Requests\Api\ValidateMailRequest;
use App\Http\Requests\Api\LoadUpdateEntitiesRequest;
use Storage;
use App\Traits\DocumentTrait;
use App\TypeDocumentIdentification;
use Exception;
use PDFMerger;
use Goutte\Client as ClientScrap;
use Symfony\Component\HttpClient\HttpClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use ubl21dian\Templates\SOAP\GetAcquirer;

class MiscelaneousController extends Controller
{
    use DocumentTrait;
    protected $nameclient;

    public function NextConsecutive(NextConsecutiveRequest $request)
    {

        // User
        $user = auth()->user();

        // User company
        $company = $user->company;

        $resolution = $company->resolutions->where('type_document_id', $request->type_document_id)->where('prefix', $request->prefix)->first();

        try {
            if ($request->type_document_id == 9 || $request->type_document_id == 10) {
                $document = DocumentPayroll::where('identification_number', $company->identification_number)->where('type_document_id', $request->type_document_id)->where('state_document_id', 1)->where('prefix', $resolution->prefix)->get()->sortByDesc('consecutive')->first();
                return [
                    'success' => true,
                    'type_document_id' => $request->type_document_id,
                    'prefix' => $resolution->prefix,
                    'number' => ($document) ? ((int)$document->consecutive + 1) : (int)$resolution->from,
                ];
            } else {
                $document = Document::where('identification_number', $company->identification_number)->where('type_document_id', $request->type_document_id)->where('state_document_id', 1)->where('prefix', $resolution->prefix)->get()->sortByDesc('number')->first();
                return [
                    'success' => true,
                    'type_document_id' => $request->type_document_id,
                    'prefix' => $resolution->prefix,
                    'number' => ($document) ? ((int)$document->number + 1) : (int)$resolution->from,
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];
        }
    }

    public function joinPDFs(JoinPDFsRequest $request)
    {
        try {
            $user = auth()->user();

            $company = $user->company;

            $new_pdf = new PDFMerger();

            foreach ($request->pdfs as $pdf) {
                if ($pdf['type_document_id'] == 1 || $pdf['type_document_id'] == 2 || $pdf['type_document_id'] == 3 || $pdf['type_document_id'] == 12)
                    $type_document = "FES-";
                else
                    if ($pdf['type_document_id'] == 4)
                    $type_document = "NCS-";
                else
                        if ($pdf['type_document_id'] == 5)
                    $type_document = "NDS-";
                else
                            if ($pdf['type_document_id'] == 9)
                    $type_document = "NIS-";
                else
                                if ($pdf['type_document_id'] == 10)
                    $type_document = "NAS-";
                else
                                    if ($pdf['type_document_id'] == 11)
                    $type_document = "DSS-";
                $new_pdf->addPDF(storage_path("app/public/{$company->identification_number}/{$type_document}{$pdf['prefix']}{$pdf['number']}" . ".pdf"), 'all');
            }

            $new_pdf->merge('file', storage_path("app/public/{$company->identification_number}/{$request->name_joined_pdfs}"));
            return [
                'success' => true,
                'message' => 'Operacion realizada con exito.',
                'pdfbase64' => base64_encode(file_get_contents(storage_path("app/public/{$company->identification_number}/{$request->name_joined_pdfs}")))
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "{$e->getLine()} - {$e->getMessage()}"
            ];
        }
    }

    public function setNameClient($name)
    {
        $this->nameclient = $name;
    }

    public function SearchCompany($nit)
    {
        $client = new ClientScrap(); //Instancia de "use Goutte\Client;"
        $crawler = $client->request('GET', "https://www.einforma.co/servlet/app/portal/ENTP/prod/LISTA_EMPRESAS/razonsocial/{$nit}");
        $name = $crawler->filter('#titEtiqueta')->text("No se encontro NIT.");
        if ($name !== "No se encontro NIT.") {
            $nodeValues = $crawler->filter('td')->each(function ($node, $i) {
                return $node->text();
            });
            return [
                'success' => "true",
                'name' => $nodeValues[6],
                'type' => $nodeValues[8],
                'direction' =>  $nodeValues[12],
                'state' =>  $nodeValues[10],
                'activity' =>  $nodeValues[18],
            ];
        } else {
            return [
                'success' => "false",
                'message' => "No se encontro el NIT",
            ];
        }
    }

    public function nameByNit($nit)
    {
        $client = new ClientScrap();
        $crawler = $client->request('GET', "https://www.einforma.co/servlet/app/portal/ENTP/prod/LISTA_EMPRESAS/razonsocial/{$nit}");
        $crawler->filter('h1[class="title01"]')->each(function ($node) {
            $this->setNameClient($node->text());
        });
        if (!is_null($this->nameclient)) {
            $arrayName = explode(" ", $name);
            if (count($arrayName) == 1)
                return [
                    'success' => true,
                    'result' => [
                        'primer_nombre' => $arrayName[0],
                        'otros_nombres' => '',
                        'primer_apellido' => '',
                        'segundo_apellido' => ''
                    ]
                ];
            else
               if (count($arrayName) == 2)
                return [
                    'success' => true,
                    'result' => [
                        'primer_nombre' => $arrayName[1],
                        'otros_nombres' => '',
                        'primer_apellido' => $arrayName[0],
                        'segundo_apellido' => ''
                    ]
                ];
            else
                   if (count($arrayName) == 3)
                return [
                    'success' => true,
                    'result' => [
                        'primer_nombre' => $arrayName[2],
                        'otros_nombres' => '',
                        'primer_apellido' => $arrayName[0],
                        'segundo_apellido' => $arrayName[1]
                    ]
                ];
            else
                        if (count($arrayName) == 4)
                return [
                    'success' => true,
                    'result' => [
                        'primer_nombre' => $arrayName[2],
                        'otros_nombres' => $arrayName[3],
                        'primer_apellido' => $arrayName[0],
                        'segundo_apellido' => $arrayName[1]
                    ]
                ];
        } else
            return [
                'success' => false,
                'message' => "No se encontro el NIT."
            ];
    }

    public function query_rut(RutRequest $request)
    {
        try {
            $user = auth()->user();

            $data = [
                'identification_number' => $request->identification_number,
                'dv' => $this->validarDigVerifDIAN($request->identification_number),
                'business_name' => null,
                'email' => null,
                'consultation_date' => date('Y-m-d H:i:s'),
            ];

            // Tipos de documentos
            $typeDocumentIdentification = TypeDocumentIdentification::find($request->type_document_id);

            // clase SOAP
            $getGetAcquirer = new GetAcquirer($user->company->certificate->path, $user->company->certificate->password);
            $getGetAcquirer->identificationNumber = $request->identification_number;
            $getGetAcquirer->identificationType = trim($typeDocumentIdentification->code) ?? 31;

            $response = $getGetAcquirer->signToSend()->getResponseToObject();
            // return [$response];
            if (is_object($response) && property_exists($response, 'Envelope')) {
                $statusCode = $response->Envelope->Body->GetAcquirerResponse->GetAcquirerResult->StatusCode ?? null;
//                $data['status_code_dian'] = (int) $statusCode;
                if ($statusCode === "200") {
                    $receiverEmail = $response->Envelope->Body->GetAcquirerResponse->GetAcquirerResult->ReceiverEmail ?? null;
                    $receiverName  = $response->Envelope->Body->GetAcquirerResponse->GetAcquirerResult->ReceiverName ?? null;

                    $getValue = function ($value) {
                        if (is_object($value) && isset($value->_attributes->nil) && $value->_attributes->nil === 'true') {
                            return null;
                        }
                        return is_string($value) ? $value : null;
                    };

                    $data['business_name'] = $getValue($receiverName);
                    $data['email'] = $getValue($receiverEmail);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "No se encontró información para el tipo: {$typeDocumentIdentification->name} y número de documento {$request->identification_number}",
                        'data' => $data
                    ], 404);
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Información consultada con éxito',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Se ha producido un error al consultar la información.',
                'data' => []
            ], 200);
        }
    }

    public function ValidateMail(ValidateMailRequest $request)
    {
        $data = [];
        $message = null;
        $success = false;
        // Verificar la sintaxis del correo electrÃ³nico
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return ['message' => 'Â¡Estructura del correo no valida!', 'success' => $success, 'data' => $data];
        }
        $data['email'] = [
            'valid' => false,
            'valid_structure' => true,
            'reason' => null,
            'status_code' => null,
            'email' => $request->email,
        ];
        $domain = explode('@', $request->email)[1];
        try {
            $mxRecords = dns_get_record($domain, DNS_MX);
            if (!$mxRecords) {
                $data['mx_records'] = [];
                $data['others'] = [
                    'acceptAll' => (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')),
                    'disposable' =>  checkdnsrr($domain, 'TXT') && preg_match('/^(")?(.*)(\.)?((disposableemail|10minutemail|guerillamail|jetable|discardmail|mailinator|yopmail|sharklasers|guerillamailblock|fakeinbox|maildrop|binkmailfree|deadaddress|dispostable|mailnull|spambog|trashmail)\.(com|org|net|info|biz|us|ru|de))?(?(1)\\1)$/', trim(implode(' ', dns_get_record('_' . $domain . '.', DNS_TXT))), $matches),
                    'free' => checkdnsrr($domain, 'TXT') && preg_match('/^(")?(.*)(\.)?((yahoo|hotmail|aol|msn|gmail|googlemail|live|outlook|gmx|icloud|me|mac|rocketmail|ymail|yahoo|yandex|zoho|mail|inbox|fastmail|protonmail|tutanota|disroot|startmail|posteo|airmail|secmail|keemail)\.(com|org|net|info|biz|us|ru|de))?(?(1)\\1)$/', trim(implode(' ', dns_get_record('_' . $domain . '.', DNS_TXT))), $matches),
                    'dominio' => $domain,
                    'provider' => explode('.', $domain)[count(explode('.', $domain)) - 2] . '.' . explode('.', $domain)[count(explode('.', $domain)) - 1]
                ];
            } else {
                $data['mx_records'] = $mxRecords;
                $data['others'] = [
                    'acceptAll' => (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')),
                    'disposable' =>  checkdnsrr($domain, 'TXT') && preg_match('/^(")?(.*)(\.)?((disposableemail|10minutemail|guerillamail|jetable|discardmail|mailinator|yopmail|sharklasers|guerillamailblock|fakeinbox|maildrop|binkmailfree|deadaddress|dispostable|mailnull|spambog|trashmail)\.(com|org|net|info|biz|us|ru|de))?(?(1)\\1)$/', trim(implode(' ', dns_get_record('_' . $domain . '.', DNS_TXT))), $matches),
                    'free' => checkdnsrr($domain, 'TXT') && preg_match('/^(")?(.*)(\.)?((yahoo|hotmail|aol|msn|gmail|googlemail|live|outlook|gmx|icloud|me|mac|rocketmail|ymail|yahoo|yandex|zoho|mail|inbox|fastmail|protonmail|tutanota|disroot|startmail|posteo|airmail|secmail|keemail)\.(com|org|net|info|biz|us|ru|de))?(?(1)\\1)$/', trim(implode(' ', dns_get_record('_' . $domain . '.', DNS_TXT))), $matches),
                    'dominio' => $domain,
                    'provider' => $mxRecords[0]['target'] ?? explode('.', $domain)[count(explode('.', $domain)) - 2] . '.' . explode('.', $domain)[count(explode('.', $domain)) - 1]
                ];
            }
        } catch (Exception $e) {
            $data['mx_records'] = [];
            $data['email']['valid'] = null;
            $data['email']['reason'] = 'No se encontrarÃ³n registro DNS.';
            $data['others'] = [
                'acceptAll' => (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')),
                'disposable' =>  checkdnsrr($domain, 'TXT') && preg_match('/^(")?(.*)(\.)?((disposableemail|10minutemail|guerillamail|jetable|discardmail|mailinator|yopmail|sharklasers|guerillamailblock|fakeinbox|maildrop|binkmailfree|deadaddress|dispostable|mailnull|spambog|trashmail)\.(com|org|net|info|biz|us|ru|de))?(?(1)\\1)$/', trim(implode(' ', dns_get_record('_' . $domain . '.', DNS_TXT))), $matches),
                'free' => checkdnsrr($domain, 'TXT') && preg_match('/^(")?(.*)(\.)?((yahoo|hotmail|aol|msn|gmail|googlemail|live|outlook|gmx|icloud|me|mac|rocketmail|ymail|yahoo|yandex|zoho|mail|inbox|fastmail|protonmail|tutanota|disroot|startmail|posteo|airmail|secmail|keemail)\.(com|org|net|info|biz|us|ru|de))?(?(1)\\1)$/', trim(implode(' ', dns_get_record('_' . $domain . '.', DNS_TXT))), $matches),
                'dominio' => $domain,
                'provider' => explode('.', $domain)[count(explode('.', $domain)) - 2] . '.' . explode('.', $domain)[count(explode('.', $domain)) - 1]
            ];
            $message = 'Â¡No fue posible validar el correo, no cuenta con registros MX o estan mal configurados!';
            return ['message' => $message, 'success'=>$success, 'data' => $data];
        }
        try {
            $client = new Client();
            $response = $client->post(env('URL_VALIDATE_EMAIL'), [
                'form_params' => [
                    'emails' => $request->email
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $responseEmail = json_decode($response->getBody());
                if (property_exists($responseEmail, 'results')) {
                    $mail = $request->email;
                    $status = $responseEmail->results->$domain->$mail->status;
                    // return $status;
                    if (intval($status) >= 200 && intval($status) <= 250) {
                        $data['email'] = [
                            'valid' => true,
                            'valid_structure' => true,
                            'reason' => $responseEmail->results->$domain->$mail->reason,
                            'status_code' => $status,
                            'email' => $request->email,
                        ];
                        $message = 'Email validado con Ã©xito.';
                        $success = true;
                    } else {
                        $data['email'] = [
                            'valid' => false,
                            'valid_structure' => true,
                            'reason' => $responseEmail->results->$domain->$mail->reason,
                            'status_code' => $status,
                            'email' => $request->email,
                        ];
                        $message = 'No fue posible validar el correo.';
                    }
                }
                if (property_exists($responseEmail, 'failed_domains') && !property_exists($responseEmail, 'results')) {
                    $data['email'] = [
                        'valid' => null,
                        'valid_structure' => true,
                        'reason' => 'No fue posible validar el correo',
                        'status_code' => null,
                        'email' => $request->email,
                    ];
                    $message = 'No fue posible validar el correo.';
                }
            }
        } catch (Exception $e) {
            return ['success' => $success, 'message' => 'No fue posible validar el email ' . $e->getMessage(), 'data' => $data];
        }
        return ['success' => $success, 'message' => $message, 'data' => $data];
    }

    public function load_update_entities(LoadUpdateEntitiesRequest $request){
        try{
            $file = fopen(storage_path('new_entities.csv'), "w");
            fwrite($file, str_replace("\t", ",", base64_decode($request->base64entities)));
            fclose($file);
            $rows = array_map('str_getcsv', file(storage_path('new_entities.csv')));
            $headers_row = array_shift($rows);
            $entities_array = [];
            foreach($rows as $row){
                if(!empty($row)){
                    $entities_array[] = array_combine($headers_row, $row);
                }
            }
            $updated = 0;
            $new = 0;
            foreach($entities_array as $entity){
                $e = Entity::where('identification_number', $entity['identification_number'])->get();
                if(count($e) == 0){
                    $e = new Entity();
                    $new++;
                }
                else{
                    $e = $e->first();
                    $updated++;
                }

                $e->type_organization_id = $entity['type_organization_id'];
                $e->name = $entity['name'];
                $e->type_document_identification_id = $entity['type_document_identification_id'];
                $e->identification_number = $entity['identification_number'];
                $e->department_id = $entity['department_id'];
                $e->municipality_id = $entity['municipality_id'];
                $e->address = $entity['address'];
                $e->email = $entity['email'];
                $e->legal_representative = $entity['legal_representative'];
                $e->phone = $entity['phone'];
                $e->save();
            }
            return [
                    'success' => true,
                    'message' => "Se cargaron/actualizaron satisfactoriamente los registros ingresados...",
                    'new_records' => $new,
                    'updated_records' => $updated,
               ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "No se pudo cargar los registros ingresados en el request, {$e->getLine()} - {$e->getMessage()}"
            ];
        }
    }
}
