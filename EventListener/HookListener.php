<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginHooksEvent;
use Newscoop\PaywallBundle\Form\Type\SubscriptionConfType;
use Newscoop\PaywallBundle\Form\Type\DurationType;

/**
 * Hook listener.
 */
class HookListener
{
    private $templating;
    private $entityManager;
    private $currencyProvider;
    private $formFactory;

    public function __construct($templating, $entityManager, $currencyProvider, $formFactory)
    {
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->currencyProvider = $currencyProvider;
        $this->formFactory = $formFactory;
    }

    public function sidebar(PluginHooksEvent $event)
    {
        $article = $event->getArgument('article');
        $defaultCurrency = $this->currencyProvider->getDefaultCurrency();
        $name = 'article-subscription-'.$article->getArticleNumber().'-'.$article->getLanguageId();
        $subscription = $this->entityManager->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
             ->getOneSubscription($article->getArticleNumber(), $name);

        $form = $this->formFactory->create(new DurationType());
        $subscriptionForm = $this->formFactory->create(
            new SubscriptionConfType(),
            $subscription
        );

        $response = $this->templating->renderResponse(
            'NewscoopPaywallBundle:Hook:sidebar.html.twig',
            array(
                'form' => $form->createView(),
                'subscriptionForm' => $subscriptionForm->createView(),
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
