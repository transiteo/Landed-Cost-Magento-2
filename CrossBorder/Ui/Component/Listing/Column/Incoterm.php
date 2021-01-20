<?php

namespace Transiteo\CrossBorder\Ui\Component\Listing\Column;

class Incoterm extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$this->getData('name')] === "ddp") {
                    $item[$this->getData('name')] = "Yes";
                } else {
                    $item[$this->getData('name')] = "No";
                }
            }
        }

        return $dataSource;
    }
}
