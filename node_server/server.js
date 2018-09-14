"use strict";
let http_server = require('./core/http_server');
let http = new http_server();

let default_extension = 'json';

http.createServer((request, response, log) => {
    try {
        let ctrl = require('./mvc/controllers/controller1');
        let view = new ctrl(request, response).view();

        if(typeof view === "object"
            && view instanceof require('./views/Error')) {
            if(request.url.indexOf('.', 0)) {
                let url = request.url;
                let extention = url.split('.')[1];
                switch (extention) {
                    case 'html':
                    case 'json':
                        view.type(extention);
                        break;
                    default:
                        view.type(default_extension);
                        break;
                }
            }
        }
        view.display();
        log(request, response, null);
        response.end();
    }
    catch (e) {
        log(request, response, e.message);
        process.exit();
    }
}, 1337);
console.log('Server running on url http://localhost:1337');