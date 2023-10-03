<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BranchSmsRepository")
 * @ORM\Table(name="branch_sms")
 * @ORM\HasLifecycleCallbacks()
 */

class BranchSmsEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @ORM\Column(name="status", type="string")
     */
    protected $status;

    /**
     * @ORM\Column(name="sent_ctr", type="string", nullable=true)
     */
    protected $sentCtr;

    /**
     * @ORM\Column(name="send_at", type="datetime", nullable=true)
     */
    protected $sendAt;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="branchSmss")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="SmsEntity", inversedBy="branchSmss")
     * @ORM\JoinColumn(name="sms_id", referencedColumnName="id", nullable=true)
     */
    protected $sms;

    /**
     * @ORM\ManyToOne(targetEntity="ClientMeterEntity", inversedBy="branchSmss")
     * @ORM\JoinColumn(name="client_meter_id", referencedColumnName="id", nullable=true)
     */
    protected $clientMeter;



    public function __construct($data = null)
    {

    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					BranchSms Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return BranchSmsEntity
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function getSms(): ?SmsEntity
    {
        return $this->sms;
    }

    public function setSms(?SmsEntity $sms): self
    {
        $this->sms = $sms;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSendAt(): ?\DateTimeInterface
    {
        return $this->sendAt;
    }

    public function setSendAt(\DateTimeInterface $sendAt): self
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    public function getSentCtr(): ?string
    {
        return $this->sentCtr;
    }

    public function setSentCtr(?string $sentCtr): self
    {
        $this->sentCtr = $sentCtr;

        return $this;
    }

   
}
