<?php

/**
 * Source code of Entity MediaFile
 *
 * @author David Dutas <david.dutas@ia.defensecdd.gouv.fr>
 */

namespace ICS\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File Management Entity
 *
 * @ORM\Table(name="media_video", schema="medias")
 * @ORM\Entity
 * @ORM\MappedSuperclass
 *
 * @package MediaBundle
 */
class MediaVideo extends MediaFile {

    
    public function __construct(ContainerInterface $container=null)
    {
        parent::__construct($container);
    }


    public function Load(string $filepath, $movedDirectory=null, $filename="",$withHash=true) : bool
    {
        $result = parent::Load($filepath,$movedDirectory,$filename,$withHash);

        //TODO : Ecrire les informations Vidéos

        return $result;
    }

    
    /**
     * Set the value of path
     *
     * @return  self
     */ 
    // public function setPath($path)
    // {
    //     $this->path=$path;
        
    //     if(is_a($path,UploadedFile::class))
    //     {
    //         $this->load($path->getRealPath(),'uploaded/images',$path->getClientOriginalName());   
    //     }

    //     return $this;
    // }
}
