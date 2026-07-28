<?php
namespace MediaText\Db\Filter;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Filter representation entities by visibility.
 *
 * Checks to see if the current user has permission to view representations.
 */
class RepresentationVisibilityFilter extends SQLFilter
{
    protected ServiceLocatorInterface $serviceLocator;

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ('MediaText\Entity\Representation' === $targetEntity->getName()) {
            $acl = $this->serviceLocator->get('Omeka\Acl');
            if ($acl->userIsAllowed('Omeka\Entity\Resource', 'view-all')) {
                return '';
            }
            // Users can view public representations they do not own.
            $constraint = "$targetTableAlias.is_public = 1";
            $identity = $this->serviceLocator->get('Omeka\AuthenticationService')->getIdentity();
            if ($identity) {
                // Users can view all representations they own.
                $constraint = sprintf(
                    '%1$s OR %2$s.media_id = (SELECT r.id FROM resource r WHERE r.owner_id = %3$s AND r.id = %2$s.media_id)',
                    $constraint,
                    $targetTableAlias,
                    $this->getConnection()->quote($identity->getId(), Type::INTEGER)
                );
            }
            return $constraint;
        }
        return '';
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}
