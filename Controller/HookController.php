<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Newscoop\PaywallBundle\Form\Type\HookType;
use Newscoop\PaywallBundle\Entity\Subscription;
use Newscoop\PaywallBundle\Entity\Duration;
use Newscoop\Entity\Article;

class HookController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/sidebar/{articleNumber}/{articleLanguage}", options={"expose"=true})
     * @Method("POST")
     */
    public function sidebarAction(Request $request, $articleNumber, $articleLanguage)
    {
        $form = $this->createForm(new HookType());
        $em = $this->get('em');
        $success = false;
        $currencyProvider = $this->get('newscoop_paywall.currency_provider');
        $defaultCurrency = $currencyProvider->getDefaultCurrency();

        $article = $this->findOneOr404($articleNumber, $articleLanguage);
        $subscription = $this->findOneByArticle($article);
        $form->handleRequest($request);
        if ($form->get('saveSubmit')->isClicked()) {
            if ($subscription) {
                $subscription->setPrice($form->get('price')->getData());
                $success = true;
            }
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $subscriptionFactory = $this->get('newscoop_paywall.subscription.factory');
            $name = $this->getSubscriptionName($article);
            $duration = $data['duration'];
            if (!$subscription) {
                $parameters = array(
                    'object' => $article,
                    'name' => $name,
                    'type' => 'article',
                    'price' => $data['price'],
                    'currencyCode' => $defaultCurrency->getCode(),
                    'duration' => $duration,
                );

                $subscription = $subscriptionFactory->createSubscription($parameters);
                $em->persist($subscription);
            } else {
                $duration->setSubscription($subscription);
                $subscription->addRange($duration);
            }

            $success = true;
        }

        $em->flush();

        return $this->container->get('templating')->renderResponse(
            'NewscoopPaywallBundle:Hook:sidebar.html.twig',
            array(
                'form' => $form->createView(),
                'success' => $success,
                'currency' => $defaultCurrency->getCode(),
                'articleNumber' => $articleNumber,
                'articleLanguage' => $articleLanguage,
                'subscription' => $subscription,
            )
        );
    }

    private function findOneOr404($articleNumber, $articleLanguage)
    {
        $em = $this->get('em');
        $article = $em->getRepository('Newscoop\Entity\Article')->findOneBy(array(
            'number' => $articleNumber,
            'language' => $articleLanguage,
        ));

        if (!$article) {
            throw new NotFoundHttpException('The article does not exist.');
        }

        return $article;
    }

    private function findOneByArticle(Article $article)
    {
        $em = $this->get('em');
        $subscription = $em->getRepository('Newscoop\PaywallBundle\Entity\Subscription')
            ->getOneSubscription($article->getNumber(), $this->getSubscriptionName($article));

        return $subscription;
    }

    private function getSubscriptionName(Article $article)
    {
        return 'article-subscription-'.$article->getNumber().'-'.$article->getLanguageId();
    }
}
