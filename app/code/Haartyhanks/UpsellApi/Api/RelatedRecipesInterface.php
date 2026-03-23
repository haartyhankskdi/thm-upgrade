<?php
namespace Haartyhanks\UpsellApi\Api;

interface RelatedRecipesInterface
{
    /**
     * Get related recipes (blog posts) for a product
     *
     * @param int $productId
     * @return array
     */
    public function getRelatedRecipes($productId);
}
