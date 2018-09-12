let http = require('http');
let fs = require('fs');
let cli_color = require("cli-color");

class http_server {
    static log(req, resp, message = null) {
        let date_file = new Date().toDateString();
        let date = date_file + ' - ' + (new Date().getHours() < 10 ? '0' + new Date().getHours() : new Date().getHours()) + ':' + (new Date().getMinutes() < 10 ? '0' + new Date().getMinutes() : new Date().getMinutes()) + ':' + (new Date().getSeconds() < 10 ? '0' + new Date().getSeconds() : new Date().getSeconds());
        let url = req.url;
        let host = req.headers.host;
        let statusCode = resp.statusCode;
        message = message !== null ? '- ' + message : '';
        let statusCode_s = '';
        let message_s = message;
        switch (statusCode) {
            case 404:
            case 400:
                if(message !== null) {
                    message_s = cli_color.yellow(message_s);
                }
                statusCode_s = cli_color.red(statusCode);
                break;
            case 500:
                if(message !== null) {
                    message_s = cli_color.red.bold(message_s);
                }
                statusCode_s = cli_color.red(statusCode);
                break;
            case 200:
            default:
                if(message !== null) {
                    message_s = cli_color.green(message_s);
                }
                statusCode_s = cli_color.green(statusCode);
                break;
        }
        let log_in_file = '['+ date + '] ' + host + ' [' + statusCode + ']: ' + url + ' ' + message;
        let log = cli_color.white('['+ date + '] ' + host + ' [') + statusCode_s + cli_color.white(']: ' + url) + ' ' + message_s;

        if(!fs.existsSync('./logs')) {
            fs.mkdir('./logs');
        }

        if(!fs.existsSync('./logs/' + date_file.replace(/\ /g, '') + '.log')) {
            fs.writeFile('./logs/' + date_file.replace(/\ /g, '') + '.log', log_in_file + "\n");
        }
        else {
            fs.appendFile('./logs/' + date_file.replace(/\ /g, '') + '.log', log_in_file + "\n");
        }
        console.log(log);
    }

    createServer(callback, port) {
        http.createServer((req, resp) => {
            callback(req, resp, http_server.log);
        }).listen(port);
    }
}

module.exports = http_server;