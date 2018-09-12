let http = require('http');

class http_server {
    createServer(callback, port) {
        http.createServer((req, resp) => {
            callback(req, resp, (req, resp) => {
                let date = new Date().toDateString() +' - ' + new Date().getHours() + ':' + new Date().getMinutes() + ':' + new Date().getSeconds();
                let url = req.url;
                let host = req.headers.host;
                let statusCode = resp.statusCode;
                console.log('['+ date + '] ' + host + ' [' + statusCode + ']: ' + url);
            });
        }).listen(port);
    }
}

module.exports = new http_server();