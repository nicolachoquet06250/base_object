"use strict";
let view = require('../core/view');

module.exports = class Html extends view {
    after_construct() {
        this._message = '';
    }

    http_code_and_type_parameter() {
        this.response.writeHead(this.http_code, {'Content-Type': 'text/html'});
    }

    display() {
        this.response.write(this._message);
    }
};