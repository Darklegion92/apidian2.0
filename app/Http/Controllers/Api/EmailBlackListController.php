<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EmailBlackListRequest;
use App\EmailBlackList;
use Exception;
use Illuminate\Http\JsonResponse;

class EmailBlackListController extends Controller
{
    public function add(EmailBlackListRequest $request): JsonResponse
    {
        try {
            $emails = $request->input('emails');

            foreach ($emails as $emailData) {
                EmailBlackList::updateOrCreate(
                    ['email' => $emailData['email']],
                    ['banned' => $emailData['banned']]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Correos creados/actualizados en lista negra con éxito.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(EmailBlackListRequest $request): JsonResponse
    {
        try {
            $emails = $request->input('emails');
            $deletedEmails = [];
            $notFoundEmails = [];

            foreach ($emails as $emailData) {
                $email = EmailBlackList::where('email', $emailData['email'])->first();

                if ($email) {
                    $email->delete();
                    $deletedEmails[] = $emailData['email'];
                } else {
                    $notFoundEmails[] = $emailData['email'];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Proceso de eliminación de la lista negra de correos electronicos completado.',
                'deleted' => $deletedEmails,
                'not_found' => $notFoundEmails,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error',
                'payload' => $e->getMessage(),
            ], 500);
        }
    }
}
