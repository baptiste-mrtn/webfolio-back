<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\CategoryRepository;
use App\Repository\SiteRepository;
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
 * @Route("/api/sites", name="app_sites")
 */
class SiteController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"GET"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(SiteRepository $repository): Response
    {
        $list = $repository->findAll();
        foreach ($list as $site) {
            $site->cryptId($site->getId());
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['idcrypt', 'site', 'category']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Sites")
     * @OA\RequestBody(
     *     @Model(type=Site::class,groups={"site"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */


     // ParamConverter("site",converter="fos_rest.request_body")

    public function create( EntityManagerInterface $em, Request $request, CategoryRepository $categoryRepository): Response
    {
        $values = json_decode($request->getContent(),true);

        $site = new Site();

        $today = new DateTimeImmutable();
        $site->setCreatedAt($today);
        $site->setTitle($values['title'])
        ->setDescription($values['description'])
        ->setPicture($values['picture'])
        ->setUrl($values['url']);
        foreach ($values['categories'] as $category ) {
            $category = $categoryRepository->find($category['id']);
            $site->addCategory($category);
        }
        $em->persist($site);
        $em->flush();
        $site->cryptId($site->getId());
        return $this->json(['entity' => $site], 200, [], ['groups' => ['idcrypt', 'site', 'category']]);
    }

    /**
     * @Route("/read/{id}" , name="read",methods={"GET"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function read($id, SiteRepository $repository): Response
    {
        $id = CryptUtils::decryptId($id);
        $site = $repository->findOneBy(["id" => $id]);
        $site->cryptId($id);
        return $this->json(['entity' => $site], 200, [], ['groups' => ['idcrypt','site', 'category']]);
    }

    /**
     * @Route("/update/{id}" , name="update",methods={"PUT"})
     * @Tag(name="Sites")
     * @OA\RequestBody(
     *     @Model(type=Site::class,groups={"site"}),
     *     description="fields"
     * )
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @ParamConverter(
     *     "site",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function update($id, Site $site, SiteRepository $repository, CategoryRepository $categoryRepository, EntityManagerInterface $em, Request $request): Response
    {
        $id = CryptUtils::decryptId($id);
        $values = json_decode($request->getContent(),true);
        $entity = $repository->findOneBy(["id" => $id]);
        if($entity){
            $entity->setTitle($site->getTitle())
            ->setDescription($site->getDescription())
            ->setUrl($site->getUrl());
            if($site->getPicture() != null || $site->getPicture() != ""){
                $entity->setPicture($site->getPicture());
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
            return $this->json(['error'=> 'No entity found with given id']);
        }
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['idcrypt', 'site', 'category']]);
    }

    /**
     * @Route("/delete/{id}" , name="delete",methods={"DELETE"})
     * @Tag(name="Sites")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function delete($id, SiteRepository $repository, EntityManagerInterface $em): Response
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
            $dir = $this->getParameter('sites_directory');
            $res = $uploader->upload($file, $dir);
        } else {
            return $this->json(['error' => 'no image found']);
        }
        return $this->json(['location' =>  $res]);
    }
}
