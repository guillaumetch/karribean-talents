<?php

namespace AppBundle\Form;

use AppBundle\Entity\Art;
use AppBundle\Entity\ArtForm;
use AppBundle\Entity\Artwork;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtworkType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('media')
            ->add('title')
            ->add('description')
            ->add('art',EntityType::class, array(
                'class'=> Art::class,
                'choice_label' => 'libelle',
                'expanded'=>false,
                'multiple'=>false
            ))
            ->add('artform',EntityType::class, array(
                'class'=> ArtForm::class,
                'query_builder' => function(EntityRepository $er) {

                    return $er->createQueryBuilder('artform')
                        ->where('artform.art = 1');
                },
                'choice_label' => 'libelle',
                'expanded'=>false,
                'multiple'=>false
            ))

            ->remove('createAt')
            ->remove('user');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Artwork'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_artwork';
    }


}
