<?php

namespace App\Form;

use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\PaymentOptionService;
use App\Service\PaymentOption\PaypalAccountCreateData;
use App\Service\PaymentOption\PaypalAccountService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaypalAccountCreateType extends AbstractType
{
    /**
     * PaypalAccountCreateType constructor.
     */
    public function __construct(
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('description', TextType::class)
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('preferred', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Paypal registrieren']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaypalAccountCreateData::class,
        ]);
    }
}
