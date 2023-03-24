<?php

namespace App\Form\PaypalAccount;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AbstractPaypalAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('paypalMeLink', TextType::class, ['required' => false])
            ->add('description', TextType::class)
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('preferred', CheckboxType::class, ['required' => false]);
    }
}
