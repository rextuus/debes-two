<?php

namespace App\Form\BankAccount;

use App\Service\PaymentOption\Form\BankAccountUpdateData;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountUpdateType extends AbstractBankAccountType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('submit', SubmitType::class, ['label' => 'Änderungen speichern']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAccountUpdateData::class,
        ]);
    }
}
