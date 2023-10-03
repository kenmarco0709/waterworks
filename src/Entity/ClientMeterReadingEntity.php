<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientMeterReadingRepository")
 * @ORM\Table(name="client_meter_reading")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientMeterReadingEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="status", type="string")
     */
    protected $status;
    
    /**
     * @ORM\Column(name="previous_reading", type="string")
     */
    protected $previousReading;

    /**
     * @ORM\Column(name="present_reading", type="string")
     */
    protected $presentReading;

    /**
     * @ORM\Column(name="consume", nullable=true)
     */
    protected $consume;

    /**
     * @ORM\Column(name="amount_per_cubic", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountPerCubic;

    /**
     * @ORM\Column(name="billed_amount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $billedAmount;

    /**
     * @ORM\Column(name="reading_date", type="datetime", nullable=true)
     */
    protected $readingDate;

    /**
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     */
    protected $dueDate;

    /**
     * @ORM\ManyToOne(targetEntity="ClientMeterEntity", inversedBy="clientMeterReadings")
     * @ORM\JoinColumn(name="client_meter_id", referencedColumnName="id", nullable=true)
     */
    protected $clientMeter;

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
     * @return ClientMeterReadingEntity
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPreviousReading(): ?string
    {
        return $this->previousReading;
    }

    public function setPreviousReading(string $previousReading): self
    {
        $this->previousReading = $previousReading;

        return $this;
    }

    public function getPresentReading(): ?string
    {
        return $this->presentReading;
    }

    public function setPresentReading(string $presentReading): self
    {
        $this->presentReading = $presentReading;

        return $this;
    }

    public function getConsume(): ?string
    {
        return $this->consume;
    }

    public function setConsume(string $consume): self
    {
        $this->consume = $consume;

        return $this;
    }

    public function getAmountPerCubic(): ?string
    {
        return $this->amountPerCubic;
    }

    public function setAmountPerCubic(?string $amountPerCubic): self
    {
        $this->amountPerCubic = $amountPerCubic;

        return $this;
    }

    public function getBilledAmount(): ?string
    {
        return $this->billedAmount;
    }

    public function setBilledAmount(?string $billedAmount): self
    {
        $this->billedAmount = $billedAmount;

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

    public function getReadingDate(): ?\DateTimeInterface
    {
        return $this->readingDate;
    }

    public function setReadingDate(?\DateTimeInterface $readingDate): self
    {
        $this->readingDate = $readingDate;

        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

   
    
}
