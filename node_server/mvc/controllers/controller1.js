"use strict";

module.exports = class Controller1 {
    constructor(response) {
        this.response = response;
    }

    model() {}

    view() {
        this.model();
        let view = require('../../views/Json');
        let view_obj = new view(this.response, 200);
        view_obj.message(
            [
                {
                    'status': 200,
                    'message': 'Voici mon message'
                }
            ]
        );
        return view_obj;
    }
};