"use strict";
let constants = require('./constantes');
let object_base = require(constants.CorePath + '/Object');
let fs = require('fs');

module.exports = class controller {
    constructor(request, response) {
        this.response = response;
        this.request = request;
        this.object = new object_base('');
        this.args = '';
        this.model_result = [];
        this.after_construct();
    }

    after_construct() {}

    model(methode, args) {
        this.args = args;
        this.format_args();
        if(fs.existsSync(constants.MvcModelsPath + '/' + this.object.getClass() + '.js')) {
            let model = require(constants.MvcModelsPath + '/' + this.object.getClass());
            let model_obj = new model();
            model_obj.object.setClass(this.object.getClass());
            if(model_obj.object.getClass() === this.object.getClass()) {
                this.model_result = model_obj.execute(methode, args);
            }
        }
    }

    view(format) {}

    format_args() {
        let args = [];
        this.args.forEach((obj, key) => {
            let expression = obj;
            args[expression.split('=')[0]] = expression.split('=')[1];
        });
        this.args = args;
    }
};