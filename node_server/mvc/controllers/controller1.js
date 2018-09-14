"use strict";

module.exports = class Controller1 {
    constructor(request, response) {
        this.response = response;
        this.request = request;
    }

    model() {}

    view() {
        this.model();
        let view = require('../../views/Json');
        let Error = require('../../views/Error');

        let error = true;

        if(error) {
            let Error_obj = new Error(this.response, 500);
            Error_obj.request(this.request);
            Error_obj.message('Erreur de serveur');
            return Error_obj;
        }
        else {
            let view_obj = new view(this.response, 200);
            view_obj.message(
                [
                    {
                        'status': 200,
                        'message': 'Success'
                    }
                ]
            );
            return view_obj;
        }
    }
};