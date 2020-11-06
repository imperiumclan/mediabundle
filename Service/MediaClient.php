<?php

namespace ICS\MediaBundle\Service;

use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Entity\MediaVideo;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

class MediaClient
{
    protected $client;
    protected $container;
    protected $basePath;

    public function __construct(ContainerInterface $container)
    {
        $store = new Store($container->getParameter('kernel.project_dir').'/var/cache/WebServices/Downloader/');
        $this->client=new CurlHttpClient();
        $this->client = new CachingHttpClient($this->client, $store);
        $this->container = $container;

    }

    public function getBasePath()
    {
        $mediasDirectory = $this->container->getParameter('medias')['path'];
        $this->basePath = $this->container->get('kernel')->getProjectDir().'/public/'.$mediasDirectory;

        if(!file_exists($this->basePath))
        {
            mkdir($this->basePath,0777,true);
        }

        return $this->basePath;
    }

    public function DownloadFile(string $url,string $fileFullname=null)
    {
        $result = null;

        if($fileFullname==null)
        {
            $fileFullname="downloaded/".basename($url);
        }

        $response = $this->client->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Download Error: '. $this->mediaURL);
        }

        $fileHandler = fopen($fileFullname, 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        $result = new MediaFile($this->container);
        $result->Load($fileFullname);

        return $result;
    }

    public function DownloadImage(string $url,string $fileFullname=null)
    {
        $result = null;

        if($fileFullname==null)
        {
            $fileFullname="downloaded/".basename($url);
        }

        $response = $this->client->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            return null;
        }

        $fileHandler = fopen($fileFullname, 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        $result = new MediaImage($this->container);
        $result->Load($fileFullname);

        return $result;
    }

    public function DownloadVideo(string $url,string $fileFullname=null)
    {
        $result = null;

        if($fileFullname==null)
        {
            $fileFullname="downloaded/".basename($url);
        }

        $response = $this->client->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Download Error: '. $this->mediaURL);
        }

        $fileHandler = fopen($fileFullname, 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        $result = new MediaVideo($this->container);
        $result->Load($fileFullname,null,"",false);

        return $result;
    }


}