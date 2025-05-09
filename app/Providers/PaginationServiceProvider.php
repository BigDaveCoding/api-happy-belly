<?php

namespace App\Providers;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationServiceProvider
{
    public static function pagination(LengthAwarePaginator $data): array
    {
        return [
            'current_page' => $data->currentPage(),
            'results_per_page' => $data->perPage(),
            'total_results' => $data->total(),
            'next_page_url' => $data->nextPageUrl(),
            'previous_page_url' => $data->previousPageUrl(),
            'all_page_urls' => $data->getUrlRange(1, $data->lastPage()),
        ];
    }
}
