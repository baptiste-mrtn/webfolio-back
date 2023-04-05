<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Repository\GalleryRepository;
use App\Utils\CryptUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Tag;

/**
 * @Route("/api/gallery", name="app_gallery")
 * */
class GalleryController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"POST"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(GalleryRepository $repository): Response
    {
        $list = $repository->findAll();
        foreach ($list as $gallery) {
            $gallery["id"] = CryptUtils::cryptId($gallery["id"]);
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['id', 'gallery']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function create(Gallery $gallery, GalleryRepository $repository, EntityManagerInterface $em): Response
    {
        $entity = new Gallery();
        $entity = $gallery;
        $em->persist($entity);
        $em->flush();
        $entity['id'] = CryptUtils::cryptId($entity["id"]);
        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['id', 'gallery']]);
    }

    /**
     * @Route("/read/{id}" , name="read",methods={"GET"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function read($id, GalleryRepository $repository): Response
    {
        $id = CryptUtils::decryptId($id);
        $gallery = $repository->findOneBy(["id" => $id]);
        $gallery["id"] = CryptUtils::cryptId($gallery["id"]);

        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['gallery' => $gallery], 200, [], ['groups' => ['', 'id']]);
    }

    /**
     * @Route("/update/{id}" , name="update",methods={"PUT"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function update($id, Gallery $gallery, GalleryRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        $entity = $gallery;
        $em->persist($entity);
        $em->flush();
        /* $count = $repository->countByQueryParam($queryParameter, $this->getUser()); */
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['', 'id']]);
    }

    /**
     * @Route("/delete/{id}" , name="delete",methods={"DELETE"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function delete($id, GalleryRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        $em->remove($entity);
        $em->flush();
        return $this->json(['entity ' . $id . ' deleted'], 200, []);
    }
}
