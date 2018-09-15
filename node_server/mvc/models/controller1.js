"use strict";
let constants = require('../../core/constantes');
let model = require(constants.CorePath + '/model');

module.exports = class controller1 extends model {
    /** @method */
    method() {
        let args = this.args;
        args['test'] = 'Je suis dans la méthode `method` du model `controller1` !';
        return args;
    }

    /** @method */
    method2() {
        let args = this.args;
        args['test'] = 'Je suis dans la méthode `method` du model `controller1` !';
        return args;
    }
};