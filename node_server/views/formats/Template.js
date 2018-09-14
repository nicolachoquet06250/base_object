"use strict";
let constants = require('../../core/constantes');
let view = require(constants.CorePath + '/view');
let fs = require('fs');
let utils = require(constants.CorePath + '/utils');
let object_base = require(constants.CorePath + '/Object');

module.exports = class Template extends view {
    after_construct() {
        this._vars = [];
        this._path = '';
        this.object.setClass('Template');
    }

    http_code_and_type_parameter() {
        this.response.writeHead(this.http_code, {'Content-Type': 'text/html'});
    }

    Path(path) {
        this._path = path + '.html';
    }

    Vars(vars) {
        this._vars = vars;
    }

    append(_var, _value) {
        this._vars[_var] = _value;
    }

    display(request) {
        if(this._path && fs.existsSync(this._path)) {
            this._message = fs.readFileSync(this._path);
            this._message = utils.Print(this._message, this._vars);
        }
        else {
            let Error = require(constants.ViewsFormatPath + '/Error');
            let Error_obj = new Error(this.response, 404);
            Error_obj.message('La page demandée n\'existe pas !');
            Error_obj.type('html');
            Error_obj.request(request);
            Error_obj.display();
        }
        this.response.write(this._message);
    }
};