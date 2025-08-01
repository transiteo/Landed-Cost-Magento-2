<?php

    namespace Transiteo\LandedCost\Model;

    use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
    use Magento\Store\Model\StoreManagerInterface;

    /**
     * Extracts languages from store codes.
     * Format expected: 'en_store', 'fr_store', etc.
     */
    class CountryExtractor
    {
        public function __construct(
            protected StoreManagerInterface $storeManager,
            private CountryCollectionFactory $countryCollectionFactory
        ) {
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
            
            $countryCollection = $this->countryCollectionFactory->create();
            $countryCollection->addFieldToSelect(['country_id', 'iso2_code', 'iso3_code']);

            $iso2ToIso3 = [];
            foreach ($countryCollection as $country) {
                $iso2ToIso3[strtoupper($country->getData('iso2_code'))] = $country->getData('iso3_code');
            }

            $result = [];
            foreach (array_keys($languages) as $iso2) {
                if (isset($iso2ToIso3[$iso2])) {
                    $result[] = $iso2ToIso3[$iso2];
                }
            }

            return array_unique($result);
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
