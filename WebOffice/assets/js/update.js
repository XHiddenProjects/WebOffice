const steps = ['checking', 'download', 'extract', 'finishing'];
let currentStep = 0;
function performUpdate() {
    if (currentStep >= steps.length) {
        alert('Update process completed.');
        return;
    }
    $.ajax({
        url: 'update.php',
        type: 'POST',
        data: { step: steps[currentStep] },
        dataType: 'json',
        success: function(response) {
            console.log(`Step: ${steps[currentStep]}, Status: ${response.status}, Message: ${response.message}`);
            setTimeout(function() {
                currentStep++;
                performUpdate();
            }, 10000);
        },
        error: function() {
            return {'error':`An error occurred during step: ${steps[currentStep]}`};
        }
    });
}
// Start the process
performUpdate();