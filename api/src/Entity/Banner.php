<?php

namespace App\Entity;

use App\Repository\BannerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BannerRepository::class)]
#[ORM\Table(name: '`banners`')]
class Banner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private mixed $content;

    #[ORM\Column]
    private bool $is_active;

    #[ORM\OneToMany(mappedBy: 'banner', targetEntity: SearchBanner::class)]
    private Collection $searchBanners;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct()
    {
        $this->searchBanners = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function setContent(mixed $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * @return Collection<int, SearchBanner>
     */
    public function getSearchBanners(): Collection
    {
        return $this->searchBanners;
    }

    public function addSearchBanner(SearchBanner $searchBanner): static
    {
        if (!$this->searchBanners->contains($searchBanner)) {
            $this->searchBanners->add($searchBanner);
            $searchBanner->setBanner($this);
        }

        return $this;
    }

    public function removeSearchBanner(SearchBanner $searchBanner): static
    {
        if ($this->searchBanners->removeElement($searchBanner)) {
            // set the owning side to null (unless already changed)
            if ($searchBanner->getBanner() === $this) {
                $searchBanner->setBanner(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
