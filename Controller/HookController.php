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
use Newscoop\PaywallBundle\Form\Type\DurationType;
use Newscoop\PaywallBundle\Form\Type\SubscriptionConfType;
use Newscoop\PaywallBundle\Entity\Subscription;
use Newscoop\Entity\Article;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HookController extends BaseController
{
    /**
     * @Route("/admin/paywall_plugin/sidebar/{articleNumber}/{articleLanguage}", options={"expose"=true})
     * @Method("POST")
     */
    public function sidebarAction(Request $request, $articleNumber, $articleLanguage)
    {
        $form = $this->createForm(new DurationType());
        $em = $this->get('em');
        $success = false;
        $currencyProvider = $this->get('newscoop_paywall.currency_provider');
        $defaultCurrency = $currencyProvider->getDefaultCurrency();

        $article = $this->findOneOr404($articleNumber, $articleLanguage);
        $subscription = $this->findOneByArticle($article);
        $subscriptionForm = $this->createSubscriptionForm($subscription);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $duration = $form->getData();
            $durationEntity = $em->getRepository('Newscoop\PaywallBundle\Entity\Duration')
                ->findOneBy(array(
                    'value' => $duration->getValue(),
                    'subscription' => $subscription->getId(),
            ));

            if (!$durationEntity) {
                $duration->setSubscription($subscription);
                $subscription->addRange($duration);

                $success = true;
                $em->flush();
            }
        }

        return $this->returnResponse(array(
            'form' => $form->createView(),
            'subscriptionForm' => $subscriptionForm->createView(),
            'success' => $success,
            'currency' => $defaultCurrency->getCode(),
            'articleNumber' => $articleNumber,
            'articleLanguage' => $articleLanguage,
            'subscription' => $subscription,
        ));
    }

    /**
     * @Route("/admin/paywall_plugin/sidebar/price/{articleNumber}/{articleLanguage}", options={"expose"=true})
     * @Method("POST")
     */
    public function priceAction(Request $request, $articleNumber, $articleLanguage)
    {
        $form = $this->createForm(new DurationType());
        $subscriptionForm = $this->createSubscriptionForm();
        $em = $this->get('em');
        $success = false;
        $currencyProvider = $this->get('newscoop_paywall.currency_provider');
        $defaultCurrency = $currencyProvider->getDefaultCurrency();

        $article = $this->findOneOr404($articleNumber, $articleLanguage);
        $subscription = $this->findOneByArticle($article);
        $subscriptionForm->handleRequest($request);
        if ($subscriptionForm->isValid()) {
            $data = $subscriptionForm->getData();
            $subscriptionFactory = $this->get('newscoop_paywall.subscription.factory');
            $name = $this->getSubscriptionName($article);
            if (!$subscription) {
                $parameters = array(
                    'object' => $article,
                    'name' => $name,
                    'type' => 'article',
                    'price' => $data->getPrice(),
                    'currencyCode' => $defaultCurrency->getCode(),
                );

                $subscription = $subscriptionFactory->createSubscription($parameters);
                $em->persist($subscription);
            } else {
                $subscription->setPrice($data->getPrice());
            }

            $success = true;
            $em->flush();
        } else {
            $subscriptionForm = $this->createSubscriptionForm($subscription);
        }

        return $this->returnResponse(array(
            'form' => $form->createView(),
            'subscriptionForm' => $subscriptionForm->createView(),
            'success' => $success,
            'currency' => $defaultCurrency->getCode(),
            'articleNumber' => $articleNumber,
            'articleLanguage' => $articleLanguage,
            'subscription' => $subscription,
        ));
    }

    private function createSubscriptionForm(Subscription $subscription = null)
    {
        return $this->createForm(new SubscriptionConfType(), $subscription);
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

    private function returnResponse(array $data)
    {
        return $this->container->get('templating')->renderResponse(
            'NewscoopPaywallBundle:Hook:sidebar.html.twig',
            $data
        );
    }
}
