$(document).ready(() => {
    $('.passkey-create').on('click', () => {
        // Fetch options for registration
        $.ajax({
            url: `${REQUEST_PATH}/challenge.php`,
            method: 'GET', // or POST if your backend expects it
            dataType: 'json',
            success: (options) => {
                // Use navigator.credentials.create with the options
                navigator.credentials.create({ publicKey: options })
                    .then(credential => {
                        // Send the credential to your server for verification
                        $.ajax({
                            url: `${REQUEST_PATH}/challenge.php`,
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify(credential),
                            success: (response) => {
                                console.log('Registration successful:', response);
                            },
                            error: (err) => {
                                console.error('Error during registration:', err);
                            }
                        });
                    })
                    .catch(err => {
                        console.error('Error creating credentials:', err);
                    });
            },
            error: (err) => {
                console.error('Error fetching options:', err);
            }
        });
    });

    $('.passkey-verify').on('click', () => {
        // Fetch options for authentication
        $.ajax({
            url: `${REQUEST_PATH}/challenge.php`,
            method: 'GET', // or POST as needed
            dataType: 'json',
            success: (options) => {
                // Use navigator.credentials.get with the options
                navigator.credentials.get({ publicKey: options })
                    .then(assertion => {
                        // Send the assertion back to your server for verification
                        $.ajax({
                            url: `${REQUEST_PATH}/challenge.php`,
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify(assertion),
                            success: (response) => {
                                console.log('Verification successful:', response);
                            },
                            error: (err) => {
                                console.error('Error during verification:', err);
                            }
                        });
                    })
                    .catch(err => {
                        console.error('Error during credential get:', err);
                    });
            },
            error: (err) => {
                console.error('Error fetching options:', err);
            }
        });
    });
});