<?php

namespace MediaText\Representation\Extractor;

use MediaText\Entity\Representation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Stdlib\Cli;

class Catdoc extends AbstractFileRepresentationExtractor
{
    public function __construct(protected Cli $cli)
    {
    }

    public function getName(): string
    {
        return 'catdoc';
    }

    public function getLabel(): string
    {
        return 'catdoc';
    }

    public function isAvailable(): bool
    {
        return $this->cli->getCommandPath('catdoc') !== false;
    }

    public function canExtractFrom(MediaRepresentation $media): bool
    {
        return $media->hasOriginal() && $media->mediaType() === 'application/msword';
    }

    protected function getRepresentationsFromFile(string $filepath): array
    {
        $commandPath = $this->cli->getCommandPath('catdoc');
        if (false === $commandPath) {
            return [];
        }

        $commandArgs = [$commandPath, '-d utf-8', $filepath];
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
