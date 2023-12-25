<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Example</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" defer></script>
</head>
<body>

<input type="button" onclick="login()" value="Login">

<script>
    // app.js
    function login() {
        const fname = "Billy";
        const lname = "Hadiat";
        const pword = "123";
        const mail = "sammy@gmail.com";

        const userData = {
            fname: fname,
            lname: lname,
            pword: pword,
            mail: mail
        };

        // Make an API request to your server
        fetch('https://havens.iccflifeskills.com.ng/v0.1/api/auth/signUp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer 123' // Replace '123' with your actual key
            },
            body: JSON.stringify(userData),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Handle the response from the server
            console.log(data);
        })
        .catch(error => {
            // Handle errors
            console.error('Error:', error.message);
        });
    }
</script>

</body>
</html>



<!-- <script>
    function login() {
        // Get username and password from the form
        var fname = 1;
        var lname = "God bless Nigeria";
        var mail = "sam123@gmail.com";
        var pword = "123";
        var bearerToken = "123"; // Replace with your actual Bearer token

        // Create data object
        var data = {
            // usertoken: '480103',
            fname: fname,
            lname: lname,
            mail: mail,
            pword:pword
        };

        // Send AJAX request with Authorization header
        $.ajax({
            url: 'https://havens.iccflifeskills.com.ng/v0.1/api/auth/signUp',
            type: 'POST',
            data: JSON.stringify(data), // Convert data to JSON string
            contentType: 'application/json', // Set content type to JSON
            headers: {
                'Authorization': 'Bearer ' + bearerToken
            },
            success: function(response) {
                console.log('Request successful:', response);
                // Handle the response from the server
            },
            error: function(error) {
                console.error('Error:', error);
                // Handle errors
            }
        });
    }
</script> -->
