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
        this.access_rights = JSON.parse(fs.readFileSync(constants.ConfsPath + '/access_rights.json').toString());
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

    get_access_rights() {
        return this.access_rights;
    }

    get_access_right(right_name) {
        return this.access_rights[right_name] !== undefined ? this.access_rights[right_name] : null;
    }
}

module.exports = new conf();