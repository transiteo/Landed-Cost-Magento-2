<?php
    namespace Transiteo\LandedCost\Model\Resolver;

    use Magento\Framework\GraphQl\Config\Element\Field;
    use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
    use Magento\Framework\GraphQl\Query\ResolverInterface;
    use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteriaBuilder;
    use Magento\Framework\App\ResourceConnection;

    class CategoryTaxDataResolver implements ResolverInterface
    {
        private $connection;

        public function __construct(ResourceConnection $resource)
        {
            $this->connection = $resource->getConnection();
        }

        /** @inheritdoc */
        public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
        {
            $select = $this->connection->select()->from('transiteo_category_matrix');
            $rows   = $this->connection->fetchAll($select);
            return array_map(function ($row) {
                return [
                    'category_id'   => (int)$row['category_id'],
                    'iso'           => $row['country_iso'],
                    'restricted'    => (bool)$row['restricted'],
                    'duty'          => (float)$row['duty'],
                    'local_tax'     => (float)$row['local_tax'],
                    'sales_tax'     => (float)$row['sales_tax'],
                    'special_taxes' => !empty($row['special_taxes']) ? json_decode($row['special_taxes'], true) : [],
                    'eco_tax'       => (float)$row['eco_tax'],
                ];
            }, $rows);
        }
    }
