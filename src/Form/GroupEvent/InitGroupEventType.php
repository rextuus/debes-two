<?php

declare(strict_types=1);

namespace App\Form\GroupEvent;

use App\Entity\PaymentOption;
use App\Entity\User;
use App\Service\GroupEvent\GroupEventData;
use App\Service\GroupEvent\GroupEventInitData;
use App\Service\PaymentOption\BankAccountData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
class InitGroupEventType extends AbstractType
{


    public function __construct(private UserTransformer $transformer) { }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('description', TextType::class, ['label' => 'Beschreibung']);
        $builder->add('selectedUsers', TextType::class);
        // Add the choice field for displaying all users
//        $builder->add('allUsers', ChoiceType::class, [
//            'label' => 'All Users',
//            'choices' => $options['users'], // an array of users to populate the choices
//            'multiple' => false, // allow single selection
//            'expanded' => false, // render as a select dropdown
//        ]);
        $builder->add('submit', SubmitType::class, ['label' => 'Event anlegen']);
        $builder->get('selectedUsers')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupEventInitData::class,
            'requester' => User::class,
            'users' => [], // an empty array by default
        ]);
    }
}