<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body>
    <h1>Hello {{ $fullname }}</h1>
    <p>Please copy this code to verify your account</p>
    <h1 style="font-size: x-large; color:blue;">{{ $code }}</h1>
    <p> or click on the link below to verify your account</p>
    <a href="{{ $url_link }}">Verify Email</a>
    <p>{{ $url_link }}</p>

    <h3>Thank you</h3>

</body>
</html>