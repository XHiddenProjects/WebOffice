const origin = window.location.origin;
const pathname = window.location.pathname;
let mainDir;
if("/"===pathname||""===pathname)mainDir=origin+"/";else{const i=pathname.split("/").filter((i=>""!==i));mainDir=`${origin}/${i[0]}/`}

$(document).ready(function() {
    // Initialize the facial recognition system
    const facialRecognition = new FacialRecognition();
    facialRecognition.init();
    facialRecognition.showFacePixel();
    $('#snapFacial').on('click', function() {
        facialRecognition.takePicture();
    });
    $('#checkFacial').on('click', function() {
        console.log(facialRecognition.compareFacial());
    });
    
    // Append the canvas to the body or a specific element
    document.body.appendChild(facialRecognition.canvasElement);
});
