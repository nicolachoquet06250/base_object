"use strict";
let constants = require('./constantes');
let utils = require(constants.CorePath + '/utils');

module.exports = class uri {
    constructor(url) {
        this.url = url;
        this.router = require(constants.CorePath + '/router');
        this.controller = '';
        this.method = '';
        this.format = '';
        this.args = [];
        this.parse();
    }

    parse() {
        let url_parsed = this.url.split('/');
        let args = [];

        url_parsed.forEach((obj, key) => {
            if(obj.indexOf('=') !== -1) {
                let arg = obj.split('=');
                args[args.length] = arg.join('=');
                this.args[arg[0]] = arg[1];
            }
        });

        let url_probably_route = url_parsed.join('/').replace(args.join('/'), '');
        if(this.router.has_route(url_probably_route)) {
            let route = this.router.get_route(url_probably_route);
            this.controller = route['controller'];
            this.method = route['method'];
            this.format = route['format'];
            let args = route['args'];
            Object.keys(args).forEach((key) => {
                this.args[key] = args[key];
            });
        }
        else {
            this.controller = url_probably_route.split('/')[1];
            let method = url_probably_route.split('/')[2];
            let _method = method.split('.');
            this.format = _method.length > 1 ? _method[1] : constants.DefaultFormat;
            this.method = _method.length > 1 ? _method[0] : method;
        }
    }

    get_controller() {
        return this.controller;
    }

    get_method() {
        return this.method;
    }

    get_args() {
        return this.args;
    }

    get_format() {
        return this.format;
    }
};