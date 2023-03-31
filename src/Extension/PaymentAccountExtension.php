<?php
declare(strict_types=1);

namespace App\Extension;

use App\Entity\BankAccount;
use App\Entity\PaypalAccount;
use App\Service\PaymentOption\PaymentOptionDTO;
use App\Service\Util\TimeConverter;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class PaymentAccountExtension extends AbstractExtension
{
    public function __construct(private Environment $environment, private TimeConverter $timeConverter)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('bank_account_content_card', [$this, 'renderBankAccountContentCard']),
            new TwigFunction('paypal_account_content_card', [$this, 'renderPaypalAccountContentCard']),
        ];
    }

    public function renderBankAccountContentCard(BankAccount $bankAccount){

        return $this->environment->render(
            'extension/payment/bank_account_card.html.twig',
            [
                'bankAccount' => $bankAccount
            ]
        );
    }

    public function renderPaypalAccountContentCard(PaypalAccount $paypalAccount){

        return $this->environment->render(
            'extension/payment/paypal_account_card.html.twig',
            [
                'paypalAccount' => $paypalAccount
            ]
        );
    }
}