<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class GetBannerDTO
{
    #[SerializedName('tag_id'),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected string $tag_id;

    #[SerializedName('feature_id'),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected string $feature_id;

    #[SerializedName('use_last_revision')]
    protected string $use_last_revision = '';

    public function getTagId(): int
    {
        return (int) $this->tag_id;
    }

    public function setTagId(int $tag_id): void
    {
        $this->tag_id = trim(strip_tags($tag_id));
    }

    public function getFeatureId(): int
    {
        return (int) $this->feature_id;
    }

    public function setFeatureId(int $feature_id): void
    {
        $this->feature_id = trim(strip_tags($feature_id));
    }

    public function getUseLastRevision(): bool
    {
        return (bool) $this->use_last_revision;
    }

    public function setUseLastRevision(string $use_last_revision): void
    {
        $param = strtolower(trim(strip_tags($use_last_revision)));

        $this->use_last_revision = 'true' === $param ? true : false;
    }
}
