<?php

namespace MediaText\Representation\Extractor;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\File\Downloader;
use Omeka\File\Store\StoreInterface;
use Omeka\File\Store\Local;

abstract class AbstractFileRepresentationExtractor implements RepresentationExtractorInterface
{
    protected StoreInterface $fileStore;
    protected Downloader $downloader;

    public function getRepresentations(MediaRepresentation $media): array
    {
        if ($this->fileStore instanceof Local) {
            $storagePath = sprintf('original/%s', $media->filename());
            $filepath = $this->fileStore->getLocalPath($storagePath);
        } else {
            $tempFile = $this->downloader->download($media->originalUrl());
            $filepath = $tempFile->getTempPath();
        }

        $representations = $this->getRepresentationsFromFile($filepath);

        if (isset($tempFile)) {
            $tempFile->delete();
        }

        if ($source = $media->source()) {
            foreach ($representations as $representation) {
                if ($representation->getSource() === null || $representation->getSource() === '') {
                    $representation->setSource($source);
                }
            }
        }

        return $representations;
    }

    abstract protected function getRepresentationsFromFile(string $filepath): array;

    public function setFileStore(StoreInterface $fileStore)
    {
        $this->fileStore = $fileStore;
    }

    public function setDownloader(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }
}
