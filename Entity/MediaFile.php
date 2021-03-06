<?php

/**
 * Source code of Entity MediaFile
 *
 * @author David Dutas <david.dutas@ia.defensecdd.gouv.fr>
 */

namespace ICS\MediaBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File Management Entity
 *
 * @ORM\Table(name="media_file", schema="medias")
 * @ORM\Entity
 * @ORM\MappedSuperclass
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\HasLifecycleCallbacks()(
 *
 * @package MediaBundle
 */
class MediaFile {

    protected const FILESIZE_HUMAN_SIZE = array('b','Kb','Mb','Gb','Tb','Pb');

    /**
     * MediaFile Identifier
     *
     * @ORM\Column(type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue()
     *
     * @var integer
     */
    private $id;
    /**
     * MediaFile AbsolutePath
     *
     * @ORM\Column(type="string" , length=2048, nullable=false)
     *
     * @var string
     */
    protected $path;
    /**
     * MediaFile
     *
     * @ORM\Column(type="string" , length=255, nullable=false)
     *
     * @var string
     */
    protected $name;
    /**
     * MediaFile MimeType
     *
     * @ORM\Column(type="string" , length=255, nullable=false)
     *
     * @var string
     */
    protected $mimeType;
    /**
     * MediaFile modification Date
     *
     * @ORM\Column(type="datetime" , nullable=false)
     *
     * @var DateTime
     */
    protected $modificationDate;
    /**
     * Hash of file for integrity or duplicity
     *
     * @ORM\Column(type="string" , length=100, nullable=true)
     *
     * @var string
     */
    protected $hash;
    /**
     * filesize
     *
     * @ORM\Column(type="float" , nullable=false)
     *
     * @var int
     */
    protected $filesize;
    /**
     * Medias basepath
     *
     * @ORM\Column(type="string" , length=2048, nullable=true)
     *
     * @var string
     */
    protected $basePath;
    /**
     * Medias default directory under public
     *
     * @ORM\Column(type="string" , length=255, nullable=true)
     *
     * @var string
     */
    protected $mediasDirectory;

    protected $container;

    public function __construct(ContainerInterface $container=null)
    {
        if($container!=null)
        {
            $this->container=$container;
            $this->mediasDirectory = $container->getParameter('medias')['path'];
            $this->basePath = $container->get('kernel')->getProjectDir().'/public/'.$this->mediasDirectory;

            if(!file_exists($this->basePath))
            {
                mkdir($this->basePath,0775,true);
            }
        }

    }


    /**
     * Load File into entity
     *
     * @param string $filepath absolute path to the file
     * @param boolean $withHash does entity compute file hash ?
     * @return boolean true if successfull loading, false in other case
     */
    public function Load(string $filepath, $movedDirectory=null,$filename="",$withHash=true) : bool
    {
        try
        {
            $newPath=pathinfo($filepath)['dirname'];

            if($newPath=="")
            {
                $newPath = $this->basePath."/files";
            }

            if($movedDirectory!=null)
            {
                if($movedDirectory[0]!='/')
                {
                    $movedDirectory = '/'.$movedDirectory;
                }

                if(!file_exists($this->basePath.$movedDirectory))
                {
                    mkdir($this->basePath.$movedDirectory,0775,true);
                }

                $newPath=$this->basePath.$movedDirectory;
            }

            if(file_exists($filepath))
            {
                if($filename=="")
                {
                    $this->setName(basename($filepath));
                    $this->setPath($newPath.'/'.$this->getName());
                }
                else
                {
                    $this->setName($filename);
                    $this->setPath($newPath.'/'.$filename);
                }

                if(rename($filepath,$this->getPath(true)))
                {
                    $modifDate = new DateTime();
                    $modifDate->setTimestamp(filemtime($this->getPath()));
                    $this->setMimeType(mime_content_type($this->getPath()));
                    $this->setModificationDate($modifDate);
                    $this->setFilesize(filesize($this->getPath()));
                    if($withHash)
                    {
                        $this->setHash($this->makeHash());
                    }
                    return true;
                }

            }
            else
            {
                throw new Exception('This File at place \''.$filepath.'\' does not exist !');
            }
        }
        catch(FileException $ex)
        {
            throw $ex;
        }

        return false;
    }

    /**
     * remove file on doctrine remove
     *
     * @ORM\PostRemove
     *
     * @return void
     */
    public function removeFromFileSystem()
    {
        if(file_exists($this->getPath()))
        {
            unlink($this->getPath());
        }
    }

    /**
     * Compute file Hash
     *
     * @return string file hash computed
     */
    protected function makeHash():string
    {
        return md5_file($this->getPath());
    }

    public function getAssetPath()
    {
        return $this->mediasDirectory.str_replace($this->basePath,'',$this->path);
    }

    /**
     * Get the value of path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */
    public function setPath($path)
    {
        $this->path=$path;

        if(is_a($path,UploadedFile::class))
        {
            $this->load($path->getRealPath(),'uploaded/files',$path->getClientOriginalName());
        }

        return $this;
    }

    /**
     * Get the value of filename
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of filename
     *
     * @return  self
     */
    public function setName($filename)
    {
        $this->name = $filename;

        return $this;
    }

    /**
     * Get the value of mimeType
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set the value of mimeType
     *
     * @return  self
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get the value of creationDate
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set the value of creationDate
     *
     * @return  self
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get the value of modificationDate
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set the value of modificationDate
     *
     * @return  self
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get the value of hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set the value of hash
     *
     * @return  self
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return  bigint
     */
    public function getFilesize($human=false)
    {
        if($human)
        {
            return MediaFile::HumanizeSize($this->filesize);
        }


        return $this->filesize;
    }

    /**
     * Set filesize
     *
     * @param  bigint  $filesize  filesize
     *
     * @return  self
     */
    public function setFilesize(int $filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    public static function HumanizeSize($size)
    {
        $fz=$size;
        $i=0;
        while($fz > 1024)
        {
            $fz=$fz/1024;
            $i++;
        }

        return number_format($fz,2).' '.MediaFile::FILESIZE_HUMAN_SIZE[$i];
    }
}
