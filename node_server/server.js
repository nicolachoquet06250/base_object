"use strict";
let constants = require('./core/constantes');
let utils = require(constants.CorePath + '/utils');
let args_class = require(constants.CorePath + '/args');
let fs = require('fs');
let http_server = require(constants.CorePath + '/http_server');
let http = new http_server();
let Error = require(constants.ViewsFormatPath + '/Error');
let uri = require(constants.CorePath + '/uri');
let confs = require(constants.CorePath + '/conf');
const {exec} = require('child_process');

http.createServer((request, response, log) => {
    let url = request.url;

    let uri_obj = new uri(url);
    let controller = uri_obj.get_controller();
    let method = uri_obj.get_method();
    let format = uri_obj.get_format();
    let args = uri_obj.get_args();

    if(controller === 'static') {
        args = new args_class(args);
        let file = args.get('f');
        let path;
        if (method === 'css') {
            if (args.get('a')) {
                let authorisations = confs.get_authorizations('scss');
                authorisations = new args_class(authorisations);
                let files;
                if ((files = authorisations.get(args.get('a'))) !== false) {
                    let concat = '';
                    files.forEach(file => {
                        if (fs.existsSync(`${constants.ScssSources}/${file}.scss`)) {
                            concat += fs.readFileSync(`${constants.ScssSources}/${file}.scss`).toString() + "\n";
                        }
                    });
                    fs.writeFile(`${constants.ScssSources}/uncompile_${args.get('a')}.scss`, concat);
                    let cmd = 'node-sass --output-style uncompressed ' + constants.ScssSources + '/uncompile_' + args.get('a') + '.scss > ' + constants.ScssDestination + '/compile_' + args.get('a') + '.css';
                    exec(constants.SassCompilationCommand(args.get('a')), () => {
                        exec(constants.SassCompilationCommand(args.get('a'), true), (err, out) => {
                            if (out !== '') {
                                response.write(out);
                                response.end();
                            }
                        });
                    });
                }
                file = 'compile_' + args.get('a') + '.css';
            } else {
                path = `${constants.StaticsPath}/${method}/${file}`;
                if (fs.existsSync(path)) {
                    response.writeHead(200, {'Content-Type': constants.StaticsMimeTypes['css']});
                    fs.readFile(path, null, function (err, data) {
                        response.write(data.toString());
                    });
                } else {
                    response.writeHead(404, {'Content-Type': constants.StaticsMimeTypes['css']});
                    response.write('');
                }
                response.end();
            }

            path = `${constants.StaticsPath}/${method}/${file}`;
            if (fs.existsSync(path)) {
                response.writeHead(200, {'Content-Type': constants.StaticsMimeTypes['css']});
                    fs.readFile(constants.ScssDestination + '/' + file, null, function (err, data) {
                });
            } else {
                response.writeHead(404, {'Content-Type': constants.StaticsMimeTypes['css']});
                response.write('');
                response.end();
            }
        }
        else if (utils.in(method, constants.StaticsControllers) && method !== 'css') {
            if (method === 'js') {
                if (args.get('a')) {
                    let authorisations = confs.get_authorizations('js');
                    authorisations = new args_class(authorisations);
                    let files;
                    if ((files = authorisations.get(args.get('a'))) !== false) {
                        let concat = '';
                        files.forEach(file => {
                            if (fs.existsSync(`${constants.StaticsPath}/js/${file}.js`)) {
                                concat += fs.readFileSync(`${constants.StaticsPath}/js/${file}.js`).toString() + "\n";
                            }
                        });
                        response.write(concat);
                    }
                    response.end();
                    return;
                } else {
                    path = `${constants.StaticsPath}/${method}/${file}`;
                    if (fs.existsSync(path)) {
                        response.writeHead(200, {'Content-Type': constants.StaticsMimeTypes['js']});
                        fs.readFile(path, null, function (err, data) {
                            response.write(data.toString());
                        });
                    } else {
                        response.writeHead(404, {'Content-Type': constants.StaticsMimeTypes['js']});
                        response.write('');
                    }
                    response.end();
                    return;
                }
            }
            if (fs.existsSync(`${constants.StaticsPath}/${method}/${file}`)) {
                if (constants.StaticsMimeTypes[method] !== undefined) {
                    let mime;
                    if (constants.StaticsMimeTypes[method].split('/')[1] !== '') {
                        mime = constants.StaticsMimeTypes[method];
                    } else {
                        mime = constants.StaticsMimeTypes[method] + method;
                    }
                    response.writeHead(200, {'Content-Type': mime});
                }
                response.write(fs.readFileSync(`${constants.StaticsPath}/${method}/${file}`));
            } else {
                if (constants.StaticsMimeTypes[method] !== undefined) {
                    let Error_obj = new Error(response, 404);
                    Error_obj.request(request);
                    Error_obj.type('html');
                    Error_obj.message(constants.FileNotFoundMessage(file));
                    Error_obj.display(request);
                }
            }
            response.end();
        }
    }
    else {
        if (fs.existsSync(constants.MvcControllersPath + '/' + controller + '.js')) {
            let ctrl = require(constants.MvcControllersPath + '/' + controller);
            let ctrl_obj = new ctrl(request, response);
            ctrl_obj.object.setClass(controller);
            let model = ctrl_obj.model(method, args, format);
            if (typeof model === 'object' && model instanceof Error) {
                model.display(request);
            } else {
                let view = ctrl_obj.view(format);
                 if (typeof view === "object" && view instanceof Error) {
                     view.type(format);
                 } else {
                     log(request, response, null);
                 }
                 view.display(request);
                 response.end();
            }
        } else {
            let Error_obj = new Error(response, 404);
            Error_obj.request(request);
            Error_obj.type(format);
            Error_obj.message(constants.ControllerNotFoundMessage(controller));
            Error_obj.display(request);
            response.end();
        }
    }
}, constants.ServerPort);

console.log(constants.ServerHomeMessage);