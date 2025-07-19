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

        /**
         * Retourne les pays depuis tous les codes de boutiques.
         *
         * @return array
         */
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

        /**
         * Retourne le code pays depuis le code de la boutique.
         *
         * @param string $storeCode
         *
         * @return string|null
         */
        public function getCountryByStoreCode(string $storeCode): ?string
        {
            if (str_ends_with($storeCode, '_store')) {
                return strtoupper(substr($storeCode, 0, -strlen('_store')));
            }

            return null; // Return null if the store code does not match the expected format
        }
    }
