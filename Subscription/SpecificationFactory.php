<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Subscription;

use Newscoop\PaywallBundle\Entity\SubscriptionSpecification;
use Newscoop\Entity\Publication;
use Newscoop\Entity\Issue;
use Newscoop\Entity\Section;
use Newscoop\Entity\Article;

/**
 * Specification Factory.
 */
class SpecificationFactory
{
    public static function create($subscription, $object)
    {
        $specification = new SubscriptionSpecification();
        if ($object instanceof Publication) {
            $specification = new SubscriptionSpecification(
                $subscription,
                $object->getId()
            );
        }

        if ($object instanceof Issue) {
            $specification = new SubscriptionSpecification(
                $subscription,
                $object->getPublicationId(),
                $object->getNumber()
            );
        }

        if ($object instanceof Section) {
            $specification = new SubscriptionSpecification(
                $subscription,
                $object->getPublicationId(),
                $object->getIssueId(),
                $object->getNumber()
            );
        }

        if ($object instanceof Article) {
            $specification = new SubscriptionSpecification(
                $subscription,
                $object->getPublicationId(),
                $object->getIssueId(),
                $object->getSectionId(),
                $object->getNumber()
            );
        }

        return $specification;
    }
}
