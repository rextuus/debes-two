<?php

declare(strict_types=1);

namespace App\Form\GroupEvent;

use App\Entity\GroupEventUserCollection;
use App\Entity\PaymentOption;
use App\Entity\User;
use App\Form\UserType;
use App\Service\GroupEvent\GroupEventData;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\GroupEvent\Payment\GroupEventPaymentData;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionRepository;
use App\Service\GroupEvent\UserCollection\GroupEventUserCollectionService;
use App\Service\PaymentOption\BankAccountData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
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

        $builder->add('submit', SubmitType::class, ['label' => 'Event anlegen']);

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