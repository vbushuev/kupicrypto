$(document).ready(function () {
    $(document).foundation();
//----------------------------------------------------------------------------------
    $('.slider1').bxSlider({
        mode: 'fade',
        captions: true,
        adaptiveHeight: true,
    });
//----------------------------------------------------------------------------------
    $('.btn1, .returnClose').click(function () {
        $('.return').toggleClass('active');
    });
//----------------------------------------------------------------------------------
    $('.btn2, .takeClose2').click(function () {
        $('.popup-wrpa.take2').toggleClass('active');
    });
//----------------------------------------------------------------------------------
    $('.btn3, .takeClose').click(function () {
        $('.popup-wrpa.take').toggleClass('active');
    });
//----------------------------------------------------------------------------------
    $('.btn4, .sellClose2').click(function () {
        $('.popup-wrpa.sell2').toggleClass('active');
    });
//----------------------------------------------------------------------------------
    $('.btn5, .sellClose').click(function () {
        $('.popup-wrpa.sell').toggleClass('active');
    });
//----------------------------------------------------------------------------------
    $('.btn6, .buyClose').click(function () {
        $('.popup-wrpa.buy').toggleClass('active');
    });
//----------------------------------------------------------------------------------





    $('.getAdvice, .closePopup').click(function () {
        $('.feedback').toggleClass('active');
    });
//----------------------------------------------------------------------------------

    $('.loanLink').click(function () {
        $('.loanLink').removeClass('active');
        $(this).addClass('active');
    });
    $('#check4').click(function () {
        $('.loanLink').removeClass('active');
        $('.clickL1').addClass('active');
    });
    $('.clickL1, #check4').click(function () {
        $('.loan2,.loan3,.loan4,.loan5').removeClass('active');
        $(this).addClass('active');
        $('.loan1').addClass('active');
    });

    $('.clickL2').click(function () {
        $('.loan1,.loan3,.loan4,.loan5').removeClass('active');
        $(this).addClass('active');
        $('.loan2').addClass('active');
    });

    $('.clickL3').click(function () {
        $('.loan1,.loan2,.loan4,.loan5').removeClass('active');
        $(this).addClass('active');
        $('.loan3').addClass('active');
    });

    $('.clickL4').click(function () {
        $('.loan1,.loan2,.loan3,.loan5').removeClass('active');
        $(this).addClass('active');
        $('.loan4').addClass('active');
    });
    $('.clickL5').click(function () {
        $('.loan1,.loan2,.loan3,.loan4').removeClass('active');
        $(this).addClass('active');
        $('.loan5').addClass('active');
    });


    $(document).mouseup(function (e){ // событие клика по веб-документу
        var div = $(".loan-c, .loanLink"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            div.removeClass('active'); // скрываем его

        }
    });

//----------------------------------------------------------------------------------
    $("#phoneTb, #phone-popup,input[name$='phone']").mask("+7 (999) 999 - 99 - 99");

});
