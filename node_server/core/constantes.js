"use strict";
let fs = require("fs");

module.exports = new class constants {
    constructor() {
        this.TodayDate = new Date().toDateString();

        this.Host = 'http://localhost';
        this.ServerPort= 1337;

        this.formats= ['html', 'json'];
        this.DefaultFormat= this.formats[1];

        this.RootPath= fs.realpathSync(__dirname + '/..');
        this.CorePath = this.RootPath + '/core';
        this.ViewsPath = this.RootPath + '/views';
        this.ViewsFormatPath = this.ViewsPath + '/formats';
        this.ViewsLayoutsPath = this.ViewsPath + '/layouts';
        this.MvcPath = this.RootPath + '/mvc';
        this.MvcControllersPath = this.MvcPath + '/controllers';
        this.MvcModelsPath = this.MvcPath + '/models';

        this.LogsPath = this.RootPath + '/logs';
        this.LogFileName = this.TodayDate.replace(/\ /g, '');
        this.LogSyntax = '[{date}] {host} [{statusCode}]: {url} {message}';
        this.LogExtension = '.log';
        this.StaticsPath = this.RootPath + '/statics';

        this.ServerHomeMessage = 'Server running on url ' + this.Host + ':' + this.ServerPort;
    }
};