<?php

namespace App\DTO;

use App\Exception\BadDataException;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class CreateBannerDTO
{
    #[SerializedName('tag_ids'),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected array $tag_ids;

    #[SerializedName('feature_id'),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected int $feature_id;

    #[SerializedName('content'),
        Assert\NotBlank(),
        Assert\NotNull()]
    protected string $content;

    #[SerializedName('is_active'),
        Assert\NotNull()]
    protected bool $is_active;

    public function getTagIds(): array
    {
        return $this->tag_ids;
    }

    public function setTagIds(array $tag_ids): void
    {
        $this->tag_ids = array_unique($tag_ids);
    }

    public function getFeatureId(): int
    {
        return $this->feature_id;
    }

    public function setFeatureId(int $feature_id): void
    {
        $this->feature_id = $feature_id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        json_decode($content);

        if (0 !== json_last_error()) {
            throw new BadDataException('Содержимое баннера - некорректный json', 400);
        }

        $this->content = $content;
    }

    public function getIsActive(): bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): void
    {
        $this->is_active = $is_active;
    }
}
