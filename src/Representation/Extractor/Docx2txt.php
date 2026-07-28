<?php

namespace MediaText\Representation\Extractor;

use MediaText\Entity\Representation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Stdlib\Cli;

class Docx2txt extends AbstractFileRepresentationExtractor
{
    public function __construct(protected Cli $cli)
    {
    }

    public function getName(): string
    {
        return 'docx2txt';
    }

    public function getLabel(): string
    {
        return 'docx2txt';
    }

    public function isAvailable(): bool
    {
        return $this->cli->getCommandPath('docx2txt') !== false;
    }

    public function canExtractFrom(MediaRepresentation $media): bool
    {
        return $media->hasOriginal() && $media->mediaType() === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }

    protected function getRepresentationsFromFile(string $filepath): array
    {
        $commandPath = $this->cli->getCommandPath('docx2txt');
        if (false === $commandPath) {
            return [];
        }

        $commandArgs = [$commandPath, $filepath, '-'];
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
