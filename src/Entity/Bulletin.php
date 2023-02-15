<?php

namespace App\Entity;

use App\Repository\BulletinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BulletinRepository::class)]
class Bulletin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'bulletins')]
    private Collection $tags;

    public function __construct(string $title = null, string $category = null, string $content = null)
    {
        // Cette méthode est automatiquement appelée à la création de notre instance de classe.

        // Si les paramètres de notre méthode de constructeur ont été remplis, ils renseigneront notre instance de classe, sinon on la remplit avec des valeurs par défaut :
        if ($title) {
            $this->title = $title;
        } else $this->title = "Bulletin créé " . uniqid();

        if ($category) {
            $this->category = $category;
        } else $this->category = "Général";

        if ($content) {
            $this->content = $content;
        } else $this->content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

        $this->date = new \DateTime("now"); // Création automatique de l'attribut de date de création
        $this->tags = new ArrayCollection();
    }

    public function clearFields(): self // self signifie qu'elle se retourne elle-même
    {
        // Cette méthode vide les champs de notre objet Bulletin.
        $this->title = null;
        $this->category = null;
        $this->content = null;

        return $this;
    }

    public function getColor(): string
    {
        // Cette méthode rend un code couleur Bootstrap selon la catégorie de l'Entity :
        switch ($this->category) {
            case "general":
                return "info"; // le return met fin à la méthode
                break; // le break ici est juste "esthétique", car il n'est pas lu
            case "divers":
                return "warning";
                break;
            case "urgent":
                return "danger";
                break;
            default:
                return "secondary";
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function displayCategory(): ?string
    {
        // Affichage du nom de catégorie formaté pour la lisibilité par l'utilisateur :
        if ($this->category == "general") {
            return "Général";
        } else
            return ucfirst($this->category);
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addBulletin($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeBulletin($this);
        }

        return $this;
    }
}
