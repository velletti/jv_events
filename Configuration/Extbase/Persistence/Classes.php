<?php
declare(strict_types=1);

return [
    \JVE\JvEvents\Domain\Model\StaticCountry::class => [
        'tableName' => 'static_countries',
    ],
    \JVE\JvEvents\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
        'properties' => [
            'deleted' => [
                'fieldName' => 'deleted'
            ],
            'disable' => [
                'fieldName' => 'disable'
            ],
            'crdate' => [
                'fieldName' => 'crdate'
            ],
        ],
    ],
];