"use strict";

module.exports = class Html {
    constructor(response, http_code: number) {
        this.response = response;
        this.http_code = http_code;
        this._message = '';
    }

    message(message: string) {
        this._message = message;
    }

    display() {
        this.response.writeHead(this.http_code, {'Content-Type': 'text/html'});
        this.response.write(this._message);
    }
};