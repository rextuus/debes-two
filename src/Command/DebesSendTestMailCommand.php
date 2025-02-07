<?php

namespace App\Command;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Mailer\MailService;
use App\Service\Transaction\TransactionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'debes:send-test-mail',
    description: 'Add a short description for your command',
)]
class DebesSendTestMailCommand extends Command
{


    public function __construct(
        private readonly MailService $mailService,
        private readonly TransactionService $transactionService
    ) {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $transaction = $this->transactionService->getTransactionById(1);
        $transaction->getLoaner()->setEmail('wrextuus@gmail.com');
        $transaction->getDebtor()->setEmail('wrextuus@gmail.com');

        $this->mailService->sendNotificationMail(
            $transaction,
            MailService::MAIL_DEBT_CREATED
        );

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
