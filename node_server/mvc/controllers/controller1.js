"use strict";

module.exports = class Controller1 {
    constructor(response) {
        this.response = response;
        throw new Error('Voici un message d erreur');
    }

    model() {}

    view() {
        this.model();
        let view = require('../../views/Json');
        let view_obj = new view(this.response, 400);
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