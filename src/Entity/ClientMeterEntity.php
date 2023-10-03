<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientMeterRepository")
 * @ORM\Table(name="client_meter")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientMeterEntity extends BaseEntity
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
     * @ORM\Column(name="connection_type", type="string")
     */
    protected $connectionType;

    /**
     * @ORM\Column(name="house_no", type="string", nullable=true)
     */
    protected $houseNo;
    
    /**
     * @ORM\Column(name="meter_model", type="string", nullable=true)
     */
    protected $meterModel;

    /**
     * @ORM\Column(name="meter_serial_no", type="string", nullable=true)
     */
    protected $meterSerialNo;

    /**
     * @ORM\Column(name="previous_reading", type="string", nullable=true)
     */
    protected $previousReading;

    /**
     * @ORM\Column(name="present_reading", type="string")
     */
    protected $presentReading;


    /**
     * @ORM\Column(name="old_balance", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $oldBalance;


    /**
     * @ORM\Column(name="remaining_balance", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $remainingBalance;
    
    /**
     * @ORM\Column(name="final_balance", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $finalBalance;

    /**
     * @ORM\ManyToOne(targetEntity="PurokEntity", inversedBy="clientMeters")
     * @ORM\JoinColumn(name="purok_id", referencedColumnName="id", nullable=true)
     */
    protected $purok;

    /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="clientMeters")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

    /**
     * @ORM\OneToMany(targetEntity="ClientMeterReadingEntity", mappedBy="clientMeter", cascade={"remove"})
     */
    protected $clientMeterReadings;

    /**
     * @ORM\OneToMany(targetEntity="ClientMeterPaymentEntity", mappedBy="clientMeter", cascade={"remove"})
     */
    protected $clientMeterPayments;

    /**
     * @ORM\OneToMany(targetEntity="BranchSmsEntity", mappedBy="clientMeter", cascade={"remove"})
     */
    protected $branchSmss;

    public function __construct($data = null)
    {
        $this->clientMeterReadings = new ArrayCollection();
        $this->clientMeterPayments = new ArrayCollection();
        $this->branchSmss = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientMeterEntity
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

    public function getConnectionType(): ?string
    {
        return $this->connectionType;
    }

    public function setConnectionType(string $connectionType): self
    {
        $this->connectionType = $connectionType;

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

    public function getRemainingBalance(): ?string
    {
        return $this->remainingBalance;
    }

    public function setRemainingBalance(?string $remainingBalance): self
    {
        $this->remainingBalance = $remainingBalance;

        return $this;
    }

    public function getPurok(): ?PurokEntity
    {
        return $this->purok;
    }

    public function setPurok(?PurokEntity $purok): self
    {
        $this->purok = $purok;

        return $this;
    }

    public function getClient(): ?ClientEntity
    {
        return $this->client;
    }

    public function setClient(?ClientEntity $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, ClientMeterReadingEntity>
     */
    public function getClientMeterReadings(): Collection
    {
        return $this->clientMeterReadings;
    }

    public function addClientMeterReading(ClientMeterReadingEntity $clientMeterReading): self
    {
        if (!$this->clientMeterReadings->contains($clientMeterReading)) {
            $this->clientMeterReadings[] = $clientMeterReading;
            $clientMeterReading->setClientMeter($this);
        }

        return $this;
    }

    public function removeClientMeterReading(ClientMeterReadingEntity $clientMeterReading): self
    {
        if ($this->clientMeterReadings->removeElement($clientMeterReading)) {
            // set the owning side to null (unless already changed)
            if ($clientMeterReading->getClientMeter() === $this) {
                $clientMeterReading->setClientMeter(null);
            }
        }

        return $this;
    }

    public function getLastReading(): ?string
    {
        return $this->lastReading;
    }

    public function setLastReading(string $lastReading): self
    {
        $this->lastReading = $lastReading;

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
            $clientMeterPayment->setClientMeter($this);
        }

        return $this;
    }

    public function removeClientMeterPayment(ClientMeterPaymentEntity $clientMeterPayment): self
    {
        if ($this->clientMeterPayments->removeElement($clientMeterPayment)) {
            // set the owning side to null (unless already changed)
            if ($clientMeterPayment->getClientMeter() === $this) {
                $clientMeterPayment->setClientMeter(null);
            }
        }

        return $this;
    }

    public function getOldBalance(): ?string
    {
        return $this->oldBalance;
    }

    public function setOldBalance(?string $oldBalance): self
    {
        $this->oldBalance = $oldBalance;

        return $this;
    }

    public function getFinalBalance(): ?string
    {
        return $this->finalBalance;
    }

    public function setFinalBalance(?string $finalBalance): self
    {
        $this->finalBalance = $finalBalance;

        return $this;
    }

    /**
     * @return Collection<int, BranchSmsEntity>
     */
    public function getBranchSmss(): Collection
    {
        return $this->branchSmss;
    }

    public function addBranchSmss(BranchSmsEntity $branchSmss): self
    {
        if (!$this->branchSmss->contains($branchSmss)) {
            $this->branchSmss[] = $branchSmss;
            $branchSmss->setClientMeter($this);
        }

        return $this;
    }

    public function removeBranchSmss(BranchSmsEntity $branchSmss): self
    {
        if ($this->branchSmss->removeElement($branchSmss)) {
            // set the owning side to null (unless already changed)
            if ($branchSmss->getClientMeter() === $this) {
                $branchSmss->setClientMeter(null);
            }
        }

        return $this;
    }

    public function getHouseNo(): ?string
    {
        return $this->houseNo;
    }

    public function setHouseNo(?string $houseNo): self
    {
        $this->houseNo = $houseNo;

        return $this;
    }

    public function getMeterModel(): ?string
    {
        return $this->meterModel;
    }

    public function setMeterModel(?string $meterModel): self
    {
        $this->meterModel = $meterModel;

        return $this;
    }

    public function getMeterSerialNo(): ?string
    {
        return $this->meterSerialNo;
    }

    public function setMeterSerialNo(?string $meterSerialNo): self
    {
        $this->meterSerialNo = $meterSerialNo;

        return $this;
    }

    
}
