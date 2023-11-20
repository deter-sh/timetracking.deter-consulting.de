<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: TimeTrack::class, orphanRemoval: true)]
    private Collection $timeTracks;

    public function __construct()
    {
        $this->timeTracks = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getCustomer()->getName() . ' - ' . $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, TimeTrack>
     */
    public function getTimeTracks(): Collection
    {
        return $this->timeTracks;
    }

    public function addTimeTrack(TimeTrack $timeTrack): static
    {
        if (!$this->timeTracks->contains($timeTrack)) {
            $this->timeTracks->add($timeTrack);
            $timeTrack->setProject($this);
        }

        return $this;
    }

    public function removeTimeTrack(TimeTrack $timeTrack): static
    {
        if ($this->timeTracks->removeElement($timeTrack)) {
            // set the owning side to null (unless already changed)
            if ($timeTrack->getProject() === $this) {
                $timeTrack->setProject(null);
            }
        }

        return $this;
    }
}
