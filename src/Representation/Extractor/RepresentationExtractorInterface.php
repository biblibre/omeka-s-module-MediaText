<?php

namespace MediaText\Representation\Extractor;

use Omeka\Api\Representation\MediaRepresentation;
use MediaText\Entity\Representation;

interface RepresentationExtractorInterface
{
    public function getName(): string;
    public function getLabel(): string;
    public function isAvailable(): bool;
    public function canExtractFrom(MediaRepresentation $media): bool;

    /**
     * @return Representation[]
     */
    public function getRepresentations(MediaRepresentation $media): array;
}
