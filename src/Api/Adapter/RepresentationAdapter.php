<?php

namespace MediaText\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class RepresentationAdapter extends AbstractEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'title' => 'title',
        'type' => 'type',
        'format' => 'format',
        'position' => 'position',
        'created' => 'created',
        'modified' => 'modified',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'title' => 'title',
        'type' => 'type',
        'format' => 'format',
        'position' => 'position',
        'source' => 'source',
        'created' => 'created',
        'modified' => 'modified',
    ];

    public function getResourceName()
    {
        return 'mediatext_representations';
    }

    public function getRepresentationClass()
    {
        return 'MediaText\Api\Representation\RepresentationRepresentation';
    }

    public function getEntityClass()
    {
        return 'MediaText\Entity\Representation';
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();

        if ($request->getOperation() === Request::CREATE) {
            if (isset($data['o:media']['o:id'])) {
                $mediaAdapter = $this->getAdapter('media');
                $media = $mediaAdapter->findEntity($data['o:media']['o:id']);
                $entity->setMedia($media);
            }
        }

        if ($this->shouldHydrate($request, 'o:title')) {
            $entity->setTitle($request->getValue('o:title'));
        }

        if ($this->shouldHydrate($request, 'o:type')) {
            $entity->setType($request->getValue('o:type', 'transcription'));
        }

        if ($this->shouldHydrate($request, 'o:format')) {
            $entity->setFormat($request->getValue('o:format', 'plain_text'));
        }

        if ($this->shouldHydrate($request, 'o:is_public')) {
            $entity->setIsPublic((bool) $request->getValue('o:is_public'));
        }

        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $compressionLevel = (int) $settings->get('mediatext_compression_level', '-1');

        if ($request->getOperation() === Request::UPDATE && $this->shouldHydrate($request, 'o:content')) {
            $content = $request->getValue('o:content');

            $entity->setContent($content);
        } elseif ($request->getOperation() === Request::CREATE) {
            $ingesterName = $request->getValue('o:ingester');
            $ingester = $this->getServiceLocator()->get('MediaText\RepresentationIngesterManager')->get($ingesterName);

            $ingester->ingest($entity, $request, $errorStore);
        }

        $content = $entity->getContent();
        if ($content !== null) {
            $formatAdapterManager = $this->getServiceLocator()->get('MediaText\FormatAdapterManager');
            $formatAdapter = $formatAdapterManager->get($entity->getFormat());
            $formatErrorStore = new ErrorStore;
            $formatAdapter->validate($content, $formatErrorStore);
            if ($formatErrorStore->hasErrors()) {
                foreach ($formatErrorStore->getErrors() as $key => $errors) {
                    foreach ($errors as $error) {
                        $errorStore->addError('error', $error);
                    }
                }
                return;
            }

            $entity->setCompressedContent(gzencode($content, $compressionLevel));
            $entity->setContent(null);
        }

        $this->updateTimestamps($request, $entity);
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (null === $entity->getMedia()) {
            $errorStore->addError('o:media_id', 'A representation needs to be associated with a media');
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['media_id'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.media',
                $this->createNamedParameter($qb, $query['media_id']))
            );
        }

        if (isset($query['title'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.title',
                $this->createNamedParameter($qb, $query['title']))
            );
        }

        if (isset($query['type'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.type',
                $this->createNamedParameter($qb, $query['type']))
            );
        }

        if (isset($query['format'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.format',
                $this->createNamedParameter($qb, $query['format']))
            );
        }
    }
}
