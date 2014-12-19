var esprima = require('esprima');
var fs = require('fs');

var args = process.argv.slice(2);
if (!args[0]) {
    console.log('directory to parse has to be set');
    process.exit(1);
}
var initialDirectory = args[0];

var translations = {
    trl: [],
    trlc: [],
    trlp: [],
    trlcp: [],

    trlKwf: [],
    trlcKwf: [],
    trlpKwf: [],
    trlcpKwf: []
};

var recursiveCheckForTrl = function(object, translations) {
    var key, child, calledFunction;
    if (object.type == 'CallExpression') {
        calledFunction = object.callee.name;
        if (calledFunction == 'trlKwf' || calledFunction == 'trl') {
            if (object.arguments[0].type == 'Literal') {
                translations[calledFunction].push(object.arguments[0].value);
            }
        } else if (calledFunction == 'trlpKwf' || calledFunction == 'trlp') {
            if (object.arguments[0].type == 'Literal' && object.arguments[1].type == 'Literal') {
                translations[calledFunction].push({
                    single: object.arguments[0].value,
                    plural: object.arguments[1].value
                });
            }
        } else if (calledFunction == 'trlcKwf' || calledFunction == 'trlc') {
            if (object.arguments[0].type == 'Literal' && object.arguments[1].type == 'Literal') {
                translations[calledFunction].push({
                    context: object.arguments[0].value,
                    msg: object.arguments[1].value
                });
            }
        } else if (calledFunction == 'trlcpKwf' || calledFunction == 'trlcp') {
            if (object.arguments[0].type == 'Literal'
                && object.arguments[1].type == 'Literal'
                && object.arguments[2].type == 'Literal'
            ) {
                translations[calledFunction].push({
                    context: object.arguments[0].value,
                    single: object.arguments[1].value,
                    plural: object.arguments[2].value
                });
            }
        }
    }
    for (key in object) {
        if (object.hasOwnProperty(key)) {
            child = object[key];
            if (typeof child === 'object' && child !== null) {
                recursiveCheckForTrl(child, translations);
            }
        }
    }
}

var recursiveParse = function(directory, translations) {
    var files = fs.readdirSync(directory);
    for (var i = 0; i < files.length; i++) {
        if (files[i] == '.git') continue;
        if (files[i] == 'node_modules') continue;
        if (files[i] == 'vendor') continue;


        var path = directory+'/'+files[i];
        if (fs.lstatSync(path).isDirectory()) {
            recursiveParse(path, translations);
        } else {
            var pathParts = files[i].split('.');
            if (pathParts[pathParts.length-1] == 'js') {
                recursiveCheckForTrl(esprima.parse(fs.readFileSync(path, 'utf8')), translations);
            }
        }
    }
}

recursiveParse(initialDirectory, translations);
console.log(JSON.stringify(translations));
