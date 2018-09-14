"use strict";
let constants = require('../../core/constantes');
let model = require(constants.CorePath + '/model');

module.exports = class controller1 extends model {
    method(args) {
        console.log('test');
        console.log(args);
    }
};