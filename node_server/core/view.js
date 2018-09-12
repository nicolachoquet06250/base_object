"use strict";

module.exports = class view {
    constructor(response, http_code) {
        this.response = response;
        this.http_code = http_code;
        this._message = [];
        this.http_code_and_type_parameter();
        this.after_construct();
    }

    after_construct() {}

    http_code_and_type_parameter() {}

    get_response() {
        return this.response;
    }

    message(message) {
        this._message = message;
    }

    display() {}
};