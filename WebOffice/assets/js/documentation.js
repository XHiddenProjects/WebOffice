$(document).ready(()=>{
    $(window).on('load',()=>{
        const currentHash = new URLSearchParams(window.location.search).get('page');
        if(currentHash)
            x = $(`.documentation-subsection-item[href="?page=${currentHash}"]`);
        else
            x = $('.documentation-subsection-item')[0];
        $(x).addClass('focus');

        $(`.documentation .documentation-content`).each((_,e)=>{
            const id = currentHash ? currentHash.replace(/\|/g,'_') : $($('.documentation-subsection-item')[0])?.attr('href')?.replace(/\?page=/,'').replace(/\|/g,'_');
            if($(e).attr('id') === id)
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
            if($(e).find('.documentation-subsection-item.focus').length>0){
                $(e).toggleClass('active');
                const content = $(e).find('.collapse-body');
                if(!$(e).hasClass('active'))
                    $(content).css('max-height','');
                else
                    $(content).css('max-height',$(content).prop('scrollHeight')+`px`);
            }
        });

        $('.documentation .documentation-codeblock').each((i,e)=>{
            $(e).find('[data-command]').each((j,el)=>{
                $(el).on('click',function(){
                    $(this).parent().find('[data-command]').removeAttr('selected');
                    $(this).attr('selected',true);
                    const code = $(this).parent().parent().parent().find('.code-content');
                    if(code.length>0){
                        code.text($(this).attr('data-command'));
                        $(code).removeClass(function(index, className) {
                            return (className.match(/(^|\s)language-\S+/g) || []).join(' ');
                        }).addClass(`language-${$(this).attr('data-lang')}`);
                        Prism.highlightElement(code[0]);
                    }
                });
            });
        });
        $('.documentation .documentation-codeblock [data-copy]').on('click',function(){
            const code = $(this).parent().parent().parent().find('.code-content');
            if(code.length>0){
                const text = code.text();
                navigator.clipboard.writeText(text).then(()=>{
                    alert('Code copied to clipboard!');
                }).catch(err=>{
                    console.error('Failed to copy text: ', err);
                });
            }
        });
        setTimeout(()=>{
            const params = new URLSearchParams(window.location.search);
            const content = $(`.documentation .documentation-content#${(params.get('page') ? params.get('page').replace(/\|/g, '_') : 'getting_started_quickstart')}`)[0];
            scrollnav.init(content,{
                insertTarget: $('.documentation .page-nav .page-nav-scroll')[0]
            });
            $('.documentation .page-nav .page-nav-scroll').remove();
        },100);
    });
    $('#documentation-searchbar').on('focus',function(){$(this).blur();});
    $(window).on('keydown',function(e){
        const key = e.key||e.keyCode||e.which;
        if(e.ctrlKey&&key=='k'&&$('.modal.show').length==0){
            e.preventDefault();
            $('#documentation-searchbar').click();
        }
    });
});
