/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

var config = {
    paths: {
        'transiteomodal': "Transiteo_DutiesTaxesCalculator/js/modal"
    },
    shim: {
        'transiteomodal': {
            deps: ['jquery']
        }
    },
    map: {
        '*': {
            'transiteoduties': 'Transiteo_DutiesTaxesCalculator/js/duties'
        }
    }
}
