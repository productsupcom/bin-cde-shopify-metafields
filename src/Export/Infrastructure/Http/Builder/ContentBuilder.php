<?php

declare(strict_types=1);

namespace Productsup\BinCdeShopifyMetafields\Export\Infrastructure\Http\Builder;

class ContentBuilder
{
    public function build(array $items, $ownerId): array
    {
        $variables = [];
        foreach ($items as $arrayKey => $item) {
            $namespace = preg_match('/^([^@]*)@/', $arrayKey, $matches) ? $matches[1] : '';
            $key = preg_match('/@([^@]*)@/', $arrayKey, $matches) ? $matches[1] : '';
            $type = preg_match('/@([^@]*)$/', $arrayKey, $matches) ? $matches[1] : '';

            $variables[] = [
                'key' => $key,
                'namespace' => $namespace,
                'ownerId' => 'gid://shopify/Product/'.$ownerId,
                'type' => $type,
                'value' => $item,
            ];
        }

        return [
            'query' => 'mutation MetafieldsSet($metafields: [MetafieldsSetInput!]!) {
                metafieldsSet(metafields: $metafields) {
                    metafields {
                        key
                        namespace
                        value
                        createdAt
                        updatedAt
                    }
                    userErrors {
                        field
                        message
                        code
                    }
                }
            }',
            'operationName' => 'MetafieldsSet',
            'variables' => [
                'metafields' => $variables,
            ],
        ];
    }
}
