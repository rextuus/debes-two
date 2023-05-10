<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserNotCorrectParticipantOfTransaction;
use App\Service\Transaction\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class ErrorController extends AbstractController
{
    public function show(Request $request): Response
    {
        $exception = $request->get('exception');
        $requestUri = $request->getRequestUri();
        if ($exception instanceof UserNotCorrectParticipantOfTransaction) {
//            if ($exception->getMessage() === TransactionService::ERROR_MESSAGE_NO_DEBTOR) {
//                $message = 'Sorry. Hierfür hast du keine Rechte. Hier gehts zurück';
//            }

            return $this->render('exception/no_participant.html.twig', [

            ]);
        }

        dd($exception);
    }
}
