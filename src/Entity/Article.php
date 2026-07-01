<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column (type: 'float', nullable: true)]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;


    #[ORM\Column(nullable: true)]
    private ?int $nr = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * @var Collection<int, ActicleScreen>
     */
    #[ORM\OneToMany(targetEntity: ActicleScreen::class, mappedBy: 'article')]
    private Collection $acticleScreens;



    #[ORM\Column(nullable: true)]
    private ?float $smallPrice = null;

    #[ORM\Column(nullable: true)]
    private ?bool $soldOut = null;



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
    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }
    public function getNr(): ?int
    {
        return $this->nr;
    }

    public function setNr(?int $nr): static
    {
        $this->nr = $nr;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

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
            $acticleScreen->setArticle($this);
        }

        return $this;
    }

    public function removeActicleScreen(ActicleScreen $acticleScreen): static
    {
        if ($this->acticleScreens->removeElement($acticleScreen)) {
            // set the owning side to null (unless already changed)
            if ($acticleScreen->getArticle() === $this) {
                $acticleScreen->setArticle(null);
            }
        }

        return $this;
    }

    public function getSmallPrice(): ?float
    {
        return $this->smallPrice;
    }

    public function setSmallPrice(?float $smallPrice): static
    {
        $this->smallPrice = $smallPrice;

        return $this;
    }
    public function toArray()
    {
    //Get ArticleSizes
        return[
            'id'=>$this->getId(),
            'nr'=>$this->getNr(),
            'title'=>$this->getTitle(),
            'price'=>$this->getPrice(),
            'description'=>$this->getDescription(),
            'image'=>$this->getImage(),
            'categoryId'=>$this->getCategory()->getId(),
            'smallPrice'=>$this->getSmallPrice(),
            'soldOut'=>$this->isSoldOut()
        ];
    }

    public function isSoldOut(): ?bool
    {
        return $this->soldOut;
    }

    public function setSoldOut(?bool $soldOut): static
    {
        $this->soldOut = $soldOut;

        return $this;
    }

}
