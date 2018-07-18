<?php

namespace Plugin\Sample\Form\Type\Admin;

use Plugin\Sample\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('param01', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ])->add('param02', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ],
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Config $Config */
            $Config = $event->getData();

            if ($Config && $subData = $Config->getSubData()) {
                $subData = unserialize($subData);

                $form = $event->getForm();
                $form['param01']->setData($subData['param01']);
                $form['param02']->setData($subData['param02']);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $subData = [
                'param01' => $form['param01']->getData(),
                'param02' => $form['param02']->getData(),
            ];

            /** @var Config $Config */
            $Config = $event->getData();
            $Config->setSubData(serialize($subData));
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
