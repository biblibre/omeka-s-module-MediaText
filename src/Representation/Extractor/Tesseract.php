<?php

namespace MediaText\Representation\Extractor;

use MediaText\Entity\Representation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Stdlib\Cli;

class Tesseract extends AbstractFileRepresentationExtractor
{
    const MEDIA_TYPES = [
        'image/png',
    ];

    public function __construct(protected Cli $cli)
    {
    }

    public function getName(): string
    {
        return 'tesseract';
    }

    public function getLabel(): string
    {
        return 'tesseract';
    }

    public function isAvailable(): bool
    {
        return $this->cli->getCommandPath('tesseract') !== false;
    }

    public function canExtractFrom(MediaRepresentation $media): bool
    {
        return $media->hasOriginal() && in_array($media->mediaType(), self::MEDIA_TYPES);
    }

    protected function getRepresentationsFromFile(string $filepath): array
    {
        $commandPath = $this->cli->getCommandPath('tesseract');
        if (false === $commandPath) {
            return [];
        }

        $commandArgs = [$commandPath, $filepath, '-', 'quiet'];
        $command = implode(' ', array_map('escapeshellarg', $commandArgs));
        $content = $this->cli->execute($command);
        if ($content === false) {
            return [];
        }

        $representation = new Representation;
        $representation->setType('transcription');
        $representation->setFormat('plain_text');
        $representation->setContent($content);

        return [$representation];
    }
}
