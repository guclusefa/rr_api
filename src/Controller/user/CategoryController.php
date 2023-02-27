<?php

namespace App\Controller\user;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    public function __construct
    (
        private readonly CategoryRepository $categoryRepository,
        private readonly SerializerService $serializerService,
    )
    {
    }

    #[Route('', name: 'api_admin_categories', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $categories = $this->categoryRepository->advanceSearch($search, $order, $direction, $page, $limit);
        $categories = $this->serializerService->serialize(Category::GROUP_GET, $categories);
        return new JsonResponse(
            $categories,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_admin_categories_show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        // get, serialize & return
        $category = $this->serializerService->serialize(Category::GROUP_ITEM, $category);
        return new JsonResponse(
            $category,
            Response::HTTP_OK,
            [],
            true
        );
    }
}