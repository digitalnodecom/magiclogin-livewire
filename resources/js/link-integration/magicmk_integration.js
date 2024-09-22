function magic_script() {

    let serverUrl = 'https://magic.mk';

    const magicForm = document.getElementById('magic-form');
    let magicInput = document.getElementById('magic-input');
    let magicSubmit = document.getElementById('magic-submit');

    // Let's make sure that magic-form element exists
    if (magicForm !== null) {

        // If we have already input and submit button added, let's use those
        if (magicInput == null && magicSubmit == null) {
            magicForm.innerHTML =
                '<div id="magic-card">' +
                '<form class="form" id="magic-auth">' +
                '<div class="input-control email"><input id="magic-input" type="email" required name="email" placeholder="Enter your email..."></div>' +
                '<div id="validation-message"></div>' +
                '<div class="input-control submit"><input id="magic-submit" type="submit" class="btn btn-primary" value="Sign in without password" style="color: white; background-color: rgb(94, 0, 207); border-color: rgb(94, 0, 207);"></div>' +
                '</form>' +
                '</div>';
            magicInput = document.getElementById('magic-input');
            magicSubmit = document.getElementById('magic-submit');
        }

        magicSubmit.addEventListener("click", function (event) {
            event.preventDefault();
            magicSubmit.disabled = true;
            var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

            if (reg.test(magicInput.value) == false) {
                document.getElementById("validation-message").style.color = "red";
                document.getElementById("validation-message").innerHTML = "You've entered an invalid email: " + magicInput.value;
                magicSubmit.disabled = false;
                return false;

            } else {

                let data = {
                    email: magicInput.value
                };
                if (window.magicmk !== undefined) {
                    Object.assign(data, window.magicmk)
                }

                fetch(serverUrl + "/api/login/", {
                    method: "POST",
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                }).then(response => {
                    const card = document.getElementById("magic-form");
                    if (!response.ok) {
                        // If response is > 300 | Failure
                        card.innerHTML =
                            '<div class="sent-title">Something went wrong</div>' +
                            '<div class="sent-subtitle link"><a href=".">Please try again.</a></div>';
                        magicSubmit.disabled = false;
                    } else {
                        // If response is < 300 | Success
                        card.innerHTML =
                            '<div class="sent-title">Check your email</div>' +
                            '<div class="sent-subtitle">A verification link has been sent to your email address: ' + magicInput.value + ' </div>';
                    }

                    return response.json();

                }).then((data) => {
                    if (data.request_id !== undefined) {

                        const intervalId = setInterval(function () {
                            fetch(serverUrl + "/api/check", {
                                method: "POST",
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({
                                    project_slug: window.magicmk.project,
                                    request_id: data.request_id
                                })
                            }).then(response => {
                                if (response.status === 200) {
                                    return response.json();
                                } else {
                                    // console.warn('Unexpected status code: ' + response.status);
                                    return null;
                                }
                            }).then((data) => {
                                if (data) {
                                    let redirect_to = '';
                                    // TODO: Implement secret key security
                                    if (data.request_id !== undefined) {
                                        // Check if we have a redirect_url from the provider configuration
                                        if (data.redirect_url) {
                                            redirect_to = data.redirect_url
                                        }
                                        // Check if we have a hardcoded redirect_url in the page
                                        if (window.magicmk.redirect_url) {
                                            redirect_to = new URL(window.magicmk.redirect_url)
                                        }
                                        // If we have a redirect_to variable then let's redirect
                                        if (redirect_to) {
                                            let params = new URLSearchParams(redirect_to.search)
                                            params.append("type", "magic")
                                            params.append("token", data.token)
                                            params.append("project", window.magicmk.project)
                                            params.append("request_id", data.request_id)

                                            let base_url = new URL(redirect_to);
                                            let final_url = `${base_url}?${params.toString()}`;
                                            window.location.replace(final_url)
                                            clearInterval(intervalId);
                                        }
                                        // Otherwise lets send an event letting the client know auth is successful
                                        const event = new CustomEvent(
                                            "magicauth-success",
                                            {
                                                token: data.token,
                                                project: window.magicmk.project,
                                                request_id: data.request_id
                                            }
                                        );
                                        dispatchEvent(event);

                                    }
                                }
                            })
                        }, 2000);
                    }
                }).catch(() => {
                    magicSubmit.disabled = false;
                });
            }

        });
    } else {
        console.log("Could not find the div with id 'magic-form'")
    }


}

export default magic_script;
