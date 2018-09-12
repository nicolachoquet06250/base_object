"use strict";
let http = require('./core/http_server');

http.createServer((request, response, log) => {
    try {
        let ctrl = require('./mvc/controllers/controller1');
        new ctrl(response).view().display();
        log(request, response, null);
        response.end();
    }
    catch (e) {
        log(request, response, e.message);
        process.exit();
    }
}, 1337);
console.log('Server running on url http://localhost:1337');