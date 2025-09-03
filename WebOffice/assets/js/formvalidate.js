$(document).ready(()=>{
    const signUpForm = $('.authorization-form .sign_up_form');
    signUpForm.on('submit',(e)=>{
        e.preventDefault();
        signUpForm.find('input, select').each((_,e)=>{
            if($(e).attr('required')){
                $(e).parent().removeClass('form-input-invalid')
                if(!$(e).val()) $(e).parent().addClass('form-input-invalid');
            }
        });
        
    });
})