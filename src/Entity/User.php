<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    private $lastName;

    #[ORM\OneToMany(targetEntity: PaymentOption::class, mappedBy: 'owner', cascade: ['persist'])]
    private $PaymentOptions;

    #[ORM\OneToMany(targetEntity: Debt::class, mappedBy: 'owner', cascade: ['persist'])]
    private $debts;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'owner', cascade: ['persist'])]
    private $loans;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: PaymentOption::class, cascade: ['persist'])]
    private Collection $paymentOptions;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: GroupEvent::class)]
    private Collection $groupEvents;

    #[ORM\OneToMany(mappedBy: 'loaner', targetEntity: GroupEventPayment::class)]
    private Collection $groupEventPayments;

    #[ORM\ManyToMany(targetEntity: GroupEventUserCollection::class, mappedBy: 'users')]
    private Collection $groupEventUserCollections;

    #[ORM\OneToMany(mappedBy: 'debtor', targetEntity: GroupEventResult::class)]
    private Collection $groupEventResults;

    public function __construct()
    {
        $this->paymentOptions = new ArrayCollection();
        $this->groupEventPayments = new ArrayCollection();
        $this->groupEvents = new ArrayCollection();
        $this->PaymentOptions = new ArrayCollection();
        $this->debts = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->groupEventUserCollections = new ArrayCollection();
        $this->groupEventResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection|PaymentOption[]
     */
    public function getPaymentOptions(): Collection
    {
        return $this->paymentOptions;
    }

    public function addPaymentOption(PaymentOption $paymentOption): self
    {
        if (!$this->paymentOptions->contains($paymentOption)) {
            $this->paymentOptions[] = $paymentOption;
            $paymentOption->setOwner($this);
        }

        return $this;
    }

    public function removePaymentOption(PaymentOption $paymentOption): self
    {
        if ($this->paymentOptions->removeElement($paymentOption)) {
            // set the owning side to null (unless already changed)
            if ($paymentOption->getOwner() === $this) {
                $paymentOption->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Debt[]
     */
    public function getDebts(): Collection
    {
        return $this->debts;
    }

    public function addDebt(Debt $debt): self
    {
        if (!$this->debts->contains($debt)) {
            $this->debts[] = $debt;
            $debt->setOwner($this);
        }

        return $this;
    }

    public function removeDebt(Debt $debt): self
    {
        if ($this->debts->removeElement($debt)) {
            // set the owning side to null (unless already changed)
            if ($debt->getOwner() === $this) {
                $debt->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Loan[]
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): self
    {
        if (!$this->loans->contains($loan)) {
            $this->loans[] = $loan;
            $loan->setOwner($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): self
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getOwner() === $this) {
                $loan->setOwner(null);
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return Collection<int, GroupEventUserCollection>
     */
    public function getGroupEventUserCollections(): Collection
    {
        return $this->groupEventUserCollections;
    }

    public function addGroupEventUserCollection(GroupEventUserCollection $groupEventUserCollection): static
    {
        if (!$this->groupEventUserCollections->contains($groupEventUserCollection)) {
            $this->groupEventUserCollections->add($groupEventUserCollection);
            $groupEventUserCollection->addUser($this);
        }

        return $this;
    }

    public function removeGroupEventUserCollection(GroupEventUserCollection $groupEventUserCollection): static
    {
        if ($this->groupEventUserCollections->removeElement($groupEventUserCollection)) {
            $groupEventUserCollection->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, GroupEventPayment>
     */
    public function getGroupEventPayments(): Collection
    {
        return $this->groupEventPayments;
    }

    public function addGroupEventPayment(GroupEventPayment $groupEventPayment): static
    {
        if (!$this->groupEventPayments->contains($groupEventPayment)) {
            $this->groupEventPayments->add($groupEventPayment);
            $groupEventPayment->setLoaner($this);
        }

        return $this;
    }

    public function removeGroupEventPayment(GroupEventPayment $groupEventPayment): static
    {
        if ($this->groupEventPayments->removeElement($groupEventPayment)) {
            // set the owning side to null (unless already changed)
            if ($groupEventPayment->getLoaner() === $this) {
                $groupEventPayment->setLoaner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GroupEvent>
     */
    public function getGroupEvents(): Collection
    {
        return $this->groupEvents;
    }

    public function addGroupEvent(GroupEvent $groupEvent): static
    {
        if (!$this->groupEvents->contains($groupEvent)) {
            $this->groupEvents->add($groupEvent);
            $groupEvent->setCreator($this);
        }

        return $this;
    }

    public function removeGroupEvent(GroupEvent $groupEvent): static
    {
        if ($this->groupEvents->removeElement($groupEvent)) {
            // set the owning side to null (unless already changed)
            if ($groupEvent->getCreator() === $this) {
                $groupEvent->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GroupEventResult>
     */
    public function getGroupEventResults(): Collection
    {
        return $this->groupEventResults;
    }

    public function addGroupEventResult(GroupEventResult $groupEventResult): static
    {
        if (!$this->groupEventResults->contains($groupEventResult)) {
            $this->groupEventResults->add($groupEventResult);
            $groupEventResult->setLoaner($this);
        }

        return $this;
    }

    public function removeGroupEventResult(GroupEventResult $groupEventResult): static
    {
        if ($this->groupEventResults->removeElement($groupEventResult)) {
            // set the owning side to null (unless already changed)
            if ($groupEventResult->getLoaner() === $this) {
                $groupEventResult->setLoaner(null);
            }
        }

        return $this;
    }
}
