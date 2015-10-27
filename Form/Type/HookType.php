<?php

/**
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2015 Sourcefabric z.ú.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
namespace Newscoop\PaywallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class HookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', 'integer', array(
                'required' => true,
            ))
            ->add('duration', new DurationType())
            ->add('saveSubmit', 'submit')
            ->add('saveAndAdd', 'submit');
    }

    public function getName()
    {
        return 'pluginHookForm';
    }
}
