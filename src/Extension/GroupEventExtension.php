<?php

namespace App\Extension;

use App\Entity\GroupEvent;
use App\Entity\User;
use App\Service\GroupEvent\Payment\GroupEventParticipantDto;
use App\Service\GroupEvent\Payment\GroupEventPaymentDto;
use Doctrine\ORM\PersistentCollection;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GroupEventExtension extends AbstractExtension
{
    private Environment $environment;

    /**
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_names', [$this, 'getUserNameString']),
            new TwigFunction('user_ids', [$this, 'getUserIdsString']),
            new TwigFunction('render_payment_entry', [$this, 'renderPaymentEntry']),
            new TwigFunction('render_participant_list', [$this, 'renderParticipantList']),
        ];
    }

    public function renderPaymentEntry(GroupEvent $event): string
    {
        $groupColorClasses = [];
        foreach ($event->getParticipantGroups() as $index => $group) {
            $groupColorClasses[$group->getId()] = 'event-group-color-' . $index + 1;
        }

        $dtos = [];
        foreach ($event->getPayments() as $payment) {
            $dto = new GroupEventPaymentDto();
            $dto->setReason($payment->getReason());
            $dto->setLoaner($payment->getLoaner()->getFullName());
            $dto->setGroupName($payment->getDebtors()->getName());
            $users = array_map(function (User $user) {
                return $user->getFullName();
            }, $payment->getDebtors()->getUsers()->toArray());
            $dto->setMembers($users);
            $dto->setAmount($payment->getAmount());
            $splitAmount = $payment->getAmount() / count($users);
            $dto->setSplitAmount(sprintf('%.2f', $splitAmount));
            $dto->setGroupColorClass($groupColorClasses[$payment->getDebtors()->getId()]);

            $dtos[] = $dto;
        }

        return $this->environment->render(
            'event/event.extension_payment.html.twig',
            [
                'dtos' => $dtos,
            ]
        );
    }

    public function renderParticipantList(GroupEvent $event): string{
        $dtos = [];
        $totalAmount = 0.0;

        foreach ($event->getUsers() as $user){
            $dto = new GroupEventParticipantDto();
            $dto->setName($user->getFullName());
            $dto->setAmount(0.0);
            $dtos[$user->getId()] = $dto;
        }

        foreach ($event->getPayments() as $payment){
            $totalAmount = $totalAmount + $payment->getAmount();

            foreach ($payment->getDebtors()->getUsers() as $debtor){
                $splitAmount = $payment->getAmount() / count($payment->getDebtors()->getUsers());
                $dtos[$debtor->getId()] = $dtos[$debtor->getId()]->setAmount($dtos[$debtor->getId()]->getAmount() + $splitAmount);
            }
        }

        return $this->environment->render(
            'event/event.extension_participant_list.html.twig',
            [
                'event' => $event,
                'dtos' => $dtos,
                'totalAmount' => $totalAmount
            ]
        );
    }

    /**
     * @param PersistentCollection $users
     */
    public function getUserNameString(PersistentCollection $users): string
    {
        $array = $users->toArray();
        $array = array_map(function (User $user) {
            return $user->getFullName();
        }, $array);
        return implode(',', $array);
    }

    /**
     * @param PersistentCollection $users
     */
    public function getUserIdsString(PersistentCollection $users): string
    {
        $array = $users->toArray();
        $array = array_map(function (User $user) {
            return $user->getId();
        }, $array);
        return implode(',', $array);
    }
}
