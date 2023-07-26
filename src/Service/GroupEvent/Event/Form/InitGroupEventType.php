<?php

declare(strict_types=1);

namespace App\Service\GroupEvent\Event\Form;

use App\Entity\User;
use App\Form\GroupEvent\UserTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class InitGroupEventType extends AbstractType
{
    public function __construct(private UserTransformer $transformer) { }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('description', TextType::class, ['label' => 'Beschreibung']);
        $builder->add('selectedUsers', TextType::class);
        $builder->add('open', CheckboxType::class);
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