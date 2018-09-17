"use strict";
let fs = require("fs");

module.exports = new class constants {
    constructor() {
        this.TodayDate = new Date().toDateString();

        // Server Constants
        this.Host = 'http://localhost';
        this.ServerPort= 1337;

        // Path Constants
        this.RootPath= fs.realpathSync(__dirname + '/..');
        this.CorePath = `${this.RootPath}/core`;
        this.ViewsPath = this.RootPath + '/views';
        this.ViewsFormatPath = this.ViewsPath + '/formats';
        this.ViewsLayoutsPath = this.ViewsPath + '/layouts';
        this.MvcPath = this.RootPath + '/mvc';
        this.MvcControllersPath = this.MvcPath + '/controllers';
        this.MvcModelsPath = this.MvcPath + '/models';

        // Logs Constants
        this.LogsPath = this.RootPath + '/logs';
        this.LogFileName = this.TodayDate.replace(/\ /g, '');
        this.LogSyntax = '[{date}] {host} [{statusCode}]: {url} {message}';
        this.LogExtension = '.log';

        // Confs Constants
        this.ConfsPath = `${this.RootPath}/conf`;
        this.JsAuthorisationsPath = `${this.ConfsPath}/js_authorizations.json`;
        this.CssAuthorisationsPath = `${this.ConfsPath}/scss_authorizations.json`;
        this.SqlConfs = `${this.ConfsPath}/sql_conf.json`;

        // Statics Files Constants
        this.StaticsPath = `${this.RootPath}/statics`;
        this.StaticsControllers = ['css', 'img', 'js'];
        this.StaticsMimeTypes = JSON.parse(fs.readFileSync(`${this.ConfsPath}/static_dirs.json`));

        // Scss Constants
        this.ScssSources = `${this.StaticsPath}/scss`;
        this.ScssDestination = `${this.StaticsPath}/css`;

        // Messages Constants
        this.ServerHomeMessage = `Server running on url ${this.Host}:${this.ServerPort}`;

        // Formats supported Constants
        this.formats= JSON.parse(fs.readFileSync(`${this.ConfsPath}/formats.json`));
        this.DefaultFormat= this.formats[1];
    }
};