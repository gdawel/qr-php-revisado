//on load
$(document).ready(function() {
	setMsgBoxTimer();
    setMasks();
});


//Set msgbox auto hidder
function setMsgBoxTimer() {
	var t = setTimeout(function() {hideMsgBox();}, 10000);
}

//Adaptacao do markup meio.mask.js para o jquery.mask
function setMasks() {
    try {
        $("[alt='cep']").mask('00000-000');
        $("[alt='cpf']").mask('000.000.000-00', {reverse: true});
        $("[alt='date']").mask('00/00/0000');

        var _masks = ['(00) 0000-00009', '(00) 00000-0000'];
        $("[alt='phone']").mask(_masks[0], {
            onKeyPress: function(phone, e, c){
                $(c).mask(
                    (phone.length == 15) ? _masks[1] : _masks[0]
                    , this
                );
            }
        });
    }
    catch (ex) {

    }
}

//hide message box
function hideMsgBox() {
    //$('.InfoBox').fadeOut();
    //$('.Error').fadeOut();
}

//InfoMsg functions
function closeInfoBox() {
	//$('.InfoBox').fadeOut();
}


//+ Jonas Raoni Soares Silva
//@ http://jsfromhell.com/number/fmt-money [rev. #2]

Number.prototype.formatMoney = function(c, d, t){
    var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "",
    i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t)
    + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};


function focusLoginField(fieldType) {
    var ctrl = (fieldType == 1) ? $('#user') : $('#password');
    var str = (fieldType == 1) ? 'E-mail' : 'Senha';
    
    if (ctrl.val() == str) {
        ctrl.removeClass('Cinza');
        ctrl.val('');
    } else {
        
    }
}

function blurLoginField(fieldType) {
    var ctrl = (fieldType == 1) ? $('#user') : $('#password');
    var str = (fieldType == 1) ? 'E-mail' : 'Senha';
    
    if (ctrl.val() == '') {
        ctrl.addClass('Cinza');
        ctrl.val(str);
    } else {
        
    }
}


/**
 * Converts querystring into a object.
 */
var urlParams;
(window.onpopstate = function () {
    var match,
        pl = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
        query = window.location.search.substring(1);

    urlParams = {};
    while (match = search.exec(query)) {
        urlParams[decode(match[1])] = decode(match[2]);
    }
})();