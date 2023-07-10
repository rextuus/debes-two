<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ChoiceType
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class SingleChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('submit', SubmitType::class, ['label' => $options['label']['submit']]);
    }
}