"use strict";
let constants = require('./constantes');
let object_base = require(constants.CorePath + '/Object');

module.exports = class model {
    constructor(methode, args) {
        this.object = new object_base('');
        this.results = [];
        this.methode = methode;
        this.args = args;
        this.after_construct();
    }

    after_construct() {}

    execute(format) {
        this.results = eval('this.' + this.methode + '_' + format + '();');
        return this.results;
    }

    get_results() {
        return this.results;
    }
};