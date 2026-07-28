<?php

namespace MediaText\Listener;

use Doctrine\ORM\EntityManager;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;

class AdvancedSearchSharedListenerAggregate
{
    public function attachShared(SharedEventManagerInterface $sharedEventManager)
    {
        $controllers = [
            'Omeka\Controller\Admin\Item',
            'Omeka\Controller\Admin\Media',
        ];
        foreach ($controllers as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.advanced_search',
                [$this, 'onViewAdvancedSearch']
            );

            $sharedEventManager->attach(
                $controller,
                'view.search.filters',
                [$this, 'onViewSearchFilters']
            );
        }

        $apiAdapters = [
            'Omeka\Api\Adapter\ItemAdapter',
            'Omeka\Api\Adapter\MediaAdapter',
        ];
        foreach ($apiAdapters as $apiAdapter) {
            $sharedEventManager->attach(
                $apiAdapter,
                'api.search.query',
                [$this, 'onApiSearchQuery']
            );
        }
    }

    public function onViewAdvancedSearch(Event $event)
    {
        $partials = $event->getParam('partials');

        $partials[] = 'media-text/common/advanced-search/has-textual-representation';

        $event->setParam('partials', $partials);
    }

    public function onApiSearchQuery(Event $event)
    {
        $adapter = $event->getTarget();
        $status = $adapter->getServiceLocator()->get('Omeka\Status');
        if (!$status->isAdminRequest()) {
            return;
        }

        $qb = $event->getParam('queryBuilder');
        $request = $event->getParam('request');

        $hasTextualRepresentations = $request->getValue('mediatext_has_textual_representations', '');
        if ($hasTextualRepresentations !== '') {
            $subQb = $adapter->getEntityManager()->createQueryBuilder();
            if ($adapter instanceof \Omeka\Api\Adapter\MediaAdapter) {
                $subQb->select('r')
                    ->from('MediaText\Entity\Representation', 'r')
                    ->andWhere('r.media = omeka_root');
            } elseif ($adapter instanceof \Omeka\Api\Adapter\ItemAdapter) {
                $subQb->select('r')
                    ->from('MediaText\Entity\Representation', 'r')
                    ->join('r.media', 'm')
                    ->andWhere('m.item = omeka_root');
            } else {
                // Other adapters not supported
                return;
            }

            $expr = $qb->expr()->exists($subQb->getDQL());
            if (!$hasTextualRepresentations) {
                $expr = $qb->expr()->not($expr);
            }
            $qb->andWhere($expr);
        }
    }

    public function onViewSearchFilters(Event $event)
    {
        $view = $event->getTarget();
        $query = $event->getParam('query');
        $filters = $event->getParam('filters');

        $hasTextualRepresentations = $query['mediatext_has_textual_representations'] ?? '';
        if ($hasTextualRepresentations !== '') {
            $filters[$view->translate('Has textual representation')][] = $hasTextualRepresentations ? $view->translate('Yes') : $view->translate('No');
        }

        $event->setParam('filters', $filters);
    }

}
