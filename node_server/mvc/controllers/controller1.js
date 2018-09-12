"use strict";
let http_server = require('../../core/http_server');

module.exports = class Controller1 {
    constructor(request, response) {
        this.response = response;
        this.request = request;
        // throw new Error('Voici un message d erreur');
    }

    model() {}

    view() {
        this.model();
        let view = require('../../views/Json');

        let view_obj = new view(this.response, 200);
        http_server.log(this.request, this.response, 'Success');
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