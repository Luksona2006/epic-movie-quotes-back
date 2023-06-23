<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Please verify your email address</title>
</head>
<body>
    <div style="width: auto; background: linear-gradient(187.16deg, #181623 0.07%, #191725 51.65%, #0D0B14 98.75%); padding: 78px 195px;">
        <div style="text-align: center;">
            <img src="https://i.ibb.co/b7YGk57/bi-chat-quote-fill.png" alt="bi-chat-quote-fill">
            <p style="color: #DDCCAA; font-weight: 500; font-size: 12px;">Movie quotes</p>
        </div>
        <div style="margin-top: 73px;">
            <p style="margin-bottom: 40px; font-size: 16px; font-weight: 400; color: white;">Welcome back! Please click the button below to confirm that you want to reset password</p>

            <a style="margin-bottom: 40px;"  href="{{ route('reset.redirect-to-password-reset', ['token' => $data['token']]) }}">
                <button
                style="background-color: #E31221; border:#E31221 1px solid; padding: 7px 13px; border-radius:4px; color:white; font-weight:400; font-size:16px; cursor: pointer;">
                    Reset Password
                </button>
            </a>
            <p style="margin-bottom: 24px; font-size: 16px; font-weight: 400; color: white;">If clicking doesn't work, you can try copying and pasting it to your browser:</p>
            <a href="{{ route('reset.redirect-to-password-reset', ['token' => $data['token']]) }}" style="text-decoration: none; margin-bottom: 40px;">
                <p style="color: #DDCCAA; font-size: 16px; font-weight: 400;">{{env('APP_URL')}}/reset-password/redirect/{{$data['token']}}</p>
            </a>
            <p style="margin-bottom: 24px; font-size: 16px; font-weight: 400; color: white;">If you have any problems, please contact us:support@moviequotes.ge</p>
            <p style="font-size: 16px; font-weight: 400; color: white;">MovieQuotes Crew</p>
        </div>
    </div>
</body>
</html>