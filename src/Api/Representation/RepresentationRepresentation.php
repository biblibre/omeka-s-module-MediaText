<?php

namespace MediaText\Api\Representation;

use MediaText\Format\Adapter\FormatAdapterInterface;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\MediaRepresentation;

class RepresentationRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-mediatext:Representation';
    }

    public function getJsonLd()
    {
        $entity = $this->resource;

        return [
            'o:media' => $this->media()->getReference(),
            'o:type' => $this->type(),
            'o:format' => $this->format(),
            'o:content' => $this->content(),
            'o:created' => $this->getDateTime($entity->getCreated()),
            'o:modified' => $entity->getModified() ? $this->getDateTime($entity->getModified()) : null,
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');

        return $url(
            'admin/media-text/representation-id',
            [
                'action' => $action,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function media(): MediaRepresentation
    {
        return $this->getAdapter('media')->getRepresentation($this->resource->getMedia());
    }

    public function title(): string
    {
        return $this->resource->getTitle();
    }

    public function type(): string
    {
        return $this->resource->getType();
    }

    public function format(): string
    {
        return $this->resource->getFormat();
    }

    public function position(): int
    {
        return $this->resource->getPosition();
    }

    public function isPublic(): bool
    {
        return $this->resource->getIsPublic();
    }

    public function isExtracted(): bool
    {
        return $this->resource->getIsExtracted();
    }

    public function source(): ?string
    {
        return $this->resource->getSource();
    }

    public function content(): string
    {
        $compressedContent = $this->resource->getCompressedContent();

        return zlib_decode($compressedContent);
    }

    public function contentAsPlainText(): string
    {
        return $this->formatAdapter()->getPlainText($this->content());
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function modified()
    {
        return $this->resource->getModified();
    }

    public function typeLabel(): string
    {
        return match ($this->type()) {
            'transcription' => 'Transcription', // @translate
            'transliteration' => 'Transliteration', // @translate
            'translation' => 'Translation', // @translate
            'other' => 'Other', // @translate
            default => '[Unknown]', // @translate
        };
    }

    public function formatLabel(): string
    {
        return $this->formatAdapter()->getLabel();
    }

    public function formatAdapter(): FormatAdapterInterface
    {
        $formatAdapterManager = $this->getServiceLocator()->get('MediaText\FormatAdapterManager');

        return $formatAdapterManager->get($this->format());
    }
}
