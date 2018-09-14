"use strict";
let constants = require('./constantes');
let object_base = require(constants.CorePath + '/Object');

module.exports = class model {
    constructor() {
        this.object = new object_base('');
        this.results = [];
        this.after_construct();
    }

    after_construct() {}

    execute(methode, args) {
        eval('this.' + methode + '(args);');
    }

    get_results() {
        return this.results;
    }
};