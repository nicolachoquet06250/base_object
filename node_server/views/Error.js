"use strict";
let http_server = require('../core/http_server');

let view = require('../core/view');

module.exports = class Error extends view {
    http_code_and_type_parameter() {
        this.response.writeHead(
            this.http_code, this._type === 'json' ?
                {'Content-Type': 'application/json'}
                : {'Content-Type': 'text/html'}
        );
    }

    request(request) {
        this._request = request;
    }

    type(type) {
        this._type = type;
        this.http_code_and_type_parameter();
    }

    html_error() {
        return '<!DOCTYPE html>' +
            '<html>' +
            '   <head>' +
            '       <meta charset="utf-8">' +
            '       <title>Erreur ' + this.http_code + '</title>' +
            '   </head>' +
            '   <body>' +
            '       <h1>Erreur ' + this.http_code + '</h1>' +
            '       <p>' + this._message + '</p>' +
            '   </body>' +
            '</html>';
    }

    json_error() {
        return JSON.stringify({
                type: 'error',
                http_code: this.http_code,
                message: this._message
            });
    }

    display() {
        this.response.write(this._type === 'html' ? this.html_error() : this.json_error());
        http_server.log(this._request, this.response, this._message);
    }
};