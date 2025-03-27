<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;

class RecipeApiServiceProvider
{
    static public function pagination(LengthAwarePaginator $data): array
    {
        return [
            'current_page' => $data->currentPage(),
            'total_recipes' => $data->total(),
            'next_page_url' => $data->nextPageUrl(),
            'previous_page_url' => $data->previousPageUrl(),
            'all_page_urls' => $data->getUrlRange(1, $data->lastPage()),
        ];
    }
}
