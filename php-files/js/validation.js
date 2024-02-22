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