<?php

    declare(strict_types=1);

    namespace Transiteo\LandedCost\Model\Resolver;

    use Magento\Framework\App\Config\ScopeConfigInterface;
    use Magento\Framework\GraphQl\Config\Element\Field;
    use Magento\Framework\GraphQl\Query\ResolverInterface;
    use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
    use Magento\Store\Model\ScopeInterface;

    class PriceDisplaySettings implements ResolverInterface
    {
        private const XML_PATH_PREFIX = 'transiteo_settings/price_display/';

        /** @var ScopeConfigInterface */
        private $scopeConfig;

        public function __construct(
            ScopeConfigInterface $scopeConfig
        )
        {
            $this->scopeConfig = $scopeConfig;
        }

        /**
         * @inheritdoc
         */
        public function resolve(
            Field       $field,
                        $context,
            ResolveInfo $info,
            ?array      $value = null,
            ?array      $args = null
        )
        {
            // Récupère l’ID de store si le call comporte un header Store ou un param. de contexte
            $storeId = (int)($context->getExtensionAttributes()->getStore()->getId() ?? 0);

            // Toutes les pages à retourner
            $pages = [
                'homepage',
                'category_page',
                'search_page',
                'seller_page',
                'product_page',
                'checkout_cart_page',
                'checkout_payment_page',
            ];

            $result = [];
            foreach ($pages as $page) {
                $result[$this->camelize($page)] = [
                    'includedInPrice' => $this->getFlag($page . '/included_in_price', $storeId),
                    'roundedPrice' => $this->getFlag($page . '/rounded_price', $storeId),
                ];
            }

            return $result;
        }

        /**
         * Encapsule isSetFlag et force bool
         */
        private function getFlag(string $pathSuffix, int $storeId): bool
        {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_PREFIX . $pathSuffix,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        /**
         * category_page → categoryPage
         */
        private function camelize(string $snake): string
        {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $snake))));
        }
    }
