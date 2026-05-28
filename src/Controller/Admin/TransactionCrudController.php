<?php

namespace App\Controller\Admin;

use App\Admin\CustomAction;
use App\Entity\PaymentAction;
use App\Entity\Transaction;
use App\Service\PaymentOption\PaymentOptionService;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transfer\TransferService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TransactionCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TransferService $transferService,
        private readonly PaymentOptionService $paymentOptionService,
        private readonly TransactionProcessor $transactionProcessor
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Transaction::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
    public function configureActions(Actions $actions): Actions
    {
        $this->addAcceptAction($actions);
        $this->addClearAction($actions);

        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    private function addAcceptAction(Actions $actions): void
    {
        $acceptTransaction = Action::new('acceptTransaction', 'Accept')
            ->linkToCrudAction('acceptTransaction')
            ->addCssClass('btn btn-warning')
            ->displayIf(static function (Transaction $entity): bool {
                return $entity->getState() === Transaction::STATE_READY;
            });
        $actions->add(Crud::PAGE_EDIT, $acceptTransaction);
        $actions->add(Crud::PAGE_INDEX, $acceptTransaction);
        $actions->add(Crud::PAGE_DETAIL, $acceptTransaction);
    }

    private function addClearAction(Actions $actions): void
    {
        $processTransaction = Action::new('processTransaction', 'Clear', 'fa fa-file-invoice')
            ->linkToCrudAction('processTransaction')
            ->addCssClass('btn btn-warning')
            ->displayIf(static function (Transaction $entity): bool {
                return $entity->getState() === Transaction::STATE_ACCEPTED;
            });

        $actions->add(Crud::PAGE_EDIT, $processTransaction);
        $actions->add(Crud::PAGE_INDEX, $processTransaction);
        $actions->add(Crud::PAGE_DETAIL, $processTransaction);
    }

    public function acceptTransaction(
        AdminContext $adminContext,
        AdminUrlGenerator $adminUrlGenerator
    ): RedirectResponse {
        dd($adminContext);
        $transaction = $adminContext->getEntity()->getInstance();

        if (!$transaction instanceof Transaction) {
            throw new \Exception('Entity needs to be instance of ' . Transaction::class);
        }

        $targetUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_EDIT)
            ->setEntityId($transaction->getId())
            ->generateUrl();

        $debt = $transaction->getDebts()[0] ?? null;
        if (!$debt) {
            throw new \LogicException('No debt found for transaction');
        }

        $this->transactionProcessor->accept($debt);

        return $this->redirect($targetUrl);
    }

    public function processTransaction(
        AdminContext $adminContext,
        AdminUrlGenerator $adminUrlGenerator
    ): RedirectResponse {
        $transaction = $adminContext->getEntity()->getInstance();
        if (!$transaction instanceof Transaction) {
            throw new \LogicException('Entity needs to be instance of ' . Transaction::class);
        }

        $targetUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_EDIT)
            ->setEntityId($transaction->getId())
            ->generateUrl();

        $loaner = $transaction->getLoaner();
        $debtor = $transaction->getDebtor();

        $paymentOptionSummaryContainer = $this->paymentOptionService->getActivePaymentOptionsOfUser($loaner, $debtor);

        $senderBankAccount = $paymentOptionSummaryContainer->getAvailableBankAccountsDebtor()[0];
        $receiverBankAccount = $paymentOptionSummaryContainer->getAvailableBankAccountsLoaner()[0];
        if ($senderBankAccount === null || $receiverBankAccount === null) {
            throw new \LogicException('Bank account not found for transaction');
        }

        $debt = $transaction->getDebts()[0] ?? null;
        if (!$debt) {
            throw new \LogicException('No debt found for transaction');
        }

        $this->transferService->createPaymentActionByPaymentOption(
            $transaction,
            $senderBankAccount,
            $receiverBankAccount,
            $debt,
            PaymentAction::VARIANT_ADMIN
        );

        return $this->redirect($targetUrl);
    }
}
