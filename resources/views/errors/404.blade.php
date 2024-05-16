<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Error: 404</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col justify-center items-center h-full w-full lg:max-h-lg bg-gray-50">
<div class="flex flex-col justify-center items-center max-w-md mx-auto bg-white rounded shadow p-8 max-h-full lg:max-h-[1280px] ">
    <div class="h-full w-full">
        <form class="flex flex-col h-full justify-around max-w-sm mx-auto text-center" action="/error-submit" method="post">
            @csrf
            <div class="flex flex-col mb-5">
                <h1 class="text-2xl font-medium mb-2">404</h1>
                <p class="text-gray-500 py-2">The resource you are looking for could not be found.</p>
                {!! $errors->first('email', '<div class="error-block">:message</div>') !!}
                <div class="pt-8">
                    <a href="/" class="text-[#57995a]">&larr; Go back to portal</a>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
