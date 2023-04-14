<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\CategoryRepository;
use App\Repository\SiteRepository;
use App\Service\FileUploader;
use App\Utils\CryptUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    public function index(SiteRepository $repository, CategoryRepository $catRepository): Response
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $list = $repository->findAll();
        foreach ($list as $site) {
            $site->cryptId($site->getId());
        }
        return $this->json(['list' => $list], 200, [], ['groups' => ['idcrypt', 'site']]);
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
     * @ParamConverter(
     *     "site",
     *     converter="fos_rest.request_body",
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function create(Site $site, SiteRepository $repository, EntityManagerInterface $em, Request $request): Response
    {
        $entity = new Site();
        $entity = $site;
        $em->persist($entity);
        $em->flush();
        $site->cryptId($site->getId());
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['idcrypt', 'site']]);
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
        return $this->json(['entity' => $site], 200, [], ['groups' => ['idcrypt','site']]);
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

    public function update($id, Site $site, SiteRepository $repository, EntityManagerInterface $em): Response
    {
        $id = CryptUtils::decryptId($id);
        $entity = $repository->findOneBy(["id" => $id]);
        if($entity){
            $entity->setTitle($site->getTitle());
            $entity->setDescription($site->getDescription());
            $entity->setPicture($site->getPicture());
            $entity->setUrl($site->getUrl());
            foreach ($site->getCategory() as $cle => $valeur) {
                $entity->addCategory($valeur);
            }
            $em->persist($entity);
            $em->flush();
            $entity->cryptId($id);
        } else {
            return $this->json(['error'=> 'No entity found with given id']);
        }
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['idcrypt', 'site']]);
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
