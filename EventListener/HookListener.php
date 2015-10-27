<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginHooksEvent;
use Newscoop\PaywallBundle\Form\Type\HookType;

/**
 * Hook listener.
 */
class HookListener
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function sidebar(PluginHooksEvent $event)
    {
        $article = $event->getArgument('article');
        $entityManager = $this->container->get('em');
        $currencyProvider = $this->container->get('newscoop_paywall.currency_provider');
        $defaultCurrency = $currencyProvider->getDefaultCurrency();
        $name = 'article-subscription-'.$article->getArticleNumber().'-'.$article->getLanguageId();
        $subscription = $entityManager->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
             ->getOneSubscription($article->getArticleNumber(), $name);

        $form = $this->container->get('form.factory')->create(
            new HookType(),
            array(
                'price' => $subscription ? $subscription->getPrice() : null,
                'duration' => null,
            )
        );

        $response = $this->container->get('templating')->renderResponse(
            'NewscoopPaywallBundle:Hook:sidebar.html.twig',
            array(
                'form' => $form->createView(),
                'success' => false,
                'currency' => $defaultCurrency->getCode(),
                'articleNumber' => $article->getArticleNumber(),
                'articleLanguage' => $article->getLanguageId(),
                'subscription' => $subscription,
            )
        );

        $event->addHookResponse($response);
    }
}
