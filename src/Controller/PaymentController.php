<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\PaypalAccount;
use App\Entity\User;
use App\Form\BankAccount\BankAccountCreateType;
use App\Form\BankAccount\BankAccountUpdateType;
use App\Form\PaypalAccount\PaypalAccountCreateType;
use App\Form\PaypalAccount\PaypalAccountUpdateType;
use App\Service\PaymentOption\BankAccountData;
use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\BankAccountUpdateData;
use App\Service\PaymentOption\PaypalAccountCreateData;
use App\Service\PaymentOption\PaypalAccountService;
use App\Service\PaymentOption\PaypalAccountUpdateData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{

    #[Route('/payment/overview', name: 'payment_overview')]
    public function index(BankAccountService $bankAccountService, PaypalAccountService $paypalAccountService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();
        $bankAccounts = $bankAccountService->getBankAccountsOfUser($requester);
        $paypalAccounts = $paypalAccountService->getPaypalAccountsOfUser($requester);

        return $this->render('payment/overview.html.twig', [
            'bankAccounts' => $bankAccounts,
            'paypalAccounts' => $paypalAccounts,
        ]);
    }

    #[Route('/payment/create/bank', name: 'payment_create_bank')]
    public function createNewBankAccount(Request $request, BankAccountService $bankAccountService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $bankAccountData = (new BankAccountData())->initFromUser($requester);
        $form = $this->createForm(BankAccountCreateType::class, $bankAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BankAccountData $data */
            $data = $form->getData();

            $bankAccountService->storeBankAccount($data);

            return $this->redirect($this->generateUrl('payment_overview'));
        }

        return $this->render('payment/bank.create.html.twig', [
            'form' => $form->createView(),
            'descriptionValue' => $bankAccountService->getCurrentPaypalAccountDescriptionHint($requester)
        ]);
    }

    #[Route('/payment/edit/bank/{bankAccount}', name: 'payment_edit_bank')]
    public function updateBankAccount(
        BankAccount $bankAccount,
        Request $request,
        BankAccountService $bankAccountService
    ): Response {
        $bankAccountData = (new BankAccountUpdateData())->initFromEntity($bankAccount);
        $form = $this->createForm(BankAccountUpdateType::class, $bankAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BankAccountUpdateData $data */
            $data = $form->getData();

            $bankAccountService->update($bankAccount, $data);

            return $this->redirect($this->generateUrl('payment_overview'));
        }

        return $this->render('payment/bank.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/payment/create/paypal', name: 'payment_create_paypal')]
    public function createNewPaypalAccount(Request $request, PaypalAccountService $paypalAccountService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $paypalAccountData = (new PaypalAccountCreateData())->initFromUser($requester);
        $form = $this->createForm(PaypalAccountCreateType::class, $paypalAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PaypalAccountCreateData $data */
            $data = $form->getData();

            $paypalAccount = $paypalAccountService->storePaypalAccount($data);

            return $this->redirect($this->generateUrl('payment_overview', ['id' => $paypalAccount->getId()]));
        }

        return $this->render('payment/paypal.create.html.twig', [
            'form' => $form->createView(),
            'descriptionValue' => $paypalAccountService->getCurrentPaypalAccountDescriptionHint($requester)
        ]);
    }

    #[Route('/payment/edit/paypal/{paypalAccount}', name: 'payment_edit_paypal')]
    public function updatePaypalAccount(
        PaypalAccount $paypalAccount,
        Request $request,
        PaypalAccountService $paypalAccountService
    ): Response {
        $bankAccountData = (new PaypalAccountUpdateData())->initFromEntity($paypalAccount);
        $form = $this->createForm(PaypalAccountUpdateType::class, $bankAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PaypalAccountUpdateData $data */
            $data = $form->getData();
            $paypalAccountService->update($paypalAccount, $data);

            return $this->redirect($this->generateUrl('payment_overview', ['paypalAccount' => $paypalAccount->getId()]));
        }

        return $this->render('payment/paypal.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}