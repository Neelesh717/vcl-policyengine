function setCombinedDateTime(inDate, inTime, outDateTime) {
        if(! dijit.byId(inDate.toString()).isValid() ||
           ! dijit.byId(inTime.toString()).isValid()) {
                dojo.byId(outDateTime.toString()).value = '';
                return;
        }
        var d = dijit.byId(inDate.toString()).value;
        var t = dijit.byId(inTime.toString()).value;
        if(d == null || t == null) {
                dojo.byId(outDateTime.toString()).value = '';
                return;
        }
        dojo.byId(outDateTime.toString()).value = dojox.string.sprintf('%d-%02d-%02d %02d:%02d:00',
                                     d.getFullYear(),
                                     (d.getMonth() + 1),
                                     d.getDate(),
                                     t.getHours(),
                                     t.getMinutes());
}

function resetContinutation(src) {
    var opt = parseInt(dojo.byId("theoption").value);
    switch(opt) {
        case 4:
            dojo.byId("continuation").value = dojo.byId('OPT_4').value;
            dojo.query("#advRedirectMsg").style('display', "");
            break;
        default:
            dojo.byId("continuation").value = dojo.byId('original').value;
            dojo.query("#advRedirectMsg").style('display', "none");
            break;
    }
    
}
