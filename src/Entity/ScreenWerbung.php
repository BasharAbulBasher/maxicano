<?php

namespace App\Entity;

use App\Repository\ScreenWerbungRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreenWerbungRepository::class)]
class ScreenWerbung
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'screenWerbungs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Screen $screen = null;

    #[ORM\ManyToOne(inversedBy: 'screenWerbungs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Werbung $werbung = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScreen(): ?Screen
    {
        return $this->screen;
    }

    public function setScreen(?Screen $screen): static
    {
        $this->screen = $screen;

        return $this;
    }

    public function getWerbung(): ?Werbung
    {
        return $this->werbung;
    }

    public function setWerbung(?Werbung $werbung): static
    {
        $this->werbung = $werbung;

        return $this;
    }

    public function toArray()
    {
        return[
            'werbung'=>$this->getWerbung()->toArray(),
            'screen'=>$this->getScreen()->toArray()
        ];
    }
}
