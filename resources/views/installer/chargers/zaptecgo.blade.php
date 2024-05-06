<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NC Installer Platform</title>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col justify-center items-center h-screen w-screen bg-gray-50 text-gray-700">
    <div class="flex flex-col justify-center items-center max-w-md mx-auto bg-white rounded shadow w-full min- p-8 min-h-[480px] max-h-[1280px] ">
        <div class="flex flex-col justify-between h-full w-full gap-2">
            <div class="flex flex-row justify-between items-center w-full">
                <div class="flex flex-col gap-1">
                    <a href="/" class="text-[#57995a]">&larr; Gå tilbage og søg</a>
                    <h1 class="text-2xl font-medium pt-1">{{ $product->name }}</h1>
                    <p class="text-md text-gray-900">S/N: {{ $charger->serial_number }}</p>
                </div>
                <img src="https://portal.nordiccharge.com/storage/{{ $product->image_url }}" alt="{{ $product->name }}" class="h-24 w-24 object-contain">
            </div>
            <div class="flex flex-col justify-between h-full w-full" x-data="{ formStep: 1 }">
                <hr class="my-6 border-[1px]">
                <div x-cloak x-show="formStep === 1">
                    <h3 class="font-bold text-xl pb-2">Installér lader</h3>
                    <p class="">Følg installationsvejledningen, som medfølger i kassen, for Zaptec Go.</p><br>
                    <p>
                        Har du ingen installationsvejledning, så kan du
                        <a class="underline" href="https://help.zaptec.com/hc/da/articles/13139473434513-S%C3%A5dan-installerer-du-Zaptec-Go#01GZK7GAHSNTY3GEQ0YWJE1E0W" target="_blank">
                            klikke her</a>.
                    </p><br>
                    <p class="font-medium">Når du har installeret og overført ejerskab til kunden, kan du fortsætte til næste trin.</p>
                </div>
                <div x-cloak x-show="formStep === 2">
                    <h3 class="font-bold text-xl pb-2">Integrér lader med Monta</h3>
                    <iframe src="https://integrations.monta.com/brands/zaptec/models/zaptec_go/methods?charge_point_identifier=01a5db6c-f1c5-11ee-b12c-ea7e0843fa57&user_identifier=user_2ea327e2-1bd2-11ee-8f83-c6f7be6ec46f&locale=en&operator_id=2253"
                            class="w-full h-[712px]">
                        <button x-cloak x-show="formStep > 1" @click="formStep -= 1" type="button" class="flex flex-row h-12 items-center text-white bg-[#FF5252] duration-200 hover:bg-[#FF3162] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                            Integrér med Monta <svg viewBox="0 0 24 24" class="h-4 w-4 ml-2" xmlns="http://www.w3.org/2000/svg" fill="#ffffff"><path d="m10 21c-2.49999947 2.5-4.99999998 2-7 0s-2.49999989-4.5.00000004-7 2.99999994-3 2.99999994-3l7.00000052 7s-.500001.5-3.0000005 3zm4.0003207-18c2.4996793-2.50000022 4.9996793-2.00000022 7.0007473 0s2.498932 4.49999978 0 7c-2.4989321 2.5000002-3.0003202 3-3.0003202 3l-7.0007478-7s.5006414-.49999978 3.0003207-3zm-3.0003207 6.9999-2.5 2.5000999zm3 3.0001-2.5 2.5z" fill="none" stroke="#fff" stroke-width="2"/></svg>
                        </button>
                    </iframe>
                    <p></p>
                </div>
                <div x-cloak x-show="formStep === 3">
                    <h3 class="font-bold text-xl pb-2">Opret kunde i Monta</h3>
                    <p>Hjælp kunden med at oprette sig i Monta appen</p>
                </div>
                <div x-cloak x-show="formStep === 4">
                    Final Step
                </div>
                <div class="flex flex-row w-full justify-between gap-4 h-12 mt-6">
                    <button x-cloak x-show="formStep > 1" @click="formStep -= 1" type="button" class="h-12 text-white bg-gray-600 duration-200 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                        &larr; Back
                    </button>
                    <button x-cloak x-show="formStep < 4" @click="formStep += 1" type="button" class="h-12 text-white bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                        Fortsæt &rarr;
                    </button>

                    <button x-cloak x-show="formStep === 4" type="submit" class="h-12 text-white bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">Submit</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
