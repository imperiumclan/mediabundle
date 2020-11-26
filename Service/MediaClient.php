<?php

namespace ICS\MediaBundle\Service;

use Exception;
use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Entity\MediaVideo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

/**
 * Class for access and creation of media.
 */
class MediaClient
{
    /**
     * @var Symfony\Component\HttpClient\CurlHttpClient Webclient
     */
    protected $client;
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface Symfony container
     */
    protected $container;
    /**
     * @var string Base path for meria recording
     */
    protected $basePath;

    /**
     * Constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $store = new Store($container->getParameter('kernel.project_dir').'/var/cache/MediaBundle/Downloader/');
        $this->client = new CurlHttpClient();
        $this->client = new CachingHttpClient($this->client, $store);
        $this->container = $container;
    }

    /**
     * Get Base path of media.
     */
    public function getBasePath()
    {
        $mediasDirectory = $this->container->getParameter('medias')['path'];
        $this->basePath = $this->container->get('kernel')->getProjectDir().'/public/'.$mediasDirectory;

        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, 0777, true);
        }

        return $this->basePath;
    }

    /**
     * Download file from the web.
     *
     * @param string $url          Url for download
     * @param string $fileFullname Output file fullname
     *
     * @return string Output file fullname of downloaded file
     */
    private function Download(string $url = null, string $fileFullname = null): ?string
    {
        if (null != $url) {
            if (null == $fileFullname) {
                $fileFullname = 'downloaded/'.basename($url);
            }

            try {
                $response = $this->client->request('GET', $url);

                if (200 !== $response->getStatusCode()) {
                    return null;
                }

                $fileHandler = fopen($fileFullname, 'w');
                foreach ($this->client->stream($response) as $chunk) {
                    fwrite($fileHandler, $chunk->getContent());
                }
            } catch (Exception $ex) {
                return null;
            }
        }

        return $fileFullname;
    }

    /**
     * Download File.
     *
     * @param string $url          Url for download
     * @param string $fileFullname Output file fullname
     *
     * @return MediaFile Return a mediafile or null
     */
    public function DownloadFile(string $url = null, string $fileFullname = null): ?MediaFile
    {
        $result = null;
        $filePath = $this->Download($url, $fileFullname);
        if (null != $filePath) {
            $result = new MediaFile($this->container);
            $result->Load($filePath);
        }

        return $result;
    }

    /**
     * Download Image.
     *
     * @param string $url          Url for download
     * @param string $fileFullname Output image fullname
     *
     * @return MediaImage Return a mediaimage or null
     */
    public function DownloadImage(string $url = null, string $fileFullname = null): ?MediaImage
    {
        $result = null;

        $filePath = $this->Download($url, $fileFullname);
        if (null != $filePath) {
            $result = new MediaImage($this->container);
            $result->Load($filePath);
        }

        return $result;
    }

    /**
     * Download File.
     *
     * @param string $url          Url for download
     * @param string $fileFullname Output video fullname
     *
     * @return MediaVideo Return a mediavideo or null
     */
    public function DownloadVideo(string $url = null, string $fileFullname = null)
    {
        $result = null;
        $filePath = $this->Download($url, $fileFullname);
        if (null != $filePath) {
            $result = new MediaVideo($this->container);
            $result->Load($filePath, null, '', false);
        }

        return $result;
    }
}
