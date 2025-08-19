$(document).ready(()=>{
    $(window).on('hashchange',()=>{
        const currentHash = window.location.hash;
        $('.documentation-subsection-item').each((i,e)=>{
            $(e).removeClass('focus');
        });
        $(`a[href="${currentHash}"]`).addClass('focus');

        $(`.documentation .documentation-content`).each((i,e)=>{
            if($(e).attr('id') === currentHash.replace('#',''))
                $(e).css('display','block');
            else
                $(e).css('display','none');
        });
    });
    $(window).on('load',()=>{
        const currentHash = window.location.hash;
        if(currentHash)
            x = $(`.documentation-subsection-item[href="${currentHash}"]`);
        else
            x = $('.documentation-subsection-item')[0];
        $(x).addClass('focus');

        $(`.documentation .documentation-content`).each((i,e)=>{
            const id = currentHash!=='' ? currentHash.replace('#','') : $($('.documentation-subsection-item')[0])?.attr('href')?.replace('#','');
            if($(e).attr('id') ===id)
                $(e).css('display','block');
            else
                $(e).css('display','none');
        });


        $('.documentation .collapse').each((i,e)=>{
            $(e).children('.collapse-title').on('click',function(){
                $(this).parent().toggleClass('active');
                const content = $(this).parent().children('.collapse-body');
                if(!$(this).parent().hasClass('active'))
                    $(content).css('max-height','');
                else
                    $(content).css('max-height',$(content).prop('scrollHeight')+`px`);

            });
        })
    });
})