module.exports = class Json {
    constructor(response, http_code) {
        this.response = response;
        this.http_code = http_code;
        this._message = [];
    }

    message(message) {
        this._message = message;
    }

    display() {
        this.response.writeHead(this.http_code, {'Content-Type': 'application/json'});
        if(typeof this._message !== 'string') {
            this._message = JSON.stringify(this._message);
        }
        this.response.write(this._message);
    }
};