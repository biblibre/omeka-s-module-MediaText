<?php

namespace MediaText\Listener;

use Doctrine\ORM\EntityManager;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;

class SolrSharedListenerAggregate
{
    public function __construct(
        protected EntityManager $em,
        protected ApiAdapterManager $apiAdapterManager
    )
    {}

    public function attachShared(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Solr\ValueExtractor\ItemValueExtractor',
            'solr.value_extractor.fields',
            [$this, 'onValueExtractorFields']
        );
        $sharedEventManager->attach(
            'Solr\ValueExtractor\ItemValueExtractor',
            'solr.value_extractor.extract_value',
            [$this, 'onValueExtractorExtractValue']
        );
    }

    public function onValueExtractorFields(Event $event)
    {
        $fields = $event->getParam('fields');

        $fields['media']['children']['mediatext_text_content'] = [
            'label' => 'Media text representation content (plain text)', // @translate
        ];

        $event->setParam('fields', $fields);
    }

    public function onValueExtractorExtractValue(Event $event)
    {
        $item = $event->getTarget();
        $field = $event->getParam('field');

        if ($field === 'media/mediatext_text_content') {
            $mediatextRepresentationAdapter = $this->apiAdapterManager->get('mediatext_representations');

            $query = $this->em->createQuery(<<<'SQL'
                SELECT r FROM MediaText\Entity\Representation r JOIN r.media m
                WHERE m.item = :item_id
            SQL);
            $query->setParameter('item_id', $item->id());
            $representations = $query->getResult();

            $texts = [];
            foreach ($representations as $representation) {
                $representationRepresentation = $mediatextRepresentationAdapter->getRepresentation($representation);
                $texts[] = $representationRepresentation->contentAsPlainText();
                $this->em->detach($representation);
            }

            if (!empty($texts)) {
                $event->setParam('value', implode("\n", $texts));
            }
        }
    }

}
