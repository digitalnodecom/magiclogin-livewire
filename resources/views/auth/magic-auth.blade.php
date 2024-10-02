<form id="magic-form">
    <input id="magic-input" required>
    <button id="magic-submit"></button>
    <p id="validation-message"></p>
</form>

<script src="{{ asset('magicmk_integration_ES6_min.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        window.magicmk = {
            project_slug: '{{ env('MAGIC_LOGIN_PROJECT_KEY') }}',
            language: '',
            redirect_url: '',
            params: {
                // extra: "parameters",
            }
        };

        magic_script();

    });
</script>
