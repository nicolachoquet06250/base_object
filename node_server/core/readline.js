let fs = require('fs');
let filePtr = {};
let fileBuffer = {};
let buffer = new Buffer(4096);

exports.fopen = function(path, mode) {
    let handle = fs.openSync(path, mode);
    filePtr[handle] = 0;
    fileBuffer[handle]= [];
    return handle
};

exports.fclose = function(handle) {
    fs.closeSync(handle);
    if (handle in filePtr) {
        delete filePtr[handle];
        delete fileBuffer[handle];
    }
    return;
};

exports.fgets = function(handle) {
    if(fileBuffer[handle].length === 0) {
        let pos = filePtr[handle];
        let br = fs.readSync(handle, buffer, 0, 4096, pos);
        if(br < 4096) {
            delete filePtr[handle];
            if(br === 0)  return false
        }
        let lst = buffer.slice(0, br).toString().split("\n");
        let minus = 0;
        if(lst.length > 1) {
            let x = lst.pop();
            minus = x.length
        }
        fileBuffer[handle] = lst;
        filePtr[handle] = pos + br - minus
    }
    return fileBuffer[handle].shift()
};

exports.eof = function(handle) {
    return (handle in filePtr) === false && (fileBuffer[handle].length === 0)
};