<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentTypeRepository")
 * @ORM\Table(name="payment_type")
 * @ORM\HasLifecycleCallbacks()
 */

class PaymentTypeEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="code", type="string", nullable=true)
     */
    protected $code;
    
    /**
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="PaymentTypes")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;
    
    /**
     * @ORM\OneToMany(targetEntity="ClientMeterPaymentEntity", mappedBy="paymentType", cascade={"remove"})
     */
    protected $clientMeterPayments;


    public function __construct($data = null)
    {
        $this->clientMeterPayments = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return PaymentTypeEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBranch(): ?BranchEntity
    {
        return $this->branch;
    }

    public function setBranch(?BranchEntity $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * @return Collection<int, ClientMeterPaymentEntity>
     */
    public function getClientMeterPayments(): Collection
    {
        return $this->clientMeterPayments;
    }

    public function addClientMeterPayment(ClientMeterPaymentEntity $clientMeterPayment): self
    {
        if (!$this->clientMeterPayments->contains($clientMeterPayment)) {
            $this->clientMeterPayments[] = $clientMeterPayment;
            $clientMeterPayment->setPaymentType($this);
        }

        return $this;
    }

    public function removeClientMeterPayment(ClientMeterPaymentEntity $clientMeterPayment): self
    {
        if ($this->clientMeterPayments->removeElement($clientMeterPayment)) {
            // set the owning side to null (unless already changed)
            if ($clientMeterPayment->getPaymentType() === $this) {
                $clientMeterPayment->setPaymentType(null);
            }
        }

        return $this;
    }

    
}
