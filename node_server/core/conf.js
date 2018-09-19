"use strict";
let constants = require('./constantes');
let fs = require('fs');

class conf {
    constructor() {
        this.formats = JSON.parse(fs.readFileSync(constants.ConfsPath + '/formats.json').toString());
        this.js_authorizations = JSON.parse(fs.readFileSync(constants.JsAuthorisationsPath).toString());
        this.scss_authorizations = JSON.parse(fs.readFileSync(constants.CssAuthorisationsPath).toString());
        this.router = JSON.parse(fs.readFileSync(constants.ConfsPath + '/router.json').toString());
        this.sql = JSON.parse(fs.readFileSync(constants.ConfsPath + '/sql_conf.json').toString());
        this.static_dirs = JSON.parse(fs.readFileSync(constants.ConfsPath + '/static_dirs.json').toString());
    }

    get_formats() {
        return this.formats;
    }

    get_authorizations(type) {
        return eval(`this.${type}_authorizations`);
    }

    get_sql() {
        return this.sql;
    }

    get_router() {
        return this.router;
    }

    get_statics_dirs() {
        return this.static_dirs;
    }
}

module.exports = new conf();