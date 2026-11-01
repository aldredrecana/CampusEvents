<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">My QR Code</h2>
    </x-slot>
    <div class="py-8 max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg text-center">
            <canvas id="qr" class="mx-auto"></canvas>
            <p class="mt-4 font-semibold">{{ auth()->user()->name }}</p>
            @if (auth()->user()->student_number)
                <p class="text-sm text-gray-700">
                    {{ auth()->user()->student_number }} &middot;
                    {{ auth()->user()->course }} {{ auth()->user()->year_level }}-{{ auth()->user()->section }}
                </p>
            @endif
            <p id="status" class="mt-2 text-xs text-gray-500">Loading code...</p>
            <p class="mt-2 text-sm text-gray-500">Show this to the staff at the entrance. The code refreshes
                automatically.</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
    <script>
        const canvas = document.getElementById('qr');
        const status = document.getElementById('status');

        async function refresh() {
            try {
                const res = await fetch(@json(route('my-qr.code')), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                await QRCode.toCanvas(canvas, data.token, {
                    width: 260
                });
                status.textContent = 'Code refreshes every 30 seconds';
            } catch (e) {
                status.textContent = 'Offline. Reconnect to get a fresh code.';
            }
        }

        refresh();
        setInterval(refresh, 30000);
    </script>
</x-app-layout>
