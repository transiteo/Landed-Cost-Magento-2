<?php

    namespace Transiteo\LandedCost\ViewModel;

    use Magento\Framework\UrlInterface;
    use Magento\Framework\View\Element\Block\ArgumentInterface;
    use Transiteo\LandedCost\Model\CountryExtractor;

    class MatrixData implements ArgumentInterface
    {
        public function __construct(
            private CountryExtractor $countryExtractor,
            private UrlInterface $urlBuilder,
        ) {}

        /**
         * Récupère les pays et leurs URLs de synchronisation.
         *
         * @return array
         */
        public function getCountriesUrl(): array
        {
            $countries = [];

            foreach ($this->countryExtractor->getCountries() as $country) {
                $countries[] = [
                    'iso_code' => $country,
                    'url' => $this->urlBuilder->getUrl(
                        'transiteo/matrix/sync',
                        ['country' => $country]
                    ),
                ];
            }

            return $countries;
        }
    }
