<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\PaymentAction;
use App\Entity\PaymentOption;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\ChoiceType;
use App\Form\Transfer\ExchangeType;
use App\Form\Transfer\PrepareBankType;
use App\Form\Transfer\PreparePaypalType;
use App\Service\Debt\DebtService;
use App\Service\Loan\LoanService;
use App\Service\Mailer\MailService;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\PaypalAccountService;
use App\Service\Transaction\DtoProvider;
use App\Service\Transaction\TransactionService;
use App\Service\Transfer\ExchangeProcessor;
use App\Service\Transfer\PrepareExchangeTransferData;
use App\Service\Transfer\PrepareTransferData;
use App\Service\Transfer\SendTransferDto;
use App\Service\Transfer\TransferService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AbstractController
{
    /**
     * TransactionController constructor.
     */
    public function __construct(
        private TransactionService   $transactionService,
        private MailService          $mailService,
        private PaymentActionService $paymentActionService,
        private TransferService      $transferService,
        private BankAccountService   $bankAccountService
    )
    {
    }

    #[Route('/transfer/prepare/{slug}', name: 'transfer_prepare')]
    public function prepareTransfer(
        Transaction     $transaction,
        Request         $request,
        TransferService $transferService
    ): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $isDebtor = $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $data = (new PrepareTransferData());
        $default = $transferService->getDefaultPaymentOptionForUser($requester);
        if (!$default) {
            throw new Exception('user has no payment option defined or enabled');
        }
        $data->setPaymentOption($default);

        $form = $this->createForm(
            PrepareTransferType::class,
            $data,
            ['label' => ['transaction' => $transferService->getAvailablePaymentMethodsForTransaction($transaction)]]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isDeclined = (bool)$form->get('decline')->isClicked();
            if ($isDeclined) {
                return $this->redirectToRoute('account_debts', []);
            }

            /** @var PrepareTransferData $data */
            $data = $form->getData();
            if ($data->getPaymentOption() instanceof BankAccount) {
                return $this->redirectToRoute('transfer_send_bank', [
                    'slug' => $transaction->getSlug(),
                    'senderBankAccount' => $data->getPaymentOption()->getId(),
                ]);
            } elseif ($data->getPaymentOption() instanceof PaypalAccount) {
                return $this->redirectToRoute('transfer_send_paypal', [
                    'slug' => $transaction->getSlug(),
                    'senderPaypalAccount' => $data->getPaymentOption()->getId(),
                ]);
            }
        }

        $dto = $this->transactionService->createDtoFromTransaction($transaction, $isDebtor);

        return $this->render('transfer/prepare.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto,
            'debtVariant' => $isDebtor,
        ]);
    }

    #[Route('/transfer/bank/{slug}/{senderBankAccount}', name: 'transfer_send_bank')]
    public function sendTransferBank(
        Transaction $transaction,
        BankAccount $senderBankAccount,
        Request     $request,
        DtoProvider $dtoProvider
    ): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        // todo check if Transaction has correct state if multiple
        $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);
        if (is_null($debt)) {
            throw new Exception('User has no debt part in this transaction');
        }


        $receiverBankAccount = $this->bankAccountService->getActiveBankAccountForUser(
            $transaction->getLoans()[0]->getOwner()
        );

        $dto = $this->prepareTransferDto($receiverBankAccount, $transaction);

        $labels = ['label' => ['submit' => 'Erledigt', 'decline' => 'Abbrechen']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted) {
                // store payment for history
                $this->transferService->createPaymentActionByPaymentOption(
                    $transaction,
                    $senderBankAccount,
                    $receiverBankAccount,
                    $debt,
                    PaymentAction::VARIANT_BANK
                );
            }
            return $this->redirectToRoute('account_debts', []);
        }

        return $this->render('transfer/send.bank.html.twig', [
            'dto'  => $dto,
            'form' => $form->createView(),
        ]);
    }

    #[Route('transfer/paypal/{slug}/{senderPaypalAccount}', name: 'transfer_send_paypal')]
    public function sendTransferPaypal(
        Transaction          $transaction,
        PaypalAccount        $senderPaypalAccount,
        Request              $request,
        PaypalAccountService $paypalAccountService,
        DtoProvider          $dtoProvider
    ): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        // todo check if Transaction has correct state if multiple
        $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);
        if (is_null($debt)) {
            throw new Exception('User has no debt part in this transaction');
        }

        $receiverPaypalAccount = $paypalAccountService->getPaypalAccountForUser($transaction->getLoans()[0]->getOwner());

//        $dto = $this->prepareTransferDto($receiverPaypalAccount, $transaction);
        $dto = $dtoProvider->createTransactionDto($transaction, true);

        $labels = ['label' => ['submit' => 'Erledigt', 'decline' => 'Abbrechen']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted) {
                // store payment for history
                $this->transferService->createPaymentActionByPaymentOption(
                    $transaction,
                    $senderPaypalAccount,
                    $receiverPaypalAccount,
                    $debt,
                    PaymentAction::VARIANT_PAYPAL
                );
            }
            return $this->redirectToRoute('account_debts', []);
        }

        return $this->render('transfer/send.paypal.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    #[Route('transfer/prepare/exchange/{slug}', name: 'exchange_prepare')]
    public function prepareExchange(
        Transaction       $transaction,
        Request           $request,
        ExchangeProcessor $exchangeProcessor,
        DtoProvider       $dtoProvider
    ): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);
        $candidates = $exchangeProcessor->findExchangeCandidatesForTransactionPart($debt);

        if (empty($candidates->getAllCandidates())) {
            $this->addFlash('success', 'Article Created! Knowledge is power!');
            return $this->redirectToRoute('account_debts', []);
        }

        $data = new PrepareExchangeTransferData();
        $form = $this->createForm(
            ExchangeType::class,
            $data,
            ['debt' => $debt]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();
            if ($isAccepted) {
                if (!$data->getLoan()) {
                    return $this->redirectToRoute('account_debts', []);
                }

                /** @var PrepareExchangeTransferData $data */
                $data = $form->getData();
                $loanToExchange = $data->getLoan();

                $debtId = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester)->getId();
                $loanId = $loanToExchange->getId();
                $session = $request->getSession();
                $session->set('debt_id', $debtId);
                $session->set('loan_id', $loanId);
                return $this->redirectToRoute(
                    'exchange_accept',
                    []
                );
            }
            return $this->redirectToRoute('account_debts', []);
        }

        $dto = $dtoProvider->createTransactionDto($transaction, true);
        return $this->render('transfer/prepare.exchange.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/accept/exchange', name: 'exchange_accept')]
    public function acceptExchange(
        Request           $request,
        ExchangeProcessor $exchangeService,
        DebtService       $debtService,
        LoanService       $loanService,
        DtoProvider       $dtoProvider
    ): Response
    {
        $session = $request->getSession();
        $debtId = $session->get('debt_id');
        $loanId = $session->get('loan_id');
        if (is_null($debtId) || is_null($loanId)) {
            return $this->redirectToRoute('landing', []);
        }

        $debt = $debtService->getDebtById($debtId);
        $loan = $loanService->getLoanById($loanId);

        $dto = $exchangeService->calculateExchange($debt->getTransaction(), $loan->getTransaction());
        $labels = ['label' => ['submit' => 'Verrechnen', 'decline' => 'Zurück zur Auswahl']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted) {
                $exchangeService->exchangeTransactionParts($debt, $loan);
                return $this->redirectToRoute('account_debts', []);
            } else {
                return $this->redirectToRoute('transfer_overview', ['slug' => $debt->getTransaction()->getSlug(), 'tabId' => 3]);
            }
        }

        return $this->render('transfer/accept.exchange.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto,
        ]);
    }

    #[Route('/transfer/overview/{slug}', name: 'transfer_overview')]
    public function equalizeDebt(
        Transaction       $transaction,
        Request           $request,
        DtoProvider       $dtoProvider,
        ExchangeProcessor $exchangeProcessor,
    ): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);

        $paymentOptions = $this->transferService->getAvailablePaymentMethodsForTransaction($transaction);

        // prepare bank
        $showBank = $paymentOptions->bothHaveBankOptions();

        $bankData = (new PrepareTransferData());
        $bankData->setPaymentOption($paymentOptions->getAvailableBankAccountsDebtor()[0]);
        $bankForm = $this->createForm(
            PrepareBankType::class,
            $bankData,
            ['payment_accounts' => $paymentOptions->getAvailableBankAccountsDebtor()]
        );

        // prepare paypal
        $showPaypal = $paymentOptions->bothHavePaypalOptions();

        $paypalData = (new PrepareTransferData());
        $paypalData->setPaymentOption($paymentOptions->getAvailablePaypalAccountsDebtor()[0]);
        $paypalForm = $this->createForm(
            PreparePaypalType::class,
            $paypalData,
            ['payment_accounts' => $paymentOptions->getAvailablePaypalAccountsDebtor()]
        );

        // prepare exchange tab
        $candidates = $exchangeProcessor->findExchangeCandidatesForTransactionPart($debt);
        $showExchange = (bool)count($candidates->getAllCandidates());

        $exchangeData = new PrepareExchangeTransferData();
        $exchangeForm = $this->createForm(
            ExchangeType::class,
            $exchangeData,
            ['debt' => $debt]
        );

        // prepare dto and
        $dto = $dtoProvider->createTransactionDto($transaction, true);

        // handle the submission of the forms
        if ($request->isMethod('POST')) {
            $innerRequest = $request->request;

            $bankIsSubmitted = false;
            if ($innerRequest->has('prepare_bank')) {
                $prepareBank = $innerRequest->all('prepare_bank');
                if (array_key_exists('submit', $prepareBank)) {
                    $bankIsSubmitted = true;
                }
            }

            $paypalIsSubmitted = false;
            if ($innerRequest->has('prepare_paypal')) {
                $preparePaypal = $innerRequest->all('prepare_paypal');
                if (array_key_exists('submit', $preparePaypal)) {
                    $paypalIsSubmitted = true;
                }
            }

            $exchangeIsSubmitted = false;
            if ($innerRequest->has('exchange')) {
                $prepareExchange = $innerRequest->all('exchange');
                if (array_key_exists('submit', $prepareExchange)) {
                    $exchangeIsSubmitted = true;
                }
            }

            if ($bankIsSubmitted) {
                $bankForm->handleRequest($request);
                if ($bankForm->isSubmitted() && $bankForm->isValid()) {
                    // process the bank form data and redirect or render a response
                    dd($bankData);
                }
            } elseif ($paypalIsSubmitted) {
                $paypalForm->handleRequest($request);
                if ($paypalForm->isSubmitted() && $paypalForm->isValid()) {
                    // process the paypal form data and redirect or render a response
                    dd('Paypal');
                }
            } elseif ($exchangeIsSubmitted) {
                $exchangeForm->handleRequest($request);
                if ($exchangeForm->isSubmitted() && $exchangeForm->isValid()) {
                    /** @var PrepareExchangeTransferData $bankData */
                    $bankData = $exchangeForm->getData();
                    $loanToExchange = $bankData->getLoan();

                    $debtId = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester)->getId();
                    $loanId = $loanToExchange->getId();
                    $session = $request->getSession();
                    $session->set('debt_id', $debtId);
                    $session->set('loan_id', $loanId);
                    return $this->redirectToRoute(
                        'exchange_accept',
                        []
                    );
                }
            }
        }

        $tabPreferences = $this->prepareTabReferences($request->get('tabId') ?: 1);

        return $this->render(
            'transaction/transaction.process.base.html.twig',
            array_merge(
                [
                    'dto' => $dto,
                    'exchangeForm' => $exchangeForm->createView(),
                    'showExchange' => $showExchange,
                    'bankForm' => $bankForm->createView(),
                    'showBank' => $showBank,
                    'paypalForm' => $paypalForm->createView(),
                    'showPaypal' => $showPaypal,
                ],
                $tabPreferences
            )
        );
    }

    private function prepareTabReferences(int $tabId): array
    {
        $tabs = ['active', '', ''];
        $icon = 'assets/img/bank-account.svg';
        $tabName = 'Bank';
        switch ($tabId) {
            case 2:
                $tabs = ['', 'active', ''];
                $icon = 'assets/img/paypal-account.svg';
                $tabName = 'Paypal';
                break;
            case 3:
                $tabs = ['', '', 'active'];
                $icon = 'assets/img/exchange.svg';
                $tabName = 'Verrechnung';
                break;
        }

        return [
            'tabClasses' => $tabs,
            'icon' => $icon,
            'tabName' => $tabName
        ];
    }

    protected function prepareTransferDto(PaymentOption $receiverBankAccount, Transaction $transaction): SendTransferDto
    {
        $dto = (new SendTransferDto)->initFrom($receiverBankAccount);
        $dto->setAmount($transaction->getLoans()[0]->getAmount());
        $dto->setReason($transaction->getReason());
        $dto->setTransactionId($transaction->getId());
        return $dto;
    }
}
