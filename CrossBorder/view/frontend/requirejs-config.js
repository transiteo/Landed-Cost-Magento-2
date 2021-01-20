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
