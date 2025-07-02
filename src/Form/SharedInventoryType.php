<?php

namespace App\Form;

use App\Entity\SharedInventory;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class SharedInventoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // En mode édition, on ne montre que le champ accessLevel
        if (!$options['edit_mode']) {
            $builder->add('userEmail', EmailType::class, [
                'label' => 'Adresse e-mail',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une adresse e-mail']),
                    new Email(['message' => 'Veuillez entrer une adresse e-mail valide']),
                ],
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse e-mail de l\'utilisateur'
                ]
            ]);
        }
        
        $builder->add('accessLevel', ChoiceType::class, [
            'label' => 'Niveau d\'accès',
            'choices' => [
                'Lecture uniquement' => 'view',
                'Lecture et modification' => 'edit',
                'Tous les droits (y compris suppression)' => 'admin',
            ],
            'expanded' => true,
            'constraints' => [
                new NotBlank(['message' => 'Veuillez sélectionner un niveau d\'accès']),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SharedInventory::class,
            'edit_mode' => false, // Par défaut, on est en mode création
        ]);
    }
}
