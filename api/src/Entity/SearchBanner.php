<?php

namespace App\Entity;

use App\Repository\SearchBannerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchBannerRepository::class)]
#[ORM\UniqueConstraint(name: 'BANNER_IDX', columns: ['tag_id', 'feature_id'])]
class SearchBanner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(nullable: false)]
    private int $tag_id;

    #[ORM\Column(nullable: false)]
    private int $feature_id;

    #[ORM\ManyToOne(targetEntity: Banner::class, inversedBy: 'searchBanners')]
    #[ORM\JoinColumn(nullable: false)]
    private Banner $banner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBanner(): ?Banner
    {
        return $this->banner;
    }

    public function setBanner(?Banner $banner): static
    {
        $this->banner = $banner;

        return $this;
    }

    public function getTagId(): ?int
    {
        return $this->tag_id;
    }

    public function setTagId(?int $tag_id): static
    {
        $this->tag_id = $tag_id;

        return $this;
    }

    public function getFeatureId(): ?int
    {
        return $this->feature_id;
    }

    public function setFeatureId(?int $feature_id): static
    {
        $this->feature_id = $feature_id;

        return $this;
    }
}
