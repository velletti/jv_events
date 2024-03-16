<?php
declare(strict_types=1);

return [
    \JVelletti\JvEvents\Domain\Model\StaticCountry::class => [
        'tableName' => 'static_countries',
    ],
    \JVelletti\JvEvents\Domain\Model\FrontendUser::class => [
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