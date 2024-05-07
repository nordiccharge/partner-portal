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
<body class="flex flex-col justify-center items-center min-h-full w-full bg-gray-50 text-gray-700" x-data="modalData">
    <div class="flex flex-col justify-center items-center max-w-md mx-auto bg-white rounded shadow w-full min- p-8 min-h-full lg:max-h-[1280px] ">
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
                    <h3 class="font-bold text-xl pb-2">Opret login til kunde i Zaptec Cloud</h3>
                    <p>Download Zaptec appen på kundens telefon og bekræft at de har adgang til deres nye lader.</p><br>
                    <p>Du skal bruge kundens login til Zaptec ved næste trin i vejledningen</p><br>
                    <p class="font-medium">Når du har bekræftet at kunden har adgang til sin lader i Zaptec appen, kan du fortsætte til næste trin.</p>
                </div>
                <div x-cloak x-show="formStep === 3">
                    <h3 class="font-bold text-xl pb-2">Integrér lader med Monta</h3>
                    <p class="">Login med kundens login til Zaptec Cloud.</p>
                    <p>Når du er klar med kundens login, så kan I integrere laderen i Monta</p>
                    <button x-show="integrated === false" @click="openModal" type="button" class="flex flex-row justify-center mt-4 h-12 items-center text-white bg-[#FF5252] duration-200 hover:bg-[#FF3162] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                        Start integration med Monta <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="ml-1 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                        </svg>
                    </button>
                    <p x-cloak class="flex flex-rowtext-xl text-green-700 mt-2" x-show="integrated === true">Laderen er bekræftet som integreret <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 ml-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </p>
                    <p x-cloak x-show="integrated === true" @click="openModal" class="text-gray-500 text-sm mt-1">Har du problemer med integrationen? <a href="#" class="underline">Klik her for at prøve igen</a></p><br><br>
                    <p class="font-medium">Når du har bekræftet at laderen er integreret med Monta, kan du fortsætte til næste trin.</p><br>
                </div>
                <div x-cloak x-show="formStep === 4">
                    <h3 class="font-bold text-xl pb-2">Opret kunde i Monta</h3>
                    <p>Hjælp kunden med at oprette sig i Monta appen</p>
                </div>
                <div class="flex flex-row w-full justify-between gap-4 h-12 mt-6">
                    <button x-cloak x-show="formStep > 1" @click="formStep -= 1" type="button" class="h-12 text-white bg-gray-600 duration-200 hover:bg-gray-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                        &larr; Back
                    </button>
                    <button x-cloak x-show="formStep < 3" @click="formStep += 1" type="button" class="h-12 text-white bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                        Fortsæt &rarr;
                    </button>
                    <button id="monta-continue" x-cloak x-show="formStep === 3" @click="formStep += 1" disabled="true" x-bind:disabled="!integrated" type="button" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none h-12 text-white bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">
                        Fortsæt &rarr;
                    </button>

                    <button x-cloak x-show="formStep === 4" type="submit" class="h-12 text-white bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-md w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 outline-0">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="showMonta === true" tabindex="-1" class="flex items-center justify-center overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center md:inset-0 h-full w-full bg-[#00000080]">
        <div class="relative p-4 w-full max-w-2xl h-full">
            <!-- Modal content -->
            <div class="flex flex-col justify-between bg-white rounded-lg shadow dark:bg-gray-700 h-full">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Monta integration
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" @click="closeModal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="flex flex-col h-full p-4 md:p-5 space-y-4 text-center">
                    <p>Husk at du kan scrolle på siden</p>
                    <iframe id="monta-frame"
                            class="grow w-full h-full">Indlæser...
                    </iframe>
                    <span id="loader" class="loader fixed top-1/3 left-1/2 text-center -ml-[24px]"></span>
                </div>
                <!-- Modal footer -->
                <div class="flex flex-col items-center gap-4 p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button id="monta-btn" type="button" disabled="true" @click="integrateCharger" class="disabled:bg-slate-50 disabled:text-slate-500 disabled:border-slate-200 disabled:shadow-none text-white bg-[#57995a] duration-200 hover:bg-[#73b076] focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 w-full">Bekræft integration<span id="timer" class="ml-1"></span></button>
                    <button type="button" @click="closeModal" class="duration-200 py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 w-full">Annullér</button>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('modalData', () => ({
                    showMonta: false,
                    integrated: false,
                    integrateCharger() {
                        if (confirm("Er du sikker på at du har integreret laderen korrekt?") === true) {
                            this.integrated = true;
                            this.showMonta = false;
                            document.querySelector('#monta-frame').src = '';
                        } else {
                            this.integrated = false;
                            this.showMonta = true
                        }
                    },
                    openModal() {
                        document.querySelector('#monta-frame').src = 'https://integrations.monta.com/brands/zaptec/models/zaptec_go/methods?charge_point_identifier=01a5db6c-f1c5-11ee-b12c-ea7e0843fa57&user_identifier=user_2ea327e2-1bd2-11ee-8f83-c6f7be6ec46f&locale=en&operator_id=2253';
                        this.showMonta = true
                        document.getElementById('loader').classList.remove('hidden');
                        var timeoutHandle;
                        function countdown(minutes, seconds) {
                            function tick() {
                                var counter = document.getElementById("timer");
                                counter.innerHTML =
                                    '(' + minutes.toString() + ":" + (seconds < 10 ? "0" : "") + String(seconds) + ')';
                                seconds--;
                                if (seconds == 0 && minutes == 0) {
                                    clearTimeout(timeoutHandle);
                                    var montaBtn = document.getElementById('monta-btn');
                                    montaBtn.disabled = false;
                                    counter.classList.add('hidden');
                                    return;
                                }
                                if (seconds >= 0) {
                                    timeoutHandle = setTimeout(tick, 1000);
                                } else {
                                    if (minutes >= 1) {
                                        // countdown(mins-1);   never reach “00″ issue solved:Contributed by Victor Streithorst
                                        setTimeout(function () {
                                            countdown(minutes - 1, 59);
                                        }, 1000);
                                    }
                                }

                            }
                            tick();
                        }

                        document.getElementById('monta-frame').onload = async function () {
                            await new Promise(r => setTimeout(r, 4000));
                            document.getElementById('loader').classList.add('hidden');
                            countdown(2, 00);
                        };
                    },
                    closeModal() {
                        document.querySelector('#monta-frame').src = '';
                        this.showMonta = false
                    }
                }))
            })
            document.addEventListener('gesturestart', function (e) {
                e.preventDefault();
            });
        </script>
    </div>
    <style>
        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #FFF;
            border-bottom-color: #FF3D00;
            border-radius: 50%;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</body>
</html>
