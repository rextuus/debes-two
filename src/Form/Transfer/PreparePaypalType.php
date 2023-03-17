<?php

namespace App\Form\Transfer;

use App\Entity\BankAccount;
use App\Entity\Debt;
use App\Entity\PaymentOption;
use App\Service\Transfer\PrepareTransferData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PrepareTransferType
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class PreparePaypalType extends PrepareTransferType
{
}