<?php

namespace App\Support;

final class GenreCatalog
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Science',
            'Poetry',
            'Drama',
            'Crime',
            'Detective',
            'Romance',
            'Fantasy',
            'Science Fiction',
            'Action',
            'Adventure',
            'Horror',
            'Literary Fiction',
            'Comedy',
            'Historical Fiction',
        ];
    }
}
