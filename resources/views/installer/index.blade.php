<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NC Installer Platform</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col justify-center items-center h-screen w-screen bg-gray-50">
    <div class="flex flex-col justify-center items-center max-w-md mx-auto bg-white rounded shadow w-full min- p-8 min-h-[480px] max-h-[1280px] ">
        <div class="h-full w-full">
            <form class="flex flex-col h-full justify-between max-w-sm mx-auto text-center" action="/" method="post">
                @csrf
                <div class="flex flex-col mb-5">
                    <h1 class="text-2xl font-medium mb-4">Installér ladestander</h1>
                    @if(isset($error))
                        <p class="text-red-500 mb-2">{{ $error }}</p>
                    @endif
                    <label for="large-input" class="block mb-2 text-md font-medium text-gray-700 dark:text-white">Indtast serienummer</label>
                    <input type="text" name="serial" class="block uppercase text-5xl text-center w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" autofocus>
                    <button type="submit" class="text-white mt-4 bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md py-4 w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Find lader</button>
                </div>
                <div class="flex flex-col gap-2 text-gray-700">
                    <p class="text-md">Har du ikke et serienummer?</p>
                    <a href="#" class="text-md text-[#57995a]">+45 12 34 56 78</a>
                </div>
                <img class="h-10" src="https://nordiccharge.com/wp-content/uploads/2023/11/NordicChargeLogo2.svg">
            </form>
        </div>
    </div>
</body>
</html>
