<?php

namespace App\Form\Transfer;

use App\Entity\BankAccount;
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
class PrepareTransferType extends AbstractType
{

    private string $name;

    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['payment_accounts'][0] instanceof BankAccount){
            $this->name = 'transfer_bank';
        }
        $builder
            ->add(
                'paymentOption',
                ChoiceType::class,
                [
                    'choices' => $this->prepareOptions($options['payment_accounts']),
                    'data' => $options['payment_accounts'],
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Überweisung vorbereiten'])
            ->add('decline', SubmitType::class, ['label' => 'Abbrechen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrepareTransferData::class,
            'payment_accounts' => PaymentOption::class,
        ]);
    }

    /**
     * @param PaymentOption[] $paymentOptions
     */
    private function prepareOptions(array $paymentOptions): array
    {
        $choices = [];
        foreach ($paymentOptions as $paymentOption){
            $choiceName = $paymentOption->getDescription();
            $choices[$choiceName] = $paymentOption;
        }
        return $choices;
    }
}