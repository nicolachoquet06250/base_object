"use strict";
let view = require('../core/view');

module.exports = class Json extends view {
    display() {
        this.response.writeHead(this.http_code, {'Content-Type': 'application/json'});
        if(typeof this._message !== 'string') {
            this._message = JSON.stringify(this._message);
        }
        this.response.write(this._message);
    }
};