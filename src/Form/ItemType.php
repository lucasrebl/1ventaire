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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inventory = $options['inventory'];
        
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
            ->add('imageFile', FileType::class, [
                'label' => 'Image (optionnelle)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG) de moins de 5 Mo.',
                    ]),
                ],
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
                'query_builder' => function (EntityRepository $er) use ($inventory) {
                    return $er->createQueryBuilder('c')
                        ->where('c.inventory = :inventory')
                        ->setParameter('inventory', $inventory);
                },
            ])
            ->add('location', EntityType::class, [
                'class' => Location::class,
                'label' => 'Emplacement',
                'required' => false,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) use ($inventory) {
                    return $er->createQueryBuilder('l')
                        ->where('l.inventory = :inventory')
                        ->setParameter('inventory', $inventory);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'inventory' => null,
        ]);
        
        $resolver->setRequired('inventory');
    }
}