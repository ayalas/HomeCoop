function ChangeLanguage(value, sAddress, sLangSwitcherPath)
{
    sRedrParam = '';
    if (sAddress != null && sAddress.length > 0 )
        sRedrParam = '&redr=' + escape( sAddress );
    document.location = sLangSwitcherPath + '?lang=' + value + sRedrParam;
}

//source: http://dracoblue.net/dev/encodedecode-special-xml-characters-in-javascript/155/
var xml_special_to_escaped_one_map = {
    '&': '&amp;',
    '"': '&quot;',
    '<': '&lt;',
    '>': '&gt;'
};
 
var escaped_one_to_xml_special_map = {
    '&amp;': '&',
    '&quot;': '"',
    '&lt;': '<',
    '&gt;': '>'
};
 
function encodeXml(string) {
    return string.replace(/([\&"<>])/g, function(str, item) {
        return xml_special_to_escaped_one_map[item];
    });
};
 
function decodeXml(string) {
    return string.replace(/(&quot;|&lt;|&gt;|&amp;)/g,
        function(str, item) {
            return escaped_one_to_xml_special_map[item];
    });
}
