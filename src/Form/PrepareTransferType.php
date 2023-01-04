<?php

namespace App\Form;

use App\Service\Transfer\PrepareTransferData;
use App\Service\Transfer\TransferService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PrepareTransferType
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class PrepareTransferType extends AbstractType
{
    /**
     * @var TransferService
     */
    private $transferService;

    /**
     * PrepareTransferType constructor.
     */
    public function __construct(TransferService $paymentOptionService)
    {
        $this->transferService = $paymentOptionService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'paymentOption',
                ChoiceType::class,
                [
                    'choices' => $options['label']['transaction'],
                    'data' => $options['label']['transaction'],
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Überweisung vorbereiten'])
            ->add('decline', SubmitType::class, ['label' => 'Abbrechen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrepareTransferData::class,
        ]);
    }
}