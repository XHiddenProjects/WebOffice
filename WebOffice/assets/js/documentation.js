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
        $('.documentation .documentation-content h2').each(function(index, element){
            const id = $(element).attr('id');
            if(id){
                const link = $(`<a href="#${id}" class="anchor-link${index==0 ? " active" : ""}">${$(element).text()}</a>`);
                $('.page-nav .page-nav-link').append(link);
            }
        });

        $(`.documentation .page-nav .spyscroll`).css('height',`calc(${$('.documentation .page-nav .anchor-link')[0]?.clientHeight??0}px * ${$('.documentation .anchor-link').length})`);

        const dataSpyList = document.querySelectorAll('[data-bs-spy="scroll"]')
        dataSpyList.forEach(dataSpyEl => {
            bootstrap.ScrollSpy.getInstance(dataSpyEl).refresh();
        });

        $('[data-bs-spy="scroll"]').on('activate.bs.scrollspy', function(e) {
            // Select the container and bar
            const container = $('.spyscroll'),
            bar = $('.spyscroll-bar');
            activeLin = $(e.relatedTarget);
            // Calculate the position of the active link
            const containerOffset = container.offset().top;
            const linkOffset = activeLin.offset().top;
            const scrollTop = container.scrollTop();
            const position = linkOffset - containerOffset + scrollTop;
            // Move the bar to the position of the active link
            bar.css('transform', `translateY(${position}px)`);
            // Update active class on links
        });
        
    });
});