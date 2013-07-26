<?php
/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SubscriptionConfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', null, array(
            'label' => 'Name of subscription: ',
            'error_bubbling' => true,
            'invalid_message' => 'Subscription name can not be empty.'
        ))
        ->add('type', 'choice', array(
            'label'  => 'Type of subscription: ',
            'choices'   => array(
                'publication'   => 'Publication',
                'issue'   => 'Issue',
                'section' => 'Section',
                'article'   => 'Article',
            )
        ))
        ->add('range', null, array(
            'label' => 'Duration of subscription in days: ',
            'attr' => array('min'=>'1'),
            'error_bubbling' => true,
            'invalid_message' => 'Duration field is invalid.'
        ))
        ->add('price', null, array(
            'label' => 'Price: ',
            'error_bubbling' => true,
            'required' => true,
            'invalid_message' => 'Price field is invalid.'
        ))
        ->add('currency', null, array(
            'label' => 'Currency: ',
            'error_bubbling' => true,
            'required' => true,
            'invalid_message' => 'Type currency.'
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Newscoop\PaywallBundle\Entity\Subscriptions',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'subscriptionconf';
    }
}