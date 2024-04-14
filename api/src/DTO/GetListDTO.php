<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\SerializedName;

class GetListDTO
{
    #[SerializedName('tag_id')]
    protected ?string $tag_id = null;

    #[SerializedName('feature_id')]
    protected ?string $feature_id = null;

    #[SerializedName('limit')]
    protected ?string $limit = '100';

    #[SerializedName('offset')]
    protected ?string $offset = '0';

    public function getTagId(): int
    {
        return (int) $this->tag_id;
    }

    public function setTagId(?string $tag_id): void
    {
        $this->tag_id = trim(strip_tags($tag_id));
    }

    public function getFeatureId(): int
    {
        return (int) $this->feature_id;
    }

    public function setFeatureId(?string $feature_id): void
    {
        $this->feature_id = trim(strip_tags($feature_id));
    }

    public function getLimit(): int
    {
        return (int) $this->limit;
    }

    public function setLimit(?string $limit): void
    {
        $this->limit = trim(strip_tags($limit));
    }

    public function getOffset(): int
    {
        return (int) $this->offset;
    }

    public function setOffset(?string $offset): void
    {
        $this->offset = trim(strip_tags($offset));
    }
}
