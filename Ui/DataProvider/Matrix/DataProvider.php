<?php

    namespace Transiteo\LandedCost\Ui\DataProvider\Matrix;

    use Magento\Catalog\Model\CategoryFactory;
    use Magento\Catalog\Model\ResourceModel\Category;
    use Magento\Ui\DataProvider\AbstractDataProvider;
    use Transiteo\LandedCost\Model\ResourceModel\CategoryMatrix\CollectionFactory;

    class DataProvider extends AbstractDataProvider
    {
        private static array $CACHE_CATEGORY_NAME = [];

        public function __construct(
            CollectionFactory $collectionFactory,
            string $name,
            string $primaryFieldName,
            string $requestFieldName,
            private CategoryFactory $categoryFactory,
            private Category $categoryResource,
            array $meta = [],
            array $data = []
        ) {
            $this->collection = $collectionFactory->create();
            parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        }

        public function getData()
        {
            $items = $this->collection->getItems();
            $data = [];

            foreach ($items as $item) {
                $row = $item->getData();

                // Exemple : conversion restriction
                $row['restricted'] = !empty($row['restricted']) ? 'Allowed' : 'Restricted';

                // récupération de la catégorie
                if (!isset(static::$CACHE_CATEGORY_NAME[$row['category_id']])) {
                    $category = $this->categoryFactory->create();
                    $this->categoryResource->load($category, $row['category_id']);

                    if ($category->getId()) {
                        static::$CACHE_CATEGORY_NAME[$row['category_id']] = $category->getName();
                    } else {
                        static::$CACHE_CATEGORY_NAME[$row['category_id']] = ' - ';
                    }
                }

                $row['category_name'] = static::$CACHE_CATEGORY_NAME[$row['category_id']];
                $row['special_taxes'] = !empty($row['special_taxes']) ? $row['special_taxes'] : '[]';
                $data[$item->getId()] = $row;
            }

            return ['items' => array_values($data), 'totalRecords' => $this->collection->getSize()];
        }
    }
