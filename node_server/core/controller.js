"use strict";
let constants = require('./constantes');
let utils = require(constants.CorePath + '/utils');
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

    model(methode, args, ext) {
        this.args = args;
        this.format_args();
        if(fs.existsSync(constants.MvcModelsPath + '/' + this.object.getClass() + '.js')) {
            let model = require(constants.MvcModelsPath + '/' + this.object.getClass());
            let model_obj = new model(methode, this.args);

            let method_is_in_array = false;
            let methodes = utils.get_object_methods(constants.MvcModelsPath + '/' + this.object.getClass());

            for(let i=0, max=methodes.length; i<max; i++) {
                if(methodes[i] === methode) {
                    method_is_in_array = true;
                    break;
                }
            }

            if(method_is_in_array) {
                model_obj.object.setClass(this.object.getClass());
                if (model_obj.object.getClass() === this.object.getClass()) {
                    this.model_result = model_obj.execute();
                }
            }
            else {
                let Error = require(constants.ViewsFormatPath + '/Error');
                let Error_obj = new Error(this.response, 404);
                Error_obj.request(this.request);
                Error_obj.type(ext);
                Error_obj.message('method ' + this.object.getClass() + '::' + methode + '() not found !');
                return Error_obj;
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