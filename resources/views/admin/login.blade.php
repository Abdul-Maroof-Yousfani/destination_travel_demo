<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --main-color: rgba(0, 0, 0, 0.22);
            --second-color: #2c2b2b;
        }
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;300&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: var(--main-color);
            font-family: 'Poppins', sans-serif;
        }

        .container {
            width:350px ;
            /* height:450px ; */
            background: var(--second-color);
            color: #fff;
            padding: 2rem;
            display: flex;
            justify-content: center;
            flex-direction: column;
            border-radius: 20px;
        }

        .headline {
            text-align: center;
            margin-bottom: .5em;
            font-size: 2rem;
        }

        .box {
            margin: .2em 0;
        }

        .container .box p {
            color: rgba(255, 255, 255, 0.781);
        }

        .container .box div {
            width: 100%;
            height: 40px;
            position: relative;
            margin: 0.5em 0;

        }

        .container .box input {
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--main-color);
            border: none;
            outline: none;
            padding-left: .8em;
            color: #fff;
            border-radius: 10px;
            transition: all 0.4s;
        }

        .container .box input:focus::placeholder {
            color: #ffffff;
        }

        .container .box div::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 102%;
            height: 105%;
            border-radius: 10px;
            background: linear-gradient(to right , #127f9fe0 ,#3587a0c5);
        }

        .loginBtn {
            width: 105%;
            height: 40px;
            border: none;
            margin: 0.5em 0;
            border-radius: 10px;
            transform: translate(-1%);
            background: linear-gradient(to right , #127f9fe0 ,#3587a0c5);
            color: #fff;
            cursor: pointer;
            transition: all 0.4s;
        }

        .loginBtn:hover {
            box-shadow: 0 0 10px #ffffff56;
            transform: translate(-1% , 5%);
        }

        .text {
            font-size: .8em;
            margin-top: 0.8em;
            text-align: center;
            color: rgba(255, 255, 255, 0.623);
        }

        .text a {
            color: rgba(255, 255, 255, 0.911);
            text-decoration: none;
            position: relative;
            left: 3px;
        }
        .error { color: #937676; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <p class="headline">Admin Login</p>

        <div class="box">
            <p>Email</p>
            <div><input type="email" id="email" placeholder="Enter your Email"></div>
        </div>

        <div class="box">
            <p>Password</p>
            <div><input type="password" id="password" placeholder="Enter your Password"></div>
        </div>

        <button class="loginBtn">Login</button>

        <div class="error" id="errorMsg"></div>
    </div>
<script>
    $(document).ready(function () {
        $('.loginBtn').on('click', function (e) {
            e.preventDefault();

            let email = $('#email').val().trim();
            let password = $('#password').val().trim();
            let errorBox = $('#errorMsg');

            errorBox.text('');

            if (!email || !password) {
                errorBox.text('Both fields are required.');
                return;
            }

            $.ajax({
                url: "{{ route('admin.login.submit') }}",
                method: 'POST',
                data: {
                    email: email,
                    password: password,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: res => window.location.href = res.redirect,
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Login failed.';
                    errorBox.text(msg);
                }
            });
        });
    });
</script>
</body>
</html>