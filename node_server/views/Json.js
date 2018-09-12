"use strict";
let view = require('../core/view');

module.exports = class Json extends view {
    http_code_and_type_parameter() {
        this.response.writeHead(this.http_code, {'Content-Type': 'application/json'});
    }

    display() {
        if(typeof this._message !== 'string') {
            this._message = JSON.stringify(this._message);
        }
        this.response.write(this._message);
    }
};