<?php

namespace App\Form\BankAccount;

use App\Service\PaymentOption\Form\BankAccountData;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountCreateType extends AbstractBankAccountType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('submit', SubmitType::class, ['label' => 'Bankkonto anlegen']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAccountData::class,
        ]);
    }
}
