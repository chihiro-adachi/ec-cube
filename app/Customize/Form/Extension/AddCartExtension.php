<?php

namespace Customize\Form\Extension;


use Eccube\Form\Type\AddCartType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddCartExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name_printed', TextType::class, [
            'label' => '名入れ',
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return AddCartType::class;
    }
}