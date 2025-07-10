<?php

    namespace Transiteo\LandedCost\Model;

    use Magento\Store\Model\StoreManagerInterface;

    /**
     * Extracts languages from store codes.
     * Format expected: 'en_store', 'fr_store', etc.
     */
    class CountryExtractor
    {
        protected StoreManagerInterface $storeManager;

        public function __construct(StoreManagerInterface $storeManager)
        {
            $this->storeManager = $storeManager;
        }

        public function getCountries(): array
        {
            $stores = $this->storeManager->getStores();
            $languages = [];

            foreach ($stores as $store) {
                $code = $store->getCode(); // e.g. 'en_store'
                if (str_ends_with($code, '_store')) {
                    $lang = strtoupper(substr($code, 0, -strlen('_store'))); // 'EN'
                    $languages[$lang] = true;
                }
            }

            return array_keys($languages);
        }
    }
