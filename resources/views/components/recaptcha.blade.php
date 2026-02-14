@props([
    'action' => 'login',
])

@if(config('services.recaptcha.site_key'))
    <div class="g-recaptcha mb-4" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-action="{{ $action }}"></div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
