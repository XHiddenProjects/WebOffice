$(document).ready(()=>{
    $(window).on('load',()=>{
        const currentHash = new URLSearchParams(window.location.search).get('page');
        if(currentHash)
            x = $(`.documentation-subsection-item[href="?page=${currentHash}"]`);
        else
            x = $('.documentation-subsection-item')[0];
        $(x).addClass('focus');

        $(`.documentation .documentation-content`).each((_,e)=>{
            const id = currentHash ? currentHash : $($('.documentation-subsection-item')[0])?.attr('href')?.replace('?page=','');
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

        

        // Generate navigation links for each h4 and set up click handlers
        /* $('.documentation .documentation-content h2').each(function(index, element){
            const id = $(element).attr('id');
            if(id){
                const link = $(`<li/>`).addClass('scroll-nav__item').html(`<a href="#${id}" class="scroll-nav__link">${$(element).text()}</a>`);
                $('.scroll-nav__list').append(link);
            }
        }); */

        const content = $('.documentation .documentation-content')[0];
        scrollnav.init(content,{
            insertTarget: $('.documentation .page-nav .page-nav-scroll')[0]
        });

        $('.documentation .page-nav .page-nav-scroll').remove();
        
    });
});