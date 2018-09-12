"use strict";
let view = require('../core/view');

module.exports = class Html extends view {
    constructor(response, http_code: number) {
        super(response, http_code);
        this._message = '';
    }

    display() {
        this.response.writeHead(this.http_code, {'Content-Type': 'text/html'});
        this.response.write(this._message);
    }
};