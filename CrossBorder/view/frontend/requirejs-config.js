var config = {
    paths: {
        'transiteomodal': "Transiteo_Taxes/js/modal"
    },
    shim: {
        'transiteomodal': {
            deps: ['jquery']
        }
    },
    map: {
        '*': {
            'transiteoduties': 'Transiteo_Taxes/js/duties'
        }
    }
}
