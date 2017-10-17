var getParam = (function(){
    return function(n){
        var searchstr = (typeof(ORDERDESCRIPTION)!="undefined")?ORDERDESCRIPTION:location.search,
            p = searchstr.split(n+'=')[1];
        // console.debug("ORDERDESCRIPTION="+ORDERDESCRIPTION+" ["+searchstr+"]");
        if(p!=undefined) p = p.split('&')[0];
        return p;
    }
})();
var LuhnCheck = (function(){
    var luhnArr = [0, 2, 4, 6, 8, 1, 3, 5, 7, 9];
    return function(str){
        var counter = 0;
        var incNum;
        var odd = false;
        var temp = String(str).replace(/[^\d]/g, "");

        if ( temp.length == 0)
            return false;
        for (var i = temp.length-1; i >= 0; --i)
        {
            incNum = parseInt(temp.charAt(i), 10);
            counter += (odd = !odd)? incNum : luhnArr[incNum];
        }
        console.debug('check pan '+str+' '+((counter%10 == 0)?'OK':'failed'));
        return (counter%10 == 0);
    }
})();
var transfer ={
    valid:function(){
        var expire = $('#expire').val(),expireArr=expire.split('/');
        $('#expiremonth').val(expireArr[0]);
        $('#expireyear').val('20'+expireArr[1]);
        //lunh check
        var r=$('#cardnumber').val().replace(/\s/g,''),n=r.length;
        if(n>19||13>n)return!1;
        for(i=0,s=0,m=1,l=n;i<l;i++)d=parseInt(r.substring(l-i-1,l-i),10)*m,s+=d>=10?d%10+1:d,1==m?m++:m--;
        // console.debug($('#expiremonth').val(),$('#expireyear').val(),r,s);return false;
        return s%10==0?!0:!1;
    },
    init:(function(){
        return function(){
            $(".pan").mask("9999 9999 9999 9999? 999");
            $(".expire").mask("99/99");
            $(".cvv2").mask("999");
            $("[name=amount],[name=currency]").on("change keyup",function(){
                var val= parseFloat($('[name=amount]').val()), cur = $("[name=currency]").val(),s=false, $amt = $("[name=amount]");
                //
                switch(cur){
                    case 'RUB':if(val>75000)s = 'Сумма не может быть больше 75 000руб.';$amt.attr("max","75000");break;
                    case 'USD':if(val>2000)s = 'Сумма не может быть больше $2000.';$amt.attr("max","2000");break;
                    case 'EUR':if(val>2000)s = 'Сумма не может быть больше &euro;1500';$amt.attr("max","1500");break;
                }
                // console.debug(val,cur,s);
                if(s!==false){
                    $amt.next(".alert").html(s).show();
                }else $amt.next(".alert").html('');
            });
            $("input.email").on("change keyup blur",function(){
                var re = /[a-z\.\-\_]+@[a-z\.\-\_]+\.[a-z]+/i
                // console.debug($("[name=email]").val(),re,$("[name=email]").val().match(re));
                // if($("[name=email]").val().length && $("[name=email]").val().match(re))
                if($("[name=email]").val().length)
                    $(".sendMail").show();
                else $(".sendMail").hide();
            });
            // $(".email").mask("*@*.*");

            var amt = getParam('amount'),wallet = getParam('wallet'),currency = getParam('currency'), number = getParam('wallet_number'), coins = getParam('coins'), cvv2 = getParam('cvv2'), email = getParam('email');
            if(amt!=undefined && currency!=undefined){
                var s = '';
                switch(currency){
                    case "RUB":s='<span>'+parseFloat(amt).toLocaleString()+"</span> Руб.";break;
                    case "USD":s='$ <span>'+parseFloat(amt).toLocaleString()+"</span>";break;
                    case "EUR":s='&euro; <span>'+parseFloat(amt).toLocaleString()+"</span>";break;
                    default:s='<span>'+parseFloat(amt).toLocaleString()+"</span> "+currency;break;
                }
                $(".amount").html(s);
                $('[name=amount]').val(amt);
                $('[name=currency]').val(currency);
            }
            if(coins!=undefined && wallet!=undefined){
                $(".coins").html('<span>'+parseFloat(coins).toLocaleString()+'</span> '+wallet.toUpperCase());
                $('[name=coins]').val(coins);
                $('[name=wallet]').val(wallet);
            }
            if(number!=undefined){
                $(".wallet").html(number);
                $("[name=wallet_number]").val(number);
            }
            if(email!=undefined){
                email = email.replace(/%40/g,'@');
                $(".email:not(input)").text(email);
                $("[name=email]").val(email);
            }
            if(cvv2!=undefined){
                cvv2 = parseInt(cvv2);
                if(cvv2==123){ //success
                    $('.response').addClass('success');
                }else{ //failed
                    $('.response').addClass('failed')
                        .html('<div class="columns message">Операция не успешна, попробуйте повторить</div>')
                        .append('<div class="columns"><a class="btn min buyClose" href="/pne/form_first">Повторить</button></div>');
                }
            }
        };
    })()
};
transfer.init();
// $(document).ready(function(){

// });
