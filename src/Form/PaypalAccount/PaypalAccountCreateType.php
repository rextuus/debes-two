<?php

namespace App\Form\PaypalAccount;

use App\Service\PaymentOption\Form\PaypalAccountCreateData;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaypalAccountCreateType extends AbstractPaypalAccountType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('submit', SubmitType::class, ['label' => 'Paypal-Account anlegen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaypalAccountCreateData::class,
        ]);
    }
}
