<?php

namespace MediaText\Representation\Extractor;

use MediaText\Entity\Representation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Stdlib\Cli;

class Odt2txt extends AbstractFileRepresentationExtractor
{
    public function __construct(protected Cli $cli)
    {
    }

    public function getName(): string
    {
        return 'odt2txt';
    }

    public function getLabel(): string
    {
        return 'odt2txt';
    }

    public function isAvailable(): bool
    {
        return $this->cli->getCommandPath('odt2txt') !== false;
    }

    public function canExtractFrom(MediaRepresentation $media): bool
    {
        return $media->hasOriginal() && $media->mediaType() === 'application/vnd.oasis.opendocument.text';
    }

    protected function getRepresentationsFromFile(string $filepath): array
    {
        $commandPath = $this->cli->getCommandPath('odt2txt');
        if (false === $commandPath) {
            return [];
        }

        $commandArgs = [$commandPath, '--encoding=UTF-8', $filepath];
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
