# TRANSITEO Landed cost connector for Magento 2

[![Latest Stable Version](https://img.shields.io/packagist/v/transiteo/landed-cost.svg?style=flat-square)](https://packagist.org/packages/transiteo/landed-cost)
[![License: MIT](https://img.shields.io/github/license/transiteo/Landed-Cost-Magento-2.svg?style=flat-square)](./LICENSE)

This module is the official connector between Transiteo Taxes Calculator and Magento 2. It has been made in partnership with Transiteo.

## Setup

### Get the package

**Composer Package:**

```shell
composer require transiteo/landed-cost
```

**Zip Package:**

Unzip the package in app/code/Transiteo/ContentManagerIndexerDisabler, from the root of your Magento instance.

### Install the module

Go to your Magento root directory and run the following magento command:
```shell
bin/magento setup:upgrade
```

## Documentation

### Sync Feature

All the Products and Orders will be synchronised with Transiteo in order to retrieve the Taxes. It is using message queue feature of Magento 2 that's why you must be sure that [cron job](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-cron.html) are running on your server.

- The message queue consumer transiteo.sync.product` is responsible for synchronising the products.
```shell
### To manually launch the synchronisation of products
bin/magento queue:consumers:run transiteo.sync.product
```
- The message queue consumer `transiteo.sync.order is responsible for synchronising the products.
```shell
### To manually launch the synchronisation of orders
bin/magento queue:consumers:run transiteo.sync.order
```
When a product or an order is saved, synchronisation messages will be added to the queues.

When indexing the indexer `catalog_product_category is running`, all the products are also added to the message queue in order to be synchronised.
```shell
### To manually launch the synchronisation of all the products
bin/magento indexer:reindex catalog_product_category
bin/magento queue:consumers:run transiteo.sync.order
```

### Cache Management

All the request to the Transiteo API are cached, when a product is reindexed, the cache associated with the product is deleted. The cache lifetime of the requests is 3600 seconds.
The cache type is `transiteo_taxes`.
If you want to manually refresh the request cache. You can run :
```shell
bin/magento cache:clean transiteo_taxes
```

## Support

- If you have any issue with this code, feel free to [open an issue](https://github.com/transiteo/Landed-Cost-Magento-2/issues/new).
- If you want to contribute to this project, feel free to [create a pull request](https://github.com/transiteo/Landed-Cost-Magento-2/compare).

## Contact

For further information, contact us:

- by email: hello@bird.eu
- or by form: [https://black.bird.eu/en/contacts/](https://black.bird.eu/contacts/)

## Authors

- **Bruno FACHE** - *Maintainer* - [It's me!](https://github.com/bruno-blackbird)
- **Blackbird Team** - *Contributor* - [They're awesome!](https://github.com/blackbird-agency)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

***That's all folks!***
