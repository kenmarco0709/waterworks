<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientMeterPaymentRepository")
 * @ORM\Table(name="client_meter_payment")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientMeterPaymentEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="transaction_no", type="string")
     */
    protected $transactionNo;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2)
     */
    protected $amount;

    /**
     * @ORM\Column(name="amount_tendered", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountTendered;

    /**
     * @ORM\Column(name="amount_change", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountChange;

    /**
     * @ORM\Column(name="ref_no", type="string", nullable=true)
     */
    protected $refNo;

    /**
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    protected $paymentDate;

    /**
     * @ORM\ManyToOne(targetEntity="ClientMeterEntity", inversedBy="clientMeterPayments")
     * @ORM\JoinColumn(name="client_meter_id", referencedColumnName="id", nullable=true)
     */
    protected $clientMeter;
    
    /**
     * @ORM\ManyToOne(targetEntity="PaymentTypeEntity", inversedBy="clientMeterPayments")
     * @ORM\JoinColumn(name="payment_type_id", referencedColumnName="id", nullable=true)
     */
    protected $paymentType;

    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientMeterPaymentEntity
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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRefNo(): ?string
    {
        return $this->refNo;
    }

    public function setRefNo(?string $refNo): self
    {
        $this->refNo = $refNo;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getClientMeter(): ?ClientMeterEntity
    {
        return $this->clientMeter;
    }

    public function setClientMeter(?ClientMeterEntity $clientMeter): self
    {
        $this->clientMeter = $clientMeter;

        return $this;
    }

    public function getPaymentType(): ?PaymentTypeEntity
    {
        return $this->paymentType;
    }

    public function setPaymentType(?PaymentTypeEntity $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getTransactionNo(): ?string
    {
        return $this->transactionNo;
    }

    public function setTransactionNo(string $transactionNo): self
    {
        $this->transactionNo = $transactionNo;

        return $this;
    }

    public function getAmountTendered(): ?string
    {
        return $this->amountTendered;
    }

    public function setAmountTendered(?string $amountTendered): self
    {
        $this->amountTendered = $amountTendered;

        return $this;
    }

    public function getAmountChange(): ?string
    {
        return $this->amountChange;
    }

    public function setAmountChange(?string $amountChange): self
    {
        $this->amountChange = $amountChange;

        return $this;
    }

 
    
}
