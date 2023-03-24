<?php

namespace App\Form\BankAccount;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractBankAccountType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('iban', TextType::class)// TODO IBAN + BIC CHECKER
            ->add('bic', TextType::class)
            ->add('accountName', TextType::class)
            ->add('bankName', TextType::class)
            ->add('description', TextType::class)
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('preferred', CheckboxType::class, ['required' => false]);
    }
}
