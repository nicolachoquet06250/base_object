"use strict";

module.exports = class view {
    constructor(response, http_code) {
        this.response = response;
        this.http_code = http_code;
        this._message = [];
    }

    message(message) {
        this._message = message;
    }

    display() {}
};