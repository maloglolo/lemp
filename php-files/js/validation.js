const validation = new JustValidate("#signup");

validation      
        .addField("#name", [
            {
                rule: "required"
            }
        ])
        .addField("#email",  [
            {
                rule: "required"
            },
            {
                rule: "email"
            },
            {
                validator: (value) => () => {
                    return fetch("validate-email.php?email=" + 
                    encodeURIComponent(value))
                            .then(function(response) {
                                return response.json();
                            })
                            .then(function(json) {
                                return json.available;
                            });
                },
                errorMessage: "Email already taken"
            }
        ])
        .addField("#password", [
            {
                rule: "password"
            },        
            {
                rule: "required",
                errorMessage: "Password is required",
            },
            {
                // Example of a custom rule for password length
                validator: (value) => {
                    return value.length >= 8;
                },
                errorMessage: "Password must be at least 8 characters",
            }
        ])
        .addField("#password_confirmation", [
            {
                validator: (value, fields) => {
                    return value === fields["#password"].elem.value;
                },
                errorMessage: "Passwords must match"
            }
        ])
        .onSuccess((event) => {
            document.getElementById("signup").submit();
        });