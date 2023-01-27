<?php

namespace App\Controller\admin;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Service\SerializerService;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/admin/categories')]
class CategoryController extends AbstractController
{
    public function __construct
    (
        private readonly CategoryRepository $categoryRepository,
        private readonly SerializerService $serializerService,
        private readonly CategoryService $categoryService,
        private readonly TranslatorInterface $translator
    )
    {
    }

    #[Route('', name: 'api_admin_categories', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // get, serialize & return
        $categories = $this->categoryRepository->findAll();
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

    #[Route('', name: 'api_admin_categories_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // deserialize, create
        $category = $this->serializerService->deserialize(Category::GROUP_WRITE ,$request, Category::class);
        $this->categoryService->create($category);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.category.created_success')],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_admin_categories_update', methods: ['PUT'])]
    public function update(Request $request, Category $category): JsonResponse
    {
        // deserialize & update
        $updatedCategory = $this->serializerService->deserialize(Category::GROUP_WRITE, $request, Category::class);
        $this->categoryService->update($category, $updatedCategory);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.category.updated_success')],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_admin_categories_delete', methods: ['DELETE'])]
    public function delete(Category $category): JsonResponse
    {
        // delete & return
        $this->categoryRepository->remove($category, true);
        return new JsonResponse(
            ['message' => $this->translator->trans('message.category.deleted_success')],
            Response::HTTP_OK
        );
    }
}