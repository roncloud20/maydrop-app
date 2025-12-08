<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified Successfully</title>
</head>
<body>
    <h1>Hello, {{$role}} {{ $fullname}}</h1>

    <p>Thank you for verifying your account, you can now login without pressure</p>
    <a href="{{$url_link}}">click here to login</a>
    <h3>Best Regards</h3>
</body>
</html>