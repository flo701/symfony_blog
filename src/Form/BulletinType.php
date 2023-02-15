<?php

namespace App\Form;

use App\Entity\Bulletin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BulletinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre du Bulletin'])
            ->add('category', ChoiceType::class, ['label' => 'Catégories', 'choices' => ['Général' => 'general', 'Divers' => 'divers', 'Urgent' => 'urgent',], 'expanded' => false, 'multiple' => false,])
            // 'choices' : valeur affichée => valeur retenue.
            // 'expanded' : menu déroulant.
            // 'multiple' : un seul choix possible
            ->add('content', TextareaType::class, ['label' => 'Contenu'])
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['style' => 'margin-top: 5px', 'class' => 'btn btn-success',]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bulletin::class,
        ]);
    }
}
