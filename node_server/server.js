"use strict";
let constants = require('./core/constantes');
let utils = require(constants.CorePath + '/utils');
let args_class = require(constants.CorePath + '/args');
let fs = require('fs');
let http_server = require(constants.CorePath + '/http_server');
let http = new http_server();
let Error = require(constants.ViewsFormatPath + '/Error');
let readline = require(constants.CorePath + '/readline');
const { exec } = require('child_process');

http.createServer((request, response, log) => {
    let url = request.url;

    let ctrl_name = url.split('/')[1];
    let ext_name;

    if(ctrl_name === 'static') {
            let directory = url.split('/')[2];
            let file = url.split('/')[3];
            let _url = url.split('/');

            delete _url[0];
            delete _url[1];
            delete _url[2];
            delete _url[3];

            let args = [];
            _url.forEach(obj => {
                if(obj !== undefined && obj !== '') {
                    args[args.length] = obj;
                }
            });
            url = url.replace('/' + args.join('/'), '');
            url = url.split('/');

            delete url[0];
            delete url[1];
            let  tmp_url = [];
            for(let i = 0, max = url.length; i<max; i++) {
                if(url[i] !== undefined && url[i] !== '') {
                    tmp_url[tmp_url.length] = url[i];
                }
            }
            url = tmp_url.join('/');
            if(directory === 'css') {
                args = new args_class(utils.format_args(args));

                if(args.get('a')) {
                    let authorisations = fs.readFileSync(constants.CssAuthorisationsPath).toString();
                    authorisations = new args_class(JSON.parse(authorisations));
                    let files;
                    if(files = authorisations.get(args.get('a'))) {
                        let concat = '';
                        files.forEach(file => {
                            if(fs.existsSync(`${constants.ScssSources}/${file}.scss`)) {
                                concat += fs.readFileSync(`${constants.ScssSources}/${file}.scss`).toString() + "\n";
                            }
                        });
                        fs.writeFile(`${constants.ScssSources}/uncompile_${args.get('a')}.scss`, concat);
                        let cmd = 'node-sass --output-style uncompressed ' + constants.ScssSources + '/uncompile_' + args.get('a') + '.scss > ' + constants.ScssDestination + '/compile_' + args.get('a') + '.css';
                        exec(cmd, () => {
                            exec('cat ./statics/css/compile_toto.css', (err, out) => {
                                if(out !== '') {
                                    response.write(out);
                                    response.end();
                                }
                            });
                        });
                    }

                    file = 'compile_' + args.get('a') + '.css';
                }
                else {
                    let path = `${constants.StaticsPath}/${directory}/${file}`;
                    if(fs.existsSync(path)) {
                        response.writeHead(200, {'Content-Type': 'text/css'});
                        fs.readFile(path, null, function(err, data) {
                            response.write(data.toString());
                            response.end();
                        });
                    }
                    else {
                        response.writeHead(404, {'Content-Type': 'text/css'});
                        response.write('');
                        response.end();
                    }
                }

                let path = `${constants.StaticsPath}/${directory}/${file}`;
                if(fs.existsSync(path)) {
                    response.writeHead(200, {'Content-Type': 'text/css'});
                        fs.readFile(constants.ScssDestination + '/' + file, null, function(err, data) {
                    });
                }
                else {
                    response.writeHead(404, {'Content-Type': 'text/css'});
                    response.write('');
                    response.end();
                }
            }
            else if (utils.in(directory, constants.StaticsControllers) && directory !== 'css') {
                if(directory === 'js') {

                    let authorisations = fs.readFileSync(constants.JsAuthorisationsPath).toString();
                    authorisations = JSON.parse(authorisations);

                }
                if(fs.existsSync(`${constants.StaticsPath}/${directory}/${url}`)) {
                    if(constants.StaticsMimeTypes[directory] !== undefined) {
                        let mime;
                        if(constants.StaticsMimeTypes[directory].split('/')[1] !== '') {
                            mime = constants.StaticsMimeTypes[directory];
                        }
                        else {
                            mime = constants.StaticsMimeTypes[directory] + directory;
                        }
                        response.writeHead(200, {'Content-Type': mime});
                    }
                    response.write(fs.readFileSync(`${constants.StaticsPath}/${directory}/${url}`));
                }
                else {
                    if(constants.StaticsMimeTypes[directory] !== undefined) {
                        let Error_obj = new Error(response, 404);
                        Error_obj.request(request);
                        Error_obj.type('html');
                        Error_obj.message('`' + url + '` file not found !');
                        Error_obj.display(request);
                    }
                }
                response.end();
            }
        }
    else {
        let router = JSON.parse(fs.readFileSync(`${constants.ConfsPath}/router.json`));
        let router_keys = Object.keys(router);
        if(url === '/') {
            if(utils.in(url, router_keys)) {
                console.log(router[url]);
            }
        }
        else {
            let methode_name = url.split('/')[2];
            let methode_name_split = methode_name.split('.');
            let methode = methode_name_split.length > 1 ? methode_name_split[0] : methode_name;
            ext_name = methode_name_split.length > 1 ? methode_name_split[1] : constants.DefaultFormat;

            url = url.split('/');

            delete url[0];
            delete url[1];
            delete url[2];

            let args = [];
            url.forEach(obj => {
                args[args.length] = obj;
            });

            let tmp_args = args.join('/');

            let route_url = url.replace('/' + tmp_args, '');
            console.log(route_url);

            if (fs.existsSync(constants.MvcControllersPath + '/' + ctrl_name + '.js')) {
                let ctrl = require(constants.MvcControllersPath + '/' + ctrl_name);
                let ctrl_obj = new ctrl(request, response);
                ctrl_obj.object.setClass(ctrl_name);
                let model = ctrl_obj.model(methode, args, ext_name);
                if (typeof model === 'object' && model instanceof Error) {
                    model.display(request);
                }
                else {
                    let view = ctrl_obj.view(ext_name);

                    if (typeof view === "object" && view instanceof Error) {
                        if (request.url.indexOf('.', 0)) {
                            view.type(ext_name);
                        }
                    }
                    else {
                        log(request, response, null);
                    }
                    view.display(request);
                }
            }
            else {
                let Error_obj = new Error(response, 404);
                Error_obj.request(request);
                Error_obj.type(ext_name);
                Error_obj.message('controller ' + ctrl_name + ' not found !');
                Error_obj.display(request);
            }
        }
        response.end();
    }
}, constants.ServerPort);

console.log(constants.ServerHomeMessage);