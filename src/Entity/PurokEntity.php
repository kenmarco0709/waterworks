<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PurokRepository")
 * @ORM\Table(name="purok")
 * @ORM\HasLifecycleCallbacks()
 */

class PurokEntity extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="puroks")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\OneToMany(targetEntity="ClientMeterEntity", mappedBy="purok", cascade={"remove"})
     */
    protected $clientMeters;


    public function __construct($data = null)
    {
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->clientMeters = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return PurokEntity
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
     * @return Collection<int, ClientMeterEntity>
     */
    public function getClientMeters(): Collection
    {
        return $this->clientMeters;
    }

    public function addClientMeter(ClientMeterEntity $clientMeter): self
    {
        if (!$this->clientMeters->contains($clientMeter)) {
            $this->clientMeters[] = $clientMeter;
            $clientMeter->setPurok($this);
        }

        return $this;
    }

    public function removeClientMeter(ClientMeterEntity $clientMeter): self
    {
        if ($this->clientMeters->removeElement($clientMeter)) {
            // set the owning side to null (unless already changed)
            if ($clientMeter->getPurok() === $this) {
                $clientMeter->setPurok(null);
            }
        }

        return $this;
    }

  

}
