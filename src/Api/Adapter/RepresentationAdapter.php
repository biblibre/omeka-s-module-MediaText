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
        'created' => 'created',
        'modified' => 'modified',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'title' => 'title',
        'type' => 'type',
        'format' => 'format',
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
        if ($this->shouldHydrate($request, 'o:media_id')) {
            $mediaAdapter = $this->getAdapter('media');
            $entity->setMedia($mediaAdapter->findEntity($request->getValue('o:media_id')));
        }

        if ($this->shouldHydrate($request, 'o:title')) {
            $entity->setTitle($request->getValue('o:title'));
        }

        if ($this->shouldHydrate($request, 'o:type')) {
            $entity->setType($request->getValue('o:type'));
        }

        if ($this->shouldHydrate($request, 'o:format')) {
            $entity->setFormat($request->getValue('o:format'));
        }

        if ($this->shouldHydrate($request, 'o:content')) {
            $content = $request->getValue('o:content');

            $formatAdapterManager = $this->getServiceLocator()->get('MediaText\FormatAdapterManager');
            $formatAdapter = $formatAdapterManager->get($entity->getFormat());
            $formatErrorStore = new ErrorStore;
            $formatAdapter->validate($content, $formatErrorStore);
            if ($formatErrorStore->hasErrors()) {
                foreach ($formatErrorStore->getErrors() as $key => $errors) {
                    foreach ($errors as $error) {
                        $errorStore->addError('o:content', $error);
                    }
                }
                return;
            }

            $settings = $this->getServiceLocator()->get('Omeka\Settings');
            $compressionLevel = (int) $settings->get('mediatext_compression_level', '-1');
            $compressedContent = gzencode($content, $compressionLevel);
            $entity->setCompressedContent($compressedContent);
        }

        $this->updateTimestamps($request, $entity);
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (null === $entity->getMedia()) {
            $errorStore->addError('o:media_id', 'A representation needs to be associated with a media');
        }

        $compressedContent = $entity->getCompressedContent();
        if ($compressedContent === null || $compressedContent === '') {
            $errorStore->addError('o:content', 'A representation cannot be empty');
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
