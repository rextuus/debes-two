<?php

namespace App\Form;

use App\Entity\User;
use App\Service\Transaction\TransactionCreateDebtorData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionCreateDebtorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (range(1, $options['debtors']) as $debtorNr) {
            $name = sprintf('debtor%d', $debtorNr);
            $builder->add($name, DebtCreateType::class, ['requester' => $options['requester']]);
        }

        $builder->add('submit', SubmitType::class, ['label' => 'Transaktion erstellen']);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransactionCreateDebtorData::class,
            'debtors' => 1,
            'requester' => User::class
        ]);
        $resolver->setAllowedTypes('debtors', 'int');
    }
}