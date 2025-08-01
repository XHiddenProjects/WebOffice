 const steps = ['checking', 'download', 'extract', 'finishing'];
    let currentStep = 0;

    function performStep() {
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
                // Wait 10 seconds before proceeding to next step
                setTimeout(function() {
                    currentStep++;
                    performStep();
                }, 10000);
            },
            error: function() {
                alert('An error occurred during step: ' + steps[currentStep]);
            }
        });
    }

    // Start the process
    performStep();
