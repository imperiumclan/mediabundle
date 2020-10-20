<?php
/**
 * Form control for media management
 * 
 * @author David Dutas <david.dutasàia.defensecdd.gouv.fr>
 */

namespace ICS\MediaBundle\Form\Type;


use Liip\ImagineBundle\Form\Type\ImageType;
use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFileType extends AbstractType {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        switch($options['data_class'])
        {
           
            case MediaImage::class :
                $builder->add('path',ImageType::class,array(
                    'label' => "Image",
                    'image_filter' => 'mediaBundleThumbnail',
                    'image_path' => 'medias',
                    'data_class' => null

                ));
            break;

            default :
                $builder->add('path',FileType::class,array(
                    'label' => "Fichier", 
                    'data_class' => null
                ))
           
            ;

            $builder->add('submit',SubmitType::class);
        }

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver-> setDefaults([
            'data_class' => MediaFile::class,
        ]);
    }
}