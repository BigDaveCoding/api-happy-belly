<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RecipeApiServiceProvider
{
    public static function pagination(LengthAwarePaginator $data): array
    {
        return [
            'current_page' => $data->currentPage(),
            'total_recipes' => $data->total(),
            'next_page_url' => $data->nextPageUrl(),
            'previous_page_url' => $data->previousPageUrl(),
            'all_page_urls' => $data->getUrlRange(1, $data->lastPage()),
        ];
    }

    public static function paginationCollection(LengthAwarePaginator $data): Collection
    {
        return $data->getCollection()->transform(function ($recipe) {
            return $recipe->setHidden(['description', 'user_id', 'created_at', 'updated_at']);
        });
    }
}
