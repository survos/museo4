<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Exhibit", mappedBy="room", orphanRemoval=true)
     */
    private $exhibits;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Museum", inversedBy="rooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $museum;

    /**
     * @ORM\Column(type="string", length=48)
     */
    private $name;

    public function __construct()
    {
        $this->exhibits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Exhibit[]
     */
    public function getExhibits(): Collection
    {
        return $this->exhibits;
    }

    public function addExhibit(Exhibit $exhibit): self
    {
        if (!$this->exhibits->contains($exhibit)) {
            $this->exhibits[] = $exhibit;
            $exhibit->setRoom($this);
        }

        return $this;
    }

    public function removeExhibit(Exhibit $exhibit): self
    {
        if ($this->exhibits->contains($exhibit)) {
            $this->exhibits->removeElement($exhibit);
            // set the owning side to null (unless already changed)
            if ($exhibit->getRoom() === $this) {
                $exhibit->setRoom(null);
            }
        }

        return $this;
    }

    public function getMuseum(): ?Museum
    {
        return $this->museum;
    }

    public function setMuseum(?Museum $museum): self
    {
        $this->museum = $museum;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
