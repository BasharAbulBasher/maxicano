<?php

namespace App\Entity;

use App\Repository\WerbungRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WerbungRepository::class)]
class Werbung
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null;

    /**
     * @var Collection<int, ScreenWerbung>
     */
    #[ORM\OneToMany(targetEntity: ScreenWerbung::class, mappedBy: 'werbung', orphanRemoval: true)]
    private Collection $screenWerbungs;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    public function __construct()
    {
        $this->screenWerbungs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNr(): ?string
    {
        return $this->nr;
    }

    public function setNr(string $nr): static
    {
        $this->nr = $nr;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): static
    {
        $this->video = $video;

        return $this;
    }

    /**
     * @return Collection<int, ScreenWerbung>
     */
    public function getScreenWerbungs(): Collection
    {
        return $this->screenWerbungs;
    }

    public function addScreenWerbung(ScreenWerbung $screenWerbung): static
    {
        if (!$this->screenWerbungs->contains($screenWerbung)) {
            $this->screenWerbungs->add($screenWerbung);
            $screenWerbung->setWerbung($this);
        }

        return $this;
    }

    public function removeScreenWerbung(ScreenWerbung $screenWerbung): static
    {
        if ($this->screenWerbungs->removeElement($screenWerbung)) {
            // set the owning side to null (unless already changed)
            if ($screenWerbung->getWerbung() === $this) {
                $screenWerbung->setWerbung(null);
            }
        }

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function toArray()
    {
        return [
            'id'=>$this->getId(),
            'nr'=>$this->getNr(),
            'title'=>$this->getTitle(),
            'description'=>$this->getDescription(),
            'price'=>$this->getPrice(),
            'image'=>$this->getImage(),
            'video'=>$this->getVideo(),
            'length'=>$this->getLength(),
        ];
    }
}
