<div id="magic-form">
    <input id="magic-input" required>
    <button id="magic-submit">Sign in without password</button>
    <p id="validation-message"></p>
</div>

<script src="{{ asset('magicmk_integration.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        magic_script();

        window.magicmk = {
            project: '{{ env('MAGIC_LOGIN_PROJECT_KEY') }}',
            language: '',
            redirect_url: '',
            params: {
                // extra: "parameters",
            }
        };
    });
</script>
