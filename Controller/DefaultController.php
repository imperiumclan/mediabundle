<?php

namespace ICS\MediaBundle\Controller;

use ICS\MediaBundle\Entity\MediaFile;
use ICS\MediaBundle\Entity\MediaImage;
use ICS\MediaBundle\Entity\MediaVideo;
use ICS\MediaBundle\Form\Type\MediaFileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/" , name="ics_media_homepage")
     */
    public function index(ContainerInterface $container)
    {
        // $f = new MediaFile($container);
        // $f->Load('D:\Developpement\Symfony\CastelRock2\public\images\info.png','default/images');
        // $em=$this->getDoctrine()->getManager();
        // $em->persist($f);
        // $em->flush();

        $objFiles = $this->getDoctrine()->getRepository(MediaFile::class)->findAll();
        $objImages = $this->getDoctrine()->getRepository(MediaImage::class)->findAll();
        $objVideo = $this->getDoctrine()->getRepository(MediaVideo::class)->findAll();

        $sizeFile = 0.0;
        foreach ($objFiles as $f) {
            $sizeFile += $f->getFilesize();
        }

        $sizeImage = 0.0;
        foreach ($objImages as $f) {
            $sizeImage += $f->getFilesize();
        }

        $sizeVideo = 0.0;
        foreach ($objVideo as $f) {
            $sizeVideo += $f->getFilesize();
        }

        $sdata[0]['name'] = 'Files';
        $sdata[0]['y'] = $sizeFile - $sizeImage - $sizeVideo;
        $sdata[1]['name'] = 'Images';
        $sdata[1]['y'] = $sizeImage;
        $sdata[2]['name'] = 'Videos';
        $sdata[2]['y'] = $sizeVideo;

        $files = 0.0 + count($objFiles);
        $images = 0.0 + count($objImages);
        $videos = 0.0 + count($objVideo);

        $data[0]['name'] = 'Files';
        $data[0]['y'] = $files - $images - $videos;
        $data[1]['name'] = 'Images';
        $data[1]['y'] = $images;
        $data[2]['name'] = 'Videos';
        $data[2]['y'] = $videos;

        return $this->render('@Media/index.html.twig', [
            'data' => $data,
            'sdata' => $sdata,
        ]);
    }

    /**
     * @Route("/files" , name="ics_media_file")
     * @Route("/files/edit/{id}" , name="ics_media_file_edit")
     */
    public function fileManagement(Request $request, ContainerInterface $container, MediaFile $file = null)
    {
        if (null == $file) {
            $file = new MediaFile($container);
        }

        $form = $this->createForm(MediaFileType::class, $file);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();
        }

        $files = $this->getDoctrine()->getRepository(MediaFile::class)->findAll();

        return $this->render('@Media/file.html.twig', [
            'files' => $files,
            'filetype' => 'file',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/images" , name="ics_media_image")
     * @Route("/images/edit/{id}" , name="ics_media_image_edit")
     */
    public function imageManagement(Request $request, ContainerInterface $container, MediaImage $file = null)
    {
        if (null == $file) {
            $file = new MediaImage($container);
        }

        $form = $this->createForm(MediaFileType::class, $file, ['data_class' => MediaImage::class]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();
        }

        $files = $this->getDoctrine()->getRepository(MediaImage::class)->findAll();

        return $this->render('@Media/file.html.twig', [
            'files' => $files,
            'filetype' => 'image',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/images" , name="ics_media_video")
     * @Route("/images/edit/{id}" , name="ics_media_video_edit")
     */
    public function videoManagement(Request $request, ContainerInterface $container, MediaImage $file = null)
    {

        return new Response('TODO : Code Video Management');
    }
}
