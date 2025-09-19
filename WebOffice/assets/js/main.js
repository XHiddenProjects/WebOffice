const animate = new AnimateJS();
const [container] = animate.Utils.$('.square');
$(document).ready(() => {
//$(document).on("contextmenu",(e=>e.preventDefault())),$(document).on("keydown",(e=>{(123===e.keyCode||e.ctrlKey&&e.shiftKey&&73===e.keyCode||e.ctrlKey&&85===e.keyCode)&&e.preventDefault()}));
    animate.Utils.$('[data-animate]').forEach((e)=>{
        if(e.getAttribute('data-animate')==='fade-left'){
                if(animate.Utils.inView(e)) animate.animate(e,'fade.inRight',{duration: animate.FAST});
            }else{
                if(animate.Utils.inView(e)) animate.animate(e,'fade.inLeft',{duration: animate.FAST});
            }
    });
    animate.scroll(window,(a)=>{
        a.Utils.$('[data-animate]').forEach((e)=>{
            if(e.getAttribute('data-animate')==='fade-left'){
                if(a.Utils.inView(e)) a.animate(e,'fade.inRight',{duration: animate.FAST});
                else a.animate(e,'fade.outRight',{duration: animate.FAST});
            }else{
                if(a.Utils.inView(e)) a.animate(e,'fade.inLeft',{duration: animate.FAST});
                else a.animate(e,'fade.outLeft',{duration: animate.FAST});
            }
        });
    });
    animate.Utils.$('.features-icon').forEach((e)=>{
        animate.hover(e,(a,e)=>{
            animate.animate(e,'zoom.in',{
                duration: 500
            });
        },(a,e)=>{
            animate.animate(e,'zoom.out',{
                duration: 500
            });
        })
    });
    $('.authorization-form .tab').each((_,e)=>{
        $(e).on('click',(i)=>{
            $('.authorization-form .tab').removeClass('active');
            if(i.target.tagName.toLowerCase()==='span'){
                const index = $(i.target).parent().attr('class');
                if(index.match(/register/)) {
                    $('.authorization-form .form-register').removeClass('d-none');
                    $('.authorization-form .form-login').addClass('d-none');
                }else{
                    $('.authorization-form .form-register').addClass('d-none');
                    $('.authorization-form .form-login').removeClass('d-none');
                }
                $(i.target).parent().addClass('active');
            }
            else {
                const index = $(i.target).attr('class');
                if(index.match(/register/)) {
                    $('.authorization-form .form-register').removeClass('d-none');
                    $('.authorization-form .form-login').addClass('d-none');
                }else{
                    $('.authorization-form .form-register').addClass('d-none');
                    $('.authorization-form .form-login').removeClass('d-none');
                }
                $(i.target).addClass('active');
            }
        });
    });
    $('.locales-select option').each((_,e)=>{
        const languageName = new Intl.DisplayNames($(e).attr('data-lang'),{type: 'language'}).of($(e).attr('data-lang')),
        regionName = new Intl.DisplayNames($(e).attr('data-lang'),{type: 'region'}).of($(e).attr('data-region').toUpperCase());
        const lang = navigator.language||navigator.languages[0];
        if($(e).val()===lang.toLocaleLowerCase()) $(e).attr('selected','selected');
        $(e).text(`${languageName} ${regionName ? `(${regionName})` : ''}`);
    });
    $(window).on('load resize',()=>{
        const obj = $('body').children().not('script').not('footer').last(),
        footer = $('footer');
        obj.css({"padding-bottom":`${Math.ceil(parseFloat(footer.css('height')))}px`});
    });
    //Tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    $('[data-timezone]').val(Intl.DateTimeFormat().resolvedOptions().timeZone);

    if ($('.authorization-form').length) {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('login')) {
            $('.tab.login').click();
        }
    }
    const mfa_digits = $('.tfa-panel .digit');
    mfa_digits.each((_,e)=>{
        $(e).on('keydown',(e)=>{
            e.preventDefault();
            const key = e.originalEvent.key,
            currentElement = $(e.target);
            if(parseInt(key)||key==="Backspace"){
                if(parseInt(key)){
                    currentElement.text(key);
                    currentElement.next().focus();
                }else{
                    currentElement.text('');
                    currentElement.prev().focus();
                }
            } 
            
        });
    });
    // Check if users are online or offline
    setInterval(()=>{
        $.ajax({
            url: `${BASE}/submissions/authStatus.php?path=${BASE}`,
            method: 'GET',
            dataType: 'json',
            success: function() {
                
            },
            error: function(xhr, status, error) {
                console.error('Error fetching user statuses:', error);
            }
        });
    }, 60000); // Update every 60 seconds
});