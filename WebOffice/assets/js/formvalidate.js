$(document).ready(()=>{
    const signUpForm = $('.authorization-form .sign_up_form'),
    loginForm = $('.authorization-form .login_form');
    signUpForm.find('input[type="checkbox"]').on('input',function(){
        $(this).prop('checked',true);
        $(this).attr('disabled','disabled');
    });
    signUpForm.on('submit',(e)=>{
        e.preventDefault();
        const submit = $(signUpForm).find('[type="submit"]'),
        originalText = $(submit).text();
        let canPass=true;
        signUpForm.find('input, select').each((_,e)=>{
            if($(e).attr('required')){
                $(e).parent().removeClass('form-input-invalid')
                $(e).parent().find('.error-msg').removeClass('d-block');
                if($(e).attr('type')==='checkbox'){
                    if(!$(e).is(':checked')) {
                        $(e).parent().find('.error-msg').addClass('d-block');
                        $(e).parent().addClass('form-input-invalid');
                        canPass=false;
                    }
                }else{
                    if(!$(e).val()) {
                        $(e).parent().find('.error-msg').addClass('d-block');
                        $(e).parent().addClass('form-input-invalid');
                        canPass=false;
                    }
                }
            }
        });
        if(canPass){
            submit.html(`<i class="fa-solid fa-spinner-third fa-spin"></i>`);
            sendRequest(`${BASE}/submissions/signup.php`,{
                data: new FormData($('.sign_up_form')[0]),
                method: 'POST'
            }).then((response)=>{
                const r = JSON.parse(response),
                alert = $(e.currentTarget).find('.alert');
                if(r['status'].match('error')){
                    alert.text(r.msg);
                    alert.removeClass('d-none');
                }else{
                    alert.text('');
                    alert.addClass('d-none');
                    window.open(`${BASE}/dashboard`,'_self');
                }
                submit.html(originalText);
            });
        }
    });
    $(loginForm).on('submit',(e)=>{
        e.preventDefault();
        const submit = $(loginForm).find('[type="submit"]'),
        originalText = $(submit).text();
        let canPass=true;
        loginForm.find('input, select').each((_,e)=>{
            if($(e).attr('required')){
                $(e).parent().removeClass('form-input-invalid')
                $(e).parent().find('.error-msg').removeClass('d-block');
                if(!$(e).val()) {
                    $(e).parent().find('.error-msg').addClass('d-block');
                    $(e).parent().addClass('form-input-invalid');
                    canPass=false;
                }
            }
        });
        if(canPass){
            submit.html(`<i class="fa-solid fa-spinner-third fa-spin"></i>`);
            
            sendRequest(`${BASE}/submissions/login.php`,{
                data: new FormData($('.login_form')[0]),
                method: 'POST'
            }).then((response)=>{
                const r = JSON.parse(response),
                alert = $(e.currentTarget).find('.alert');
                if(r['status'].match('error')){
                    alert.text(r.msg);
                    alert.removeClass('d-none');
                }else{
                    alert.text('');
                    alert.addClass('d-none');
                    if(r['msg']['2fa']) window.open(`${BASE}/2fa`,'_self');
                    else window.open(`${BASE}/dashboard`,'_self');
                }
                submit.html(originalText);
            });
        }
    });
    $('.tfa-panel form input').on('input',function(){
        const $input = $(this);
        $input.val($input.val().replace(/\D/g, '').substring(0, 6));
    });
    $('.tfa-panel form').on('submit',(e)=>{
        e.preventDefault();
        $(e.target).parent().find('.error-msg').removeClass('d-block');
        $(e.target).parent().find('input').removeClass('is-invalid');
        const submit = $(loginForm).find('[type="submit"]'),
        originalText = $(submit).text();
        canPass = true;
        if(!$('input').val()||$('input').val().length<6){
            $(e.target).parent().find('.error-msg').addClass('d-block');
            $(e.target).parent().find('input').addClass('is-invalid');
            canPass=false;
        }

        if(canPass){
            submit.html(`<i class="fa-solid fa-spinner-third fa-spin"></i>`);
            sendRequest(`${BASE}/submissions/mfa.php`,{
                data: new FormData($('.tfa-panel form')[0]),
                method: 'POST'
            }).then((response)=>{
                const r = JSON.parse(response),
                alert = $(e.currentTarget).find('.alert');
                if(r['status'].match('error')){
                    alert.text(r.msg);
                    alert.removeClass('d-none');
                }else{
                    alert.text('');
                    alert.addClass('d-none');
                    window.open(`${BASE}/dashboard`,'_self');
                }
            });
            submit.html(originalText);
        }
    });
})