<?php

namespace App\Form\PaypalAccount;

use App\Service\PaymentOption\Form\PaypalAccountUpdateData;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaypalAccountUpdateType extends AbstractPaypalAccountType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('submit', SubmitType::class, ['label' => 'Änderungen sichern']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaypalAccountUpdateData::class,
        ]);
    }
}
