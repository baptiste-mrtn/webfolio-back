<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Repository\CategoryRepository;
use App\Repository\GalleryRepository;
use App\Repository\ReviewRepository;
use App\Service\FileUploader;
use App\Utils\CryptUtils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/gallery", name="app_gallery")
 */
class GalleryController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"GET"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(GalleryRepository $repository, CategoryRepository $catRepository): Response
    {
        $list = $repository->findAll();
        foreach ($list as $gallery) {
            $gallery->cryptId($gallery->getId());
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['idcrypt', 'gallery', 'category', 'review']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Gallery")
     * @OA\RequestBody(
     *     @Model(type=Gallery::class,groups={"gallery"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    // ParamConverter("gallery",converter="fos_rest.request_body")

    public function create(EntityManagerInterface $em, Request $request, CategoryRepository $categoryRepository): Response
    {
        $values = json_decode($request->getContent(), true);

        $gallery = new Gallery();
        $today = new DateTimeImmutable();
        $gallery->setCreatedAt($today);
        $gallery->setTitle($values['title'])
            ->setDescription($values['description'])
            ->setPicture($values['picture']);
        foreach ($values['categories'] as $category) {
            $category = $categoryRepository->find($category['id']);
            $gallery->addCategory($category);
        }
        $em->persist($gallery);
        $em->flush();
        $gallery->cryptId($gallery->getId());
        return $this->json(['entity' => $gallery], 200, [], ['groups' => ['idcrypt', 'gallery', 'category', 'review']]);
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

    public function read($id, GalleryRepository $repository, ReviewRepository $revRepo): Response
    {
        $id = CryptUtils::decryptId($id);
        $gallery = $repository->findOneBy(["id" => $id]);
        $gallery->cryptId($id);
        $reviews = $revRepo->findBy(["gallery"=>$gallery]);
        foreach ($reviews as $review) {
            $review->cryptId($review->getId());
        }
        return $this->json(['entity' => $gallery], 200, [], ['groups' => ['idcrypt', 'gallery', 'category', 'review']]);
    }

    /**
     * @Route("/update/{id}" , name="update",methods={"PUT"})
     * @Tag(name="Gallery")
     * @OA\RequestBody(
     *     @Model(type=Gallery::class,groups={"gallery"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @ParamConverter(
     *     "gallery",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function update($id, Gallery $gallery, GalleryRepository $repository, CategoryRepository $categoryRepository, EntityManagerInterface $em, Request $request): Response
    {
        $id = CryptUtils::decryptId($id);
        $values = json_decode($request->getContent(), true);
        $entity = $repository->findOneBy(["id" => $id]);
        if ($entity) {
            $entity->setTitle($gallery->getTitle())
                ->setDescription($gallery->getDescription());
            if ($gallery->getPicture() != null || $gallery->getPicture() != "") {
                $entity->setPicture($gallery->getPicture());
            }
            $newCategories = $values["categories"];
            $oldCategories = $entity->getCategories();
            foreach ($oldCategories as $category) {
                if (!in_array($category, $newCategories)) {
                    $category = $categoryRepository->find($category->getId());
                    $entity->removeCategory($category);
                }
            }

            // Ajouter les nouvelles catégories qui ne sont pas dans les anciennes catégories
            foreach ($newCategories as $category) {
                if (!$oldCategories->contains($category)) {
                    $category = $categoryRepository->find($category['id']);
                    $entity->addCategory($category);
                }
            }
            $em->persist($entity);
            $em->flush();
            $entity->cryptId($id);
        } else {
            return $this->json(['error' => 'No entity found with given id']);
        }
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['idcrypt', 'gallery', 'category', 'review']]);
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

    /**
     * @Route("/file" , name="file",methods={"POST"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function uploadFile(Request $request, FileUploader $uploader): Response
    {
        $file = $request->files->get('picture');
        if ($file) {
            $dir = $this->getParameter('gallery_directory');
            $res = $uploader->upload($file, $dir);
        } else {
            return $this->json(['error' => 'no image found']);
        }
        return $this->json(['location' =>  $res]);
    }

        /**
     * @Route("/find/{category}" , name="find",methods={"POST"})
     * @Tag(name="Gallery")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

     public function findByCategory($category, GalleryRepository $repository): Response
     {
         $list = $repository->findAll();
         $finalList = [];
         foreach ($list as $site) {
             foreach ($site->getCategories() as $categoryGallery) {
                 if ($categoryGallery->getName() === $category) {
                     $site->cryptId($site->getId());
                     array_push($finalList, $site);
                 }
             }
         }
         return $this->json(['list' => $finalList], 200, [], ['groups' => ['idcrypt', 'gallery', 'category', 'review']]);
     }
}
