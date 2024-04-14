<?php

namespace App\DTO;

use App\Exception\BadDataException;
use Symfony\Component\Serializer\Annotation\SerializedName;

class UpdateBannerDTO
{
    #[SerializedName('tag_ids')]
    protected ?array $tag_ids = null;

    #[SerializedName('feature_id')]
    protected ?int $feature_id = null;

    #[SerializedName('content')]
    protected ?string $content = null;

    #[SerializedName('is_active')]
    protected ?bool $is_active = null;

    public function getTagIds(): ?array
    {
        return $this->tag_ids;
    }

    public function setTagIds(?array $tag_ids): void
    {
        $this->tag_ids = $tag_ids ? array_unique($tag_ids) : null;
    }

    public function getFeatureId(): ?int
    {
        return $this->feature_id;
    }

    public function setFeatureId(?int $feature_id): void
    {
        $this->feature_id = $feature_id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        if (!$content) {
            $this->content = null;

            return;
        }

        json_decode($content);

        if (0 !== json_last_error()) {
            throw new BadDataException('Содержимое баннера - некорректный json', 400);
        }

        $this->content = $content;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(?bool $is_active): void
    {
        $this->is_active = $is_active;
    }
}
