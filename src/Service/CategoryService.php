<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;

class CategoryService
{
    public function __construct
    (
        private readonly CategoryRepository $categoryRepository,
        private readonly SerializerService $serializerService
    )
    {
    }

    public function create($category): void
    {
        // check errors
        $this->serializerService->checkErrors($category);
        // save
        $this->categoryRepository->save($category, true);
    }

    public function update(Category $category, $updatedCategory): void
    {
        // update
        $category->setName($updatedCategory->getName());
        // check errors
        $this->serializerService->checkErrors($category);
        // save
        $this->categoryRepository->save($category, true);
    }
}