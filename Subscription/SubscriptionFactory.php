<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Subscription;

use Newscoop\PaywallBundle\Entity\Subscription;
use Newscoop\PaywallBundle\Entity\Duration;

/**
 * Subscription Factory.
 */
class SubscriptionFactory implements SubscriptionFactoryInterface
{
    public function createSubscription(array $data = array())
    {
        $subscription = new Subscription(
            $data['name'],
            $data['type'],
            $data['price'],
            $data['currencyCode']
        );

        $specification = SpecificationFactory::create(
            $subscription,
            $data['object']
        );

        $subscription->addSpecification($specification);
        if (array_key_exists('duration', $data) && isset($data['duration'])) {
            $duration = $data['duration'];
            $duration->setSubscription($subscription);
            $subscription->addRange($duration);
        }

        return $subscription;
    }
}
