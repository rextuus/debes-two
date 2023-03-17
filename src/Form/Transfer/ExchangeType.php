<?php

namespace App\Form\Transfer;

use App\Entity\Debt;
use App\Service\Loan\LoanDto;
use App\Service\Transfer\ExchangeProcessor;
use App\Service\Transfer\PrepareExchangeTransferData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ExchangeType
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * 
 */
class ExchangeType extends AbstractType
{
    public function __construct(private ExchangeProcessor $exchangeProcessor)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'loan',
                ChoiceType::class,
                [
                    'choices' => $this->prepareOptions($options['debt']),
                    'data' => $options['debt'],
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Mit dieser Transaktion verrechnen'])
            ->add('decline', SubmitType::class, ['label' => 'Abbrechen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrepareExchangeTransferData::class,
            'debt' => Debt::class,
        ]);
    }

    private function prepareOptions(Debt $debt): array
    {
        $candidates = $this->exchangeProcessor->findExchangeCandidatesForTransactionPart($debt);
        $choices = [];

        $loans = $candidates->getAllCandidates();
        foreach ($candidates->getAllCandidatesDto() as $nr => $candidate) {
            /** @var LoanDto $candidate */
//            $transaction = $candidate->getTransaction();
            $choiceName = $candidate->getAmount() . ' € für ' . $candidate->getReason() . ' an ' . $candidate->getTransactionPartners();
            $choices[$choiceName] = $loans[$nr];
        }
        return $choices;
    }
}