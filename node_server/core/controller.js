"use strict";
let constants = require('./constantes');
let utils = require(constants.CorePath + '/utils');
let object_base = require(constants.CorePath + '/Object');
let fs = require('fs');
let args_class = require(constants.CorePath + '/args');

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

    static method_is_in(method_name, methods, ext) {
        for(let i=0, max=methods.length; i<max; i++) {
            if(methods[i] === method_name + '_' + ext) {
                return true;
            }
        }
        return false;
    }

    model(methode, args, ext) {
        if(this.args[0] !== undefined) this.args = utils.format_args(args);
        if(fs.existsSync(constants.MvcModelsPath + '/' + this.object.getClass() + '.js')) {
            let model = require(constants.MvcModelsPath + '/' + this.object.getClass());

            let args_obj = new args_class(this.args);
            let model_obj = new model(methode, args_obj);
            let methodes = utils.get_object_methods(constants.MvcModelsPath + '/' + this.object.getClass());

            if(controller.method_is_in(methode, methodes, ext)) {
                model_obj.object.setClass(this.object.getClass());
                if (model_obj.object.getClass() === this.object.getClass()) {
                    this.model_result = model_obj.execute(ext);
                }
            }
            else {
                let Error = require(constants.ViewsFormatPath + '/Error');
                let Error_obj = new Error(this.response, 404);
                Error_obj.request(this.request);
                Error_obj.type(ext);
                Error_obj.message('method ' + this.object.getClass() + '::' + methode + '() for `' + ext + '` format not found !');
                return Error_obj;
            }
        }
    }

    view(format) {}

    count_args() {
        return Object.keys(this.args).length;
    }

    get_results_size() {
        let nb = 0;
        if(typeof this.model_result === 'object') {
            nb = Object.keys(this.model_result).length;
        }
        else {
            nb = this.model_result.length;
        }
        return nb;
    }
};