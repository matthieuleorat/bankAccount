<?php

namespace App\CategoryGuesser;

use App\Entity\Category;
use App\Entity\DetailsToCategory;
use App\Entity\Transaction;

class CategoryGuesser
{
    public static function execute(DetailsToCategory $detailsToCategory, string $details) : ? Category
    {
        preg_match("/{$detailsToCategory->getRegex()}/m", $details, $matches);
        if (count($matches)) {
            return $detailsToCategory->getCategory();
        }

        return null;
    }
}
