<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Location;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ItemType extends AbstractType
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->tokenStorage->getToken()->getUser();
        
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'article',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nom pour l\'article',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnelle)',
                'required' => false,
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité',
                'required' => false,
                'attr' => [
                    'min' => 1,
                ],
            ])
            ->add('barcode', TextType::class, [
                'label' => 'Code-barres (optionnel)',
                'required' => false,
            ])
            ->add('expiryDate', DateType::class, [
                'label' => 'Date de péremption (optionnelle)',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Catégorie',
                'required' => false,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'label' => 'Emplacement',
                'required' => false,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('l')
                        ->where('l.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('l.name', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}