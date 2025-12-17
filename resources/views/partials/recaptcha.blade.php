{{-- 1. Load the API with the Global Key --}}
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>

{{-- 2. Initialize Logic --}}
<script>
    grecaptcha.ready(function() {
        // Use the global key from config
        grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'page_load'})
        .then(function(token) {
            console.log("Global reCAPTCHA active. Token generated.");
            
            // OPTIONAL: If you have hidden inputs in forms named 'recaptcha_token', 
            // this loop will automatically fill them all.
            document.querySelectorAll('input[name="recaptcha_token"]').forEach(input => {
                input.value = token;
            });
        });
    });
</script>

{{-- 3. Optional CSS to hide badge (remove if you want to show it) --}}
<style>
    /* .grecaptcha-badge { visibility: hidden; } */
</style>