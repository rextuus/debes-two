<?php

namespace App\Form\BankAccount;

use App\Service\PaymentOption\BankAccountData;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountCreateType extends AbstractBankAccountType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('submit', SubmitType::class, ['label' => 'Bankkonto anlegen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BankAccountData::class,
        ]);
    }
}
