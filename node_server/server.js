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

    console.log(controller, method, format, args);

    if(controller === 'static') {
        args = new args_class(args);
        let file = args.get('f');
        let path;
        if (method === 'css') {
            if (args.get('a')) {
                let authorisations = confs.get_authorizations('scss');//fs.readFileSync(constants.CssAuthorisationsPath).toString();
                authorisations = new args_class(JSON.parse(authorisations));
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
                    exec(cmd, () => {
                        exec('cat ./statics/css/compile_toto.css', (err, out) => {
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
            }

            path = `${constants.StaticsPath}/${method}/${file}`;
            if (fs.existsSync(path)) {
                response.writeHead(200, {'Content-Type': constants.StaticsMimeTypes['css']});
                    fs.readFile(constants.ScssDestination + '/' + file, null, function (err, data) {
                });
            } else {
                response.writeHead(404, {'Content-Type': constants.StaticsMimeTypes['css']});
                response.write('');
            }
        } else if (utils.in(method, constants.StaticsControllers) && method !== 'css') {
            if (method === 'js') {
                let authorisations = fs.readFileSync(constants.JsAuthorisationsPath).toString();
                authorisations = JSON.parse(authorisations);
                let files;
                if ((files = authorisations.get(args.get('a'))) !== false) {
                    let concat = '';
                    files.forEach(file => {
                        if (fs.existsSync(`${constants.ScssSources}/${file}.js`)) {
                            concat += fs.readFileSync(`${constants.ScssSources}/${file}.js`).toString() + "\n";
                        }
                    });
                    response.write(concat);
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
                }
            }
            if (fs.existsSync(`${constants.StaticsPath}/${method}/${url}`)) {
                if (constants.StaticsMimeTypes[method] !== undefined) {
                    let mime;
                    if (constants.StaticsMimeTypes[method].split('/')[1] !== '') {
                        mime = constants.StaticsMimeTypes[method];
                    } else {
                        mime = constants.StaticsMimeTypes[method] + method;
                    }
                    response.writeHead(200, {'Content-Type': mime});
                }
                response.write(fs.readFileSync(`${constants.StaticsPath}/${method}/${url}`));
            } else {
                if (constants.StaticsMimeTypes[method] !== undefined) {
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

    }
    // process.exit();

    // let ctrl_name = url.split('/')[1];
    // let ext_name;
    //
    // if (ctrl_name === 'static') {
    //     let directory = url.split('/')[2];
    //     let file = url.split('/')[3];
    //     let _url = url.split('/');
    //
    //     delete _url[0];
    //     delete _url[1];
    //     delete _url[2];
    //     delete _url[3];
    //
    //     let args = [];
    //     _url.forEach(obj => {
    //         if (obj !== undefined && obj !== '') {
    //             args[args.length] = obj;
    //         }
    //     });
    //     url = url.replace('/' + args.join('/'), '');
    //     url = url.split('/');
    //
    //     delete url[0];
    //     delete url[1];
    //     let tmp_url = [];
    //     for (let i = 0, max = url.length; i < max; i++) {
    //         if (url[i] !== undefined && url[i] !== '') {
    //             tmp_url[tmp_url.length] = url[i];
    //         }
    //     }
    //     url = tmp_url.join('/');
    //     if (directory === 'css') {
    //         args = new args_class(utils.format_args(args));
    //         if (args.get('a')) {
    //             let authorisations = fs.readFileSync(constants.CssAuthorisationsPath).toString();
    //             authorisations = new args_class(JSON.parse(authorisations));
    //             let files;
    //             if ((files = authorisations.get(args.get('a'))) !== false) {
    //                 let concat = '';
    //                 files.forEach(file => {
    //                     if (fs.existsSync(`${constants.ScssSources}/${file}.scss`)) {
    //                         concat += fs.readFileSync(`${constants.ScssSources}/${file}.scss`).toString() + "\n";
    //                     }
    //                 });
    //                 fs.writeFile(`${constants.ScssSources}/uncompile_${args.get('a')}.scss`, concat);
    //                 let cmd = 'node-sass --output-style uncompressed ' + constants.ScssSources + '/uncompile_' + args.get('a') + '.scss > ' + constants.ScssDestination + '/compile_' + args.get('a') + '.css';
    //                 exec(cmd, () => {
    //                     exec('cat ./statics/css/compile_toto.css', (err, out) => {
    //                         if (out !== '') {
    //                             response.write(out);
    //                             response.end();
    //                         }
    //                     });
    //                 });
    //             }
    //
    //             file = 'compile_' + args.get('a') + '.css';
    //         } else {
    //             let path = `${constants.StaticsPath}/${directory}/${file}`;
    //             if (fs.existsSync(path)) {
    //                 response.writeHead(200, {'Content-Type': 'text/css'});
    //                 fs.readFile(path, null, function (err, data) {
    //                     response.write(data.toString());
    //                     response.end();
    //                 });
    //             } else {
    //                 response.writeHead(404, {'Content-Type': 'text/css'});
    //                 response.write('');
    //                 response.end();
    //             }
    //         }
    //
    //         let path = `${constants.StaticsPath}/${directory}/${file}`;
    //         if (fs.existsSync(path)) {
    //             response.writeHead(200, {'Content-Type': 'text/css'});
    //             fs.readFile(constants.ScssDestination + '/' + file, null, function (err, data) {
    //             });
    //         } else {
    //             response.writeHead(404, {'Content-Type': 'text/css'});
    //             response.write('');
    //             response.end();
    //         }
    //     } else if (utils.in(directory, constants.StaticsControllers) && directory !== 'css') {
    //         if (directory === 'js') {
    //             let authorisations = fs.readFileSync(constants.JsAuthorisationsPath).toString();
    //             authorisations = JSON.parse(authorisations);
    //         }
    //         if (fs.existsSync(`${constants.StaticsPath}/${directory}/${url}`)) {
    //             if (constants.StaticsMimeTypes[directory] !== undefined) {
    //                 let mime;
    //                 if (constants.StaticsMimeTypes[directory].split('/')[1] !== '') {
    //                     mime = constants.StaticsMimeTypes[directory];
    //                 } else {
    //                     mime = constants.StaticsMimeTypes[directory] + directory;
    //                 }
    //                 response.writeHead(200, {'Content-Type': mime});
    //             }
    //             response.write(fs.readFileSync(`${constants.StaticsPath}/${directory}/${url}`));
    //         } else {
    //             if (constants.StaticsMimeTypes[directory] !== undefined) {
    //                 let Error_obj = new Error(response, 404);
    //                 Error_obj.request(request);
    //                 Error_obj.type('html');
    //                 Error_obj.message('`' + url + '` file not found !');
    //                 Error_obj.display(request);
    //             }
    //         }
    //         response.end();
    //     }
    // } else {
    //     // let router = JSON.parse(fs.readFileSync(`${constants.ConfsPath}/router.json`));
    //     constants.console.log(url, router);
    //
    //     response.end();
    //     // let router_keys = Object.keys(router);
    //     // if (url === '/') {
    //     //     if (utils.in(url, router_keys)) {
    //     //         let route = router[url];
    //     //
    //     //         ctrl_name = route['controller'];
    //     //         let methode = route['method'];
    //     //         ext_name = route['format'];
    //     //         let args = route['args'];
    //     //
    //     //         if (fs.existsSync(constants.MvcControllersPath + '/' + ctrl_name + '.js')) {
    //     //             let ctrl = require(constants.MvcControllersPath + '/' + ctrl_name);
    //     //             let ctrl_obj = new ctrl(request, response);
    //     //             ctrl_obj.object.setClass(ctrl_name);
    //     //             let model = ctrl_obj.model(methode, args, ext_name);
    //     //             if (typeof model === 'object' && model instanceof Error) {
    //     //                 model.display(request);
    //     //             } else {
    //     //                 let view = ctrl_obj.view(ext_name);
    //     //
    //     //                 if (typeof view === "object" && view instanceof Error) {
    //     //                     if (request.url.indexOf('.', 0)) {
    //     //                         view.type(ext_name);
    //     //                     }
    //     //                 } else {
    //     //                     log(request, response, null);
    //     //                 }
    //     //                 view.display(request);
    //     //             }
    //     //         } else {
    //     //             let Error_obj = new Error(response, 404);
    //     //             Error_obj.request(request);
    //     //             Error_obj.type(ext_name);
    //     //             Error_obj.message(constants.ControllerNotFoundMessage(ctrl_name));
    //     //             Error_obj.display(request);
    //     //         }
    //     //     }
    //     //     response.end();
    //     // } else {
    //     //     if(ctrl_name.indexOf('=')) {
    //     //         let _url = url.split('/');
    //     //
    //     //         let args = [];
    //     //         _url.forEach((obj, key) => {
    //     //             if (obj.indexOf('=') && obj !== '') {
    //     //                 args[args.length] = obj;
    //     //                 delete _url[key];
    //     //             }
    //     //         });
    //     //
    //     //         console.log(_url, url, args, utils.in(url, router_keys));
    //     //
    //     //         url = '/';
    //     //
    //     //         if (utils.in(url, router_keys)) {
    //     //             let route = router[url];
    //     //
    //     //             ctrl_name = route['controller'];
    //     //             let methode = route['method'];
    //     //             ext_name = route['format'];
    //     //             let args = route['args'];
    //     //
    //     //             if (fs.existsSync(constants.MvcControllersPath + '/' + ctrl_name + '.js')) {
    //     //                 let ctrl = require(constants.MvcControllersPath + '/' + ctrl_name);
    //     //                 let ctrl_obj = new ctrl(request, response);
    //     //                 ctrl_obj.object.setClass(ctrl_name);
    //     //                 let model = ctrl_obj.model(methode, utils.format_args(args), ext_name);
    //     //                 if (typeof model === 'object' && model instanceof Error) {
    //     //                     model.display(request);
    //     //                 } else {
    //     //                     let view = ctrl_obj.view(ext_name);
    //     //
    //     //                     if (typeof view === "object" && view instanceof Error) {
    //     //                         if (request.url.indexOf('.', 0)) {
    //     //                             view.type(ext_name);
    //     //                         }
    //     //                     } else {
    //     //                         log(request, response, null);
    //     //                     }
    //     //                     view.display(request);
    //     //                 }
    //     //             } else {
    //     //                 let Error_obj = new Error(response, 404);
    //     //                 Error_obj.request(request);
    //     //                 Error_obj.type(ext_name);
    //     //                 Error_obj.message(constants.ControllerNotFoundMessage(ctrl_name));
    //     //                 Error_obj.display(request);
    //     //             }
    //     //         }
    //     //     }
    //     //     else {
    //     //         let methode_name = url.split('/')[2];
    //     //         let methode_name_split = methode_name.split('.');
    //     //         let methode = methode_name_split.length > 1 ? methode_name_split[0] : methode_name;
    //     //         ext_name = methode_name_split.length > 1 ? methode_name_split[1] : constants.DefaultFormat;
    //     //
    //     //         url = url.split('/');
    //     //
    //     //         let args = [];
    //     //         url.forEach((obj, key) => {
    //     //             if (obj.indexOf('=')) {
    //     //                 args[args.length] = obj;
    //     //                 delete url[key];
    //     //             }
    //     //         });
    //     //
    //     //         // delete url[0];
    //     //         // delete url[1];
    //     //         // delete url[2];
    //     //     }
    //     //
    //     //     let tmp_args = args.join('/');
    //     //     console.log(ctrl_name, methode_name, methode, ext_name, args);
    //     //     let route_url = tmp_args.replace('/' + tmp_args, '');
    //     //     console.log(route_url);
    //     //
    //     //     // if (fs.existsSync(constants.MvcControllersPath + '/' + ctrl_name + '.js')) {
    //     //     //     let ctrl = require(constants.MvcControllersPath + '/' + ctrl_name);
    //     //     //     let ctrl_obj = new ctrl(request, response);
    //     //     //     ctrl_obj.object.setClass(ctrl_name);
    //     //     //     let model = ctrl_obj.model(methode, args, ext_name);
    //     //     //     if (typeof model === 'object' && model instanceof Error) {
    //     //     //         model.display(request);
    //     //     //     } else {
    //     //     //         let view = ctrl_obj.view(ext_name);
    //     //     //
    //     //     //         if (typeof view === "object" && view instanceof Error) {
    //     //     //             if (request.url.indexOf('.', 0)) {
    //     //     //                 view.type(ext_name);
    //     //     //             }
    //     //     //         } else {
    //     //     //             log(request, response, null);
    //     //     //         }
    //     //     //         view.display(request);
    //     //     //     }
    //     //     // } else {
    //     //     //     let Error_obj = new Error(response, 404);
    //     //     //     Error_obj.request(request);
    //     //     //     Error_obj.type(ext_name);
    //     //     //     Error_obj.message('controller ' + ctrl_name + ' not found !');
    //     //     //     Error_obj.display(request);
    //     //     // }
    //     //     response.end();
    //     // }
    // }
}, constants.ServerPort);

console.log(constants.ServerHomeMessage);