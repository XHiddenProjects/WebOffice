const animate = new AnimateJS();
const [container] = animate.Utils.$('.square');
$(document).ready(() => {
//$(document).on("contextmenu",(e=>e.preventDefault())),$(document).on("keydown",(e=>{(123===e.keyCode||e.ctrlKey&&e.shiftKey&&73===e.keyCode||e.ctrlKey&&85===e.keyCode)&&e.preventDefault()}));
    animate.Utils.$('[data-animate]').forEach((e)=>{
        if(e.getAttribute('data-animate')==='fade-left'){
                if(animate.Utils.inView(e)) animate.animate(e,'fade.inRight');
            }else{
                if(animate.Utils.inView(e)) animate.animate(e,'fade.inLeft');
            }
    });
    animate.scroll(window,()=>{
        animate.Utils.$('[data-animate]').forEach((e)=>{
            if(e.getAttribute('data-animate')==='fade-left'){
                if(animate.Utils.inView(e)) animate.animate(e,'fade.inRight');
                else animate.animate(e,'fade.outRight');
            }else{
                if(animate.Utils.inView(e)) animate.animate(e,'fade.inLeft');
                else animate.animate(e,'fade.outLeft');
            }
        });
    });
});