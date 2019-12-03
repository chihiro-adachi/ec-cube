<?php

namespace Customize\Form\Extension;

use Eccube\Form\Type\Shopping\OrderItemType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderItemTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name_printed', TextType::class, [
            'label' => '名入れ(+500円)',
            'required' => false,
        ]);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return OrderItemType::class;
    }
}
