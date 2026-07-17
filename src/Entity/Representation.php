<?php

namespace MediaText\Entity;

use DateTime;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Media;

/**
 * @Entity
 * @Table(name="mediatext_representation")
 */
class Representation extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Media")
     * @JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $media;

    /**
     * @Column
     */
    protected $title;

    /**
     * @Column
     */
    protected $type;

    /**
     * @Column
     */
    protected $format;

    /**
     * @Column(type="blob")
     */
    protected $compressedContent;

    /**
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * @Column(type="datetime")
     */
    protected $modified;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMedia(Media $media)
    {
        $this->media = $media;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setCompressedContent(string $compressedContent): void
    {
        $this->compressedContent = $compressedContent;
    }

    public function getCompressedContent(): string
    {
        if (is_resource($this->compressedContent)) {
            $this->compressedContent = stream_get_contents($this->compressedContent, null, 0);
        }

        return $this->compressedContent;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setModified(DateTime $modified): void
    {
        $this->modified = $modified;
    }

    public function getModified(): DateTime
    {
        return $this->modified;
    }
}
