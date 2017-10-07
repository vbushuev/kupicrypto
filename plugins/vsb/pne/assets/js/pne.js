"use strict";
window.pne={
    init:function(){
        this.checkall();
    },
    checkall:function(){
        this.related = function(){
            if($(".checkable:checked").length==0)$('[data-ref=checkable]').addClass('disabled');
            else $('[data-ref=checkable]').removeClass('disabled');
        };
        var that = this;
        $(".checkall:not(.pne-accepted)").on("change",function(){
            var what = $('[data-name='+$(this).attr("data-rel")+']');
            // console.debug(what,'[data-name='+$(this).attr("data-rel")+']');
            what.find('.checkable').prop("checked",$(this).is(':checked'));
            that.related();
        }).addClass("pne-accepted");
        $(".checkable:not(.pne-accepted)").on("change",function(){
            if(!$(this).is(":checked"))$(".checkall").prop("checked",false);
            that.related();
        }).addClass("pne-accepted");
    },
    removeall:function(){
        $('.removeall').on("click",function(){
            $(".checkable:checked").each(function(){
                var id = $(this).attr("data-value")
                console.debug('click - '+id);
                $('.remove[data-id='+id+']').click();
            });
        });
    }
};
$(document).ready(function(){
    console.debug('PNE JS loaded');
    pne.init();
});
