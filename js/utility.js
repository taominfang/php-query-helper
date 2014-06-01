

function getUUID() {
    return 'xxxxxxxx-xxxx-adr-4xxx-yxxx-xxxxxxxxxxxx'.
            replace(/[xy]/g, function(c)
    {
        var r = Math.random() * 16 | 0, v = c == 'x' ? r : r & 0x3 | 0x8;
        return v.toString(16);
    });

}

var escapeHTML = (function() {
    'use strict';
    var chr = {
        '"': '&quot;', '&': '&amp;', "'": '&#39;',
        '/': '&#47;', '<': '&lt;', '>': '&gt;'
    };
    return function(text) {
        return text.replace(/[\"&'\/<>]/g, function(a) {
            return chr[a];
        });
    };
}());
