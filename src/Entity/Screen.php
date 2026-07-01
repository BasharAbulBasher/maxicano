<?php

namespace App\Entity;

use App\Repository\ScreenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreenRepository::class)]
class Screen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var Collection<int, ActicleScreen>
     */
    #[ORM\OneToMany(targetEntity: ActicleScreen::class, mappedBy: 'screen')]
    private Collection $acticleScreens;

    /**
     * @var Collection<int, ScreenWerbung>
     */
    #[ORM\OneToMany(targetEntity: ScreenWerbung::class, mappedBy: 'screen', orphanRemoval: true)]
    private Collection $screenWerbungs;

    public function __construct()
    {
        $this->acticleScreens = new ArrayCollection();
        $this->screenWerbungs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, ActicleScreen>
     */
    public function getActicleScreens(): Collection
    {
        return $this->acticleScreens;
    }

    public function addActicleScreen(ActicleScreen $acticleScreen): static
    {
        if (!$this->acticleScreens->contains($acticleScreen)) {
            $this->acticleScreens->add($acticleScreen);
            $acticleScreen->setScreen($this);
        }

        return $this;
    }

    public function removeActicleScreen(ActicleScreen $acticleScreen): static
    {
        if ($this->acticleScreens->removeElement($acticleScreen)) {
            // set the owning side to null (unless already changed)
            if ($acticleScreen->getScreen() === $this) {
                $acticleScreen->setScreen(null);
            }
        }

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
            $screenWerbung->setScreen($this);
        }

        return $this;
    }

    public function removeScreenWerbung(ScreenWerbung $screenWerbung): static
    {
        if ($this->screenWerbungs->removeElement($screenWerbung)) {
            // set the owning side to null (unless already changed)
            if ($screenWerbung->getScreen() === $this) {
                $screenWerbung->setScreen(null);
            }
        }

        return $this;
    }
    public function toArray()
    {
        return[
            "id"=>$this->getId(),
            'title'=>$this->getTitle(),
        ];
    }
}
