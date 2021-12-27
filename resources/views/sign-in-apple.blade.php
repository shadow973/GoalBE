<html>
<head>
    <meta name="appleid-signin-client-id" content="ge.goal.web">
    <meta name="appleid-signin-scope" content="name email">
    <meta name="appleid-signin-redirect-uri" content="https://v2.api.goal.ge/api/sign-in/apple">
    <meta name="appleid-signin-state" content="{{csrf_token()}}">
    <meta name="appleid-signin-nonce" content="passphrase">
    <meta name="appleid-signin-use-popup" content="true"> <!-- or false defaults to false -->
</head>
<body>
<div id="appleid-signin" data-color="black" data-border="true" data-type="sign in"></div>
<script type="text/javascript" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
</body>
</html>