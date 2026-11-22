<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">My QR Code</h2>
    </x-slot>
    <div class="py-8 max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow overflow-hidden text-center">
            <div class="bg-maroon-700 text-white py-3 border-b-4 border-gold-400">
                <div class="font-bold">{{ auth()->user()->name }}</div>
                @if (auth()->user()->student_number)
                    <div class="text-sm text-gold-300">
                        {{ auth()->user()->student_number }} &middot;
                        {{ auth()->user()->course }} {{ auth()->user()->year_level }}-{{ auth()->user()->section }}
                    </div>
                @endif
            </div>

            <div class="p-6">
                <canvas id="qr" class="mx-auto max-w-full"></canvas>

                {{-- Countdown bar so students can see when the code changes --}}
                <div class="mt-4 h-2 rounded-full bg-maroon-100 overflow-hidden">
                    <div id="bar" class="h-full bg-maroon-700" style="width: 100%"></div>
                </div>
                <p id="status" class="mt-2 text-xs text-gray-500">Loading code...</p>
                <p class="mt-3 text-sm text-gray-600">Show this to the staff at the entrance. Screenshots will not work.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const REFRESH = 30; // seconds
            const canvas = document.getElementById('qr');
            const status = document.getElementById('status');
            const bar = document.getElementById('bar');
            let left = REFRESH;

            async function refresh() {
                try {
                    const res = await fetch(@json(route('my-qr.code')), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Server returned ' + res.status);
                    const data = await res.json();
                    await window.QRCode.toCanvas(canvas, data.token, {
                        width: 320,
                        margin: 2,
                        errorCorrectionLevel: 'L',
                        color: {
                            dark: '#4d080a',
                            light: '#ffffff'
                        },
                    });
                    left = REFRESH;
                } catch (e) {
                    status.textContent = 'Cannot load code. Check your internet connection.';
                    console.error(e);
                }
            }

            // Countdown, then fetch a new code
            setInterval(() => {
                left--;
                if (left <= 0) refresh();
                bar.style.width = (Math.max(left, 0) / REFRESH * 100) + '%';
                status.textContent = 'New code in ' + Math.max(left, 0) + 's';
            }, 1000);

            // Get a fresh code right away when the student comes back to this tab
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    refresh();
                    keepAwake();
                }
            });

            // Keep the phone screen on while the QR is shown (where the browser supports it)
            async function keepAwake() {
                try {
                    await navigator.wakeLock?.request('screen');
                } catch (e) {}
            }

            refresh();
            keepAwake();
        });
    </script>
</x-app-layout>
