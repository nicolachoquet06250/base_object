"use strict";
let constants = require('./core/constantes');
let utils = require(constants.CorePath + '/utils');
let http_server = require(constants.CorePath + '/http_server');
let http = new http_server();
let Error = require(constants.ViewsFormatPath + '/Error');

http.createServer((request, response, log) => {
    // try {
        let url = request.url;
        let ctrl_name = url.split('/')[1];
        let methode_name = url.split('/')[2];
        let methode = methode_name.split('.')[0];
        let ext_name = methode_name.split('.').length > 1 ? methode_name.split('.')[1] : constants.DefaultFormat;

        url = url.split('/');

        delete url[0];
        delete url[1];
        delete url[2];

        let args = [];
        url.forEach((obj, key) => {
            args[args.length] = obj;
        });

        let ctrl = require(constants.MvcControllersPath + '/' + ctrl_name);
        let ctrl_obj = new ctrl(request, response);
        ctrl_obj.object.setClass(ctrl_name);
        ctrl_obj.model(methode, args);
        let view = ctrl_obj.view(ext_name);

        if(typeof view === "object" && view instanceof Error) {
            if(request.url.indexOf('.', 0)) {
                view.type(ext_name);
            }
        }
        else {
            log(request, response, null);
        }
        view.display(request);
        response.end();
    // }
    // catch (e) {
    //     log(request, response, e.toString());
    //     process.exit();
    // }
}, constants.ServerPort);

console.log('Server running on url ' + constants.Host + ':' + constants.ServerPort);