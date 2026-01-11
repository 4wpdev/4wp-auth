/**
 * 4wp-auth Frontend Script
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle auth button clicks
        $(document).on('click', '.forwp-auth-btn', function(e) {
            e.preventDefault();
            
            const provider = $(this).data('provider');
            if (!provider) {
                console.error('Provider not specified');
                return;
            }

            // Get authorization URL
            const apiUrl = forwpAuth.apiUrl + 'auth/' + provider;
            console.log('Requesting auth URL from:', apiUrl);
            
            fetch(apiUrl)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.auth_url) {
                        window.location.href = data.auth_url;
                    } else if (data.code) {
                        const errorMsg = data.message || data.code;
                        console.error('API Error:', data);
                        alert('Помилка: ' + errorMsg);
                    } else {
                        console.error('Unexpected response:', data);
                        alert('Неочікувана помилка. Перевірте консоль браузера (F12).');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Помилка підключення: ' + error.message + '\n\nПеревірте консоль браузера (F12) для деталей.');
                });
        });

        // Display error message if present
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('forwp_auth_error');
        if (error) {
            const errorMessage = decodeURIComponent(error);
            // You can customize this to show a nicer error message
            console.error('Auth error:', errorMessage);
        }
    });
})(jQuery);

