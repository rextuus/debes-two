<?php

namespace App\Extension;

use App\Entity\GroupEvent;
use App\Entity\GroupEventUserCollection;
use App\Entity\User;
use App\Service\GroupEvent\Payment\GroupEventParticipantDto;
use App\Service\GroupEvent\Payment\GroupEventPaymentDto;
use App\Service\GroupEvent\UserCollection\UserCollectionDto;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GroupEventExtension extends AbstractExtension
{
    public function __construct(private Environment $environment, private Security $security)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_names', [$this, 'getUserNameString']),
            new TwigFunction('user_ids', [$this, 'getUserIdsString']),
            new TwigFunction('render_payment_entry', [$this, 'renderPaymentEntry']),
            new TwigFunction('render_participant_list', [$this, 'renderParticipantList']),
            new TwigFunction('render_group_fields', [$this, 'renderGroupFields']),
            new TwigFunction('render_calculation_user_entry', [$this, 'renderCalculationUserEntry']),
        ];
    }

    public function renderPaymentEntry(GroupEvent $event): string
    {
        $groupColorClasses = [];
        foreach ($event->getUserGroups() as $index => $group) {
            $groupColorClasses[$group->getId()] = 'event-group-color-' . $index + 1;
        }

        $dtos = [];
        foreach ($event->getGroupEventPayments() as $payment) {
            $dto = new GroupEventPaymentDto();
            $dto->setReason($payment->getReason());
            $dto->setLoaner($payment->getLoaner()->getFullName());
            $groupName = '';

            // check if we need to change the other group name
            if ($payment->getDebtors()->isAllOther()){
                $user = $this->security->getUser();
                $allUser = $payment->getGroupEvent()->getUsers();
                if (count($allUser) - 1 === count($payment->getDebtors()->getUsers()) && !in_array($user, $payment->getDebtors()->getUsers()->toArray())) {
                    $groupName = 'Alle außer dir';
                }
            }
            if ($payment->getDebtors()->isInitial()){
                $groupName = 'Alle';
            }
            // TODO get name from new entity
            if (empty($groupName)){
                $groupName = (sprintf('Du + %d weitere', count($payment->getDebtors()->getUsers()->toArray())-1));
            }


            $dto->setGroupName($groupName);
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
            'event/extension/event.extension_payment.html.twig',
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

        foreach ($event->getGroupEventPayments() as $payment){
            $totalAmount = $totalAmount + $payment->getAmount();

            foreach ($payment->getDebtors()->getUsers() as $debtor){
                $splitAmount = $payment->getAmount() / count($payment->getDebtors()->getUsers());
                $dtos[$debtor->getId()] = $dtos[$debtor->getId()]->setAmount($dtos[$debtor->getId()]->getAmount() + $splitAmount);
            }
        }

        return $this->environment->render(
            'event/extension/event.extension_participant_list.html.twig',
            [
                'event' => $event,
                'dtos' => $dtos,
                'totalAmount' => $totalAmount
            ]
        );
    }

    public function renderGroupFields(GroupEvent $event): string{
        $dtos = [];

        foreach ($event->getUserGroups()->toArray() as $index => $group){
            /** @var GroupEventUserCollection $group */
            $dto = new UserCollectionDto();
            $dto->setName('');
            if ($group->isAllOther()){
                $user = $this->security->getUser();
                $allUser = $event->getUsers();
                if (count($allUser) - 1 === count($group->getUsers()->toArray()) && !in_array($user, $group->getUsers()->toArray())) {
                    $dto->setName('Alle außer dir');
                }
            }
            if ($group->isInitial()){
                $dto->setName('Alle');
            }
            // TODO get name from new entity
            if (empty($dto->getName())){
                $dto->setName(sprintf('Du + %d weitere', count($group->getUsers()->toArray())-1));
            }

            $dto->setColorClass('event-group-color-'.$index+1);
            $users = array_map(function (User $user) {
                return $user->getFullName();
            }, $group->getUsers()->toArray());
            $dto->setMembers(implode(',',$users));
            $dto->setId($group->getId());

            $dtos[] = $dto;
        }

        return $this->environment->render(
            'event/extension/event.extension_group_fields.html.twig',
            [
                'dtos' => $dtos,
            ]
        );
    }

    public function getUserNameString(PersistentCollection $users): string
    {
        $array = $users->toArray();
        $array = array_map(function (User $user) {
            return $user->getFullName();
        }, $array);
        return implode(',', $array);
    }

    public function getUserIdsString(PersistentCollection $users): string
    {
        $array = $users->toArray();
        $array = array_map(function (User $user) {
            return $user->getId();
        }, $array);
        return implode(',', $array);
    }

    public function renderCalculationUserEntry(UserCollectionDto $dto): string
    {
        $userDataDto = new UserCollectionDto();

        return $this->environment->render(
            'event/extension/event.extension_calculation_entry.html.twig',
            [
                'user' => $userDataDto,
            ]
        );
    }
}
