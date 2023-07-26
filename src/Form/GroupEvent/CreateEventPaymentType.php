<?php

declare(strict_types=1);

namespace App\Form\GroupEvent;

use App\Entity\GroupEventUserCollection;
use App\Entity\User;
use App\Service\GroupEvent\Payment\Form\GroupEventPaymentData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CreateEventPaymentType extends AbstractType
{


    public function __construct(private GroupEventUserCollectionRepository $groupEventUserCollectionRepository) { }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
//        $builder->add('groupEvent', TextType::class, ['label' => 'Beschreibung']);
        $builder->add('amount', TextType::class);
//        $builder->add('loaner', UserType::class);
        $builder->add('debtors', NumberType::class, ['label' => ' ']);
        $builder->add('reason', TextType::class);

        $builder->add('submit', SubmitType::class, ['label' => 'Zahlung hinzufügen']);

        $builder->get('debtors')->addModelTransformer(
            new CallbackTransformer(
                function (GroupEventUserCollection $groupEventUserCollection): int {
                    // transform the string back to an array
                    return $groupEventUserCollection->getId();
                },
                function (int $id): GroupEventUserCollection {
                    return $this->groupEventUserCollectionRepository->find($id);
                }
            )
        );

        $builder->get('amount')->addModelTransformer(
            new CallbackTransformer(
                function ($currency): string {
                    // transform the string back to an array
                    return $currency . ' €';
                },
                function ($stringCurrency): float {
                    return (float)str_replace(' €', '', $stringCurrency);
                }
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupEventPaymentData::class,
            'users' => [],
            'requester' => User::class,
        ]);
    }
}