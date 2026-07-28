<?php

namespace MediaText\Representation\Extractor;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Stdlib\Cli;
use MediaText\Entity\Representation;

class Pdftotext extends AbstractFileRepresentationExtractor
{
    public function __construct(protected Cli $cli)
    {
    }

    public function getName(): string
    {
        return 'pdftotext';
    }

    public function getLabel(): string
    {
        return 'pdftotext';
    }

    public function isAvailable(): bool
    {
        return $this->cli->getCommandPath('pdftotext') !== false;
    }

    public function canExtractFrom(MediaRepresentation $media): bool
    {
        return $media->hasOriginal() && $media->mediaType() === 'application/pdf';
    }

    protected function getRepresentationsFromFile(string $filepath): array
    {
        $commandPath = $this->cli->getCommandPath('pdftotext');
        if (false === $commandPath) {
            return [];
        }

        $commandArgs = [$commandPath, '-enc', 'UTF-8', $filepath, '-'];
        $command = implode(' ', array_map('escapeshellarg', $commandArgs));
        $content = $this->cli->execute($command);

        $representation = new Representation;
        $representation->setType('transcription');
        $representation->setFormat('plain_text');
        $representation->setContent($content);

        return [$representation];
    }
}
