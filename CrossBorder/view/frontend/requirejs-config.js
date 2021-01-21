/*
 * @author Blackbird Agency
 * @copyright Open Software License (OSL 3.0)
 * @link <hello@bird.eu>
 */

var config = {
    paths: {
        'transiteomodal': "Transiteo_CrossBorder/js/modal"
    },
    shim: {
        'transiteomodal': {
            deps: ['jquery']
        }
    },
    map: {
        '*': {
            'transiteoduties': 'Transiteo_CrossBorder/js/duties'
        }
    }
}
