"use strict";
let view = require('../../views/Json');
let Error = require('../../views/Error');

module.exports = class Controller1 {
    constructor(request, response) {
        this.response = response;
        this.request = request;
    }

    model() {}

    view() {
        this.model();
        let error = false;

        let view_obj = new view(this.response, 200);

        if(error) {
            let Error_obj = new Error(this.response, 500);
            Error_obj.request(this.request);
            Error_obj.message('Erreur de serveur');
            return Error_obj;
        }

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
};