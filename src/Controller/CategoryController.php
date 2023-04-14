<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Tag;
use App\Entity\Category;
use App\Repository\CategoryRepository;

/**
 * @Route("/api/category", name="app_category")
 * */
class CategoryController extends AbstractController
{
    /**
     * @Route("/all" , name="list",methods={"GET"})
     * @Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function index(CategoryRepository $repository): Response
    {
        $list = $repository->findAll();
        return $this->json(['list' => $list], 200, [], ['groups' => ['id', 'category']]);
    }

    /**
     * @Route("/create" , name="create",methods={"POST"})
     * @Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function create(Category $category, CategoryRepository $repository, EntityManagerInterface $em): Response
    {
        $entity = new Category();
        $entity = $category;
        $em->persist($entity);
        $em->flush();
        return $this->json(['entity' => $entity], 200, [], ['groups' => ['id', 'category']]);
    }

    /**
     * @Route("/delete/{id}" , name="delete",methods={"DELETE"})
     * @Tag(name="Category")
     * @OA\Response(
     *     response=200,
     *     description="Status ok"
     * )
     * @param Request $request
     * @return JsonResponse
     */

    public function delete($id, CategoryRepository $repository, EntityManagerInterface $em): Response
    {
        $entity = $repository->findOneBy(["id" => $id]);
        $em->remove($entity);
        $em->flush();
        return $this->json(['entity ' . $id . ' deleted'], 200, []);
    }
}
