let http = require('http');
let fs = require('fs');

class http_server {
    createServer(callback, port) {
        http.createServer((req, resp) => {
            callback(req, resp, (req, resp, message = null) => {
                let date_file = new Date().toDateString();
                let date = date_file + ' - ' + new Date().getHours() + ':' + new Date().getMinutes() + ':' + new Date().getSeconds();
                let url = req.url;
                let host = req.headers.host;
                let statusCode = resp.statusCode;
                message = message !== null ? '- ' + message : '';
                let log = '['+ date + '] ' + host + ' [' + statusCode + ']: ' + url + ' ' + message;

                if(!fs.existsSync('./logs')) {
                    fs.mkdir('./logs');
                }

                if(fs.existsSync('./logs/' + date_file.replace(/\ /g, '') + '.log')) {
                    fs.appendFile('./logs/' + date_file.replace(/\ /g, '') + '.log', log + "\n");
                }
                else {
                    fs.writeFile('./logs/' + date_file.replace(/\ /g, '') + '.log', log + "\n");
                }
                console.log(log);
            });
        }).listen(port);
    }
}

module.exports = new http_server();