"use strict";
let http = require('./core/http_server');

http.createServer((request, response, log) => {
    let ctrl = require('./mvc/controllers/controller1');
    new ctrl(response).view().display();
    log(request, response);
    response.end();
}, 1337);
console.log('Server running on url http://localhost:1337');