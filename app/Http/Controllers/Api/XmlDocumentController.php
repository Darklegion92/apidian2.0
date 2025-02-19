<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetReferenceNotesRequest;
use ubl21dian\Templates\SOAP\GetXmlByDocumentKey;
use App\Http\Requests\Api\XmlDocumentRequest;
use App\Traits\DocumentTrait;
use Illuminate\Support\Facades\Log;
use ubl21dian\Templates\SOAP\GetReferenceNotes;

class XmlDocumentController extends Controller
{
    use DocumentTrait;

    /**
     * Document.
     *
     * @param string $trackId
     *
     * @return array
     */
    public function document(XmlDocumentRequest $request, $trackId, $GuardarEn = false)
    {
        // User
        $user = auth()->user();

        // Company
        $company = $user->company;

        // Verificar la disponibilidad de la DIAN antes de continuar
        $dian_url = $company->software->url;
        if (!$this->verificarEstadoDIAN($dian_url)) {
            // Manejar la indisponibilidad del servicio, por ejemplo:
            return [
                'success' => false,
                'message' => 'El servicio de la DIAN no está disponible en este momento. Por favor, inténtelo más tarde.',
            ];
        }

        // Verify Certificate
        $certificate_days_left = 0;
        $c = $this->verify_certificate();
        if(!$c['success'])
            return $c;
        else
            $certificate_days_left = $c['certificate_days_left'];

        if($request->is_payroll)
            $getXml = new GetXmlByDocumentKey($user->company->certificate->path, $user->company->certificate->password, $user->company->software->url_payroll);
        else
            $getXml = new GetXmlByDocumentKey($user->company->certificate->path, $user->company->certificate->password, $user->company->software->url);
        $getXml->trackId = $trackId;
        $GuardarEn = str_replace("_", "\\", $GuardarEn);

        if ($request->GuardarEn){
            $R = $getXml->signToSend($request->GuardarEn.'\\Req-XmlDocument.xml')->getResponseToObject($request->GuardarEn.'\\Rpta-XmlDocument.xml');
            if($R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Code == "100")
                return [
                    'success' => true,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R,
                    'certificate_days_left' => $certificate_days_left,
                ];
            else
                return [
                    'success' => false,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Message,
                    'certificate_days_left' => $certificate_days_left,
                ];
        }
        else{
            $R = $getXml->signToSend()->getResponseToObject();
            if($R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Code == "100")
                return [
                    'success' => true,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R,
                    'certificate_days_left' => $certificate_days_left,
                ];
            else
                return [
                    'success' => false,
                    'message' => 'Consulta generada con éxito',
                    'ResponseDian' => $R->Envelope->Body->GetXmlByDocumentKeyResponse->GetXmlByDocumentKeyResult->Message,
                    'certificate_days_left' => $certificate_days_left,
                ];
        }
    }

    public function getReferenceNotes($trackId)
    {
        try {
            // User
            $user = auth()->user();

            // Verify Certificate
            $certificate_days_left = 0;
            $c = $this->verify_certificate();
            if (!$c['success'])
                return $c;
            else
                $certificate_days_left = $c['certificate_days_left'];

            // Get Reference Notes
            $getRefereceNotes = new GetReferenceNotes($user->company->certificate->path, $user->company->certificate->password, $user->company->software->url);
            $getRefereceNotes->trackId = $trackId;


            $response = $getRefereceNotes->signToSend()->getResponseToObject();
            if (!isset($response->Envelope->Body->GetReferenceNotesResponse)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Servicio DIAN no disponible, intente más tarde.',
                    'ResponseDian' => null,
                    'certificate_days_left' => $certificate_days_left,
                ], 404);
            }
            // return [$response->Envelope];
            if ($response->Envelope->Body->GetReferenceNotesResponse->GetReferenceNotesResult->StatusCode != "00") {
                return response()->json([
                    'success' => true,
                    'message' => $response->Envelope->Body->GetReferenceNotesResponse->GetReferenceNotesResult->StatusDescription ?? 'No se encontró el CUFE en los registros DIAN.',
                    'document' => [
                        'issue_date' => null,
                        'profile_execution_id' => null,
                        'uuid' => $trackId,
                        'notes' => []
                    ],
                    // 'ResponseDian' => $response,
                    'certificate_days_left' => $certificate_days_left,
                ], 404);
            } else {

                // Decodificar el XML base64
                $invoiceXMLStr = preg_replace('/<\?xml[^>]+?>\s*/', '', base64_decode($response->Envelope->Body->GetReferenceNotesResponse->GetReferenceNotesResult->XmlBase64Bytes));
                $documentResponses = $this->ValueXMLMultiple($invoiceXMLStr, "/cac:DocumentResponse/") ?? [];
                // Log::debug($invoiceXMLStr);
                $notes = [];
                foreach ($documentResponses as $documentResponse) {
                    // Log::debug($documentResponse);
                    $notesData = [
                        // 'reference_id'   => $this->ValueXML($documentResponse, '/cac:Response/cbc:ReferenceID/'),
                        'response_code'  => $this->ValueXML($documentResponse, '/cac:Response/cbc:ResponseCode/'),
                        'description'   => $this->ValueXML($documentResponse, '/cac:Response/cbc:Description/'),
                        'effective_date' => $this->ValueXML($documentResponse, '/cac:Response/cbc:EffectiveDate/'),
                        'effective_time' => $this->ValueXML($documentResponse, '/cac:Response/cbc:EffectiveTime/'),
                        'id' => $this->ValueXML($documentResponse, '/cac:DocumentReference/cbc:ID/'),
                        'uuid' => $this->ValueXML($documentResponse, '/cac:DocumentReference/cbc:UUID/'),
                        'issuer_party' => [
                            'identification_number' => $this->ValueXML($documentResponse, '/cac:IssuerParty/cac:PartyTaxScheme/cbc:CompanyID/'),
                            'name' => $this->ValueXML($documentResponse, '/cac:IssuerParty/cac:PartyTaxScheme/cbc:RegistrationName/'),
                        ],
                        'recipient_party' => [
                            'identification_number' => $this->ValueXML($documentResponse, '/cac:RecipientParty/cac:PartyTaxScheme/cbc:CompanyID/'),
                            'name' => $this->ValueXML($documentResponse, '/cac:RecipientParty/cac:PartyTaxScheme/cbc:RegistrationName/'),
                        ],
                        'line_response' => [
                            'response_code' => $this->ValueXML($documentResponse, '/cac:LineResponse/cac:Response/cbc:ResponseCode/'),
                            'description' => $this->ValueXML($documentResponse, '/cac:LineResponse/cac:Response/cbc:Description/'),
                        ]
                    ];
                    $notes[] = $notesData;
                }
                return response()->json([
                    'success' => true,
                    'message' => $response->Envelope->Body->GetReferenceNotesResponse->GetReferenceNotesResult->StatusDescription,
                    'document' => [
                        'issue_date' => $this->ValueXML($invoiceXMLStr, '/cbc:IssueDate/') . ' ' . $this->ValueXML($invoiceXMLStr, '/cbc:IssueTime/'),
                        'profile_execution_id' => $this->ValueXML($invoiceXMLStr, '/cbc:ProfileExecutionID/'),
                        'uuid' => $trackId,
                        'notes' => $notes
                    ],
                    'certificate_days_left' => $certificate_days_left,
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Ha ocurrido un error al consultar las notas de la factura, por favor intenten más tarde.',
                'document' => [
                    'issue_date' => null,
                    'profile_execution_id' => null,
                    'uuid' => $trackId,
                    'notes' => []
                ],
                'certificate_days_left' => $certificate_days_left,
            ], 503);
        }
    }
}
