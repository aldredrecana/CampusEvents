<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Scan: {{ $event->title }}</h2>
        <p class="text-sm text-gray-500">{{ $event->venue }} &middot; {{ $event->starts_at->format('M d, h:i A') }} -
            {{ $event->ends_at->format('h:i A') }}</p>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        @unless ($event->isScanOpen())
            <div class="mb-4 p-3 rounded bg-gold-100 text-maroon-900 text-sm">
                Scanning opens at {{ $event->opensAt()->format('M d, h:i A') }} and closes at
                {{ $event->ends_at->format('h:i A') }}.
            </div>
        @endunless

        {{-- Big result banner so staff can read it from arm's length --}}
        <div id="result" class="mb-4 p-4 rounded-lg text-center text-lg font-semibold bg-gray-100 text-gray-600">
            Point the camera at a student's QR code
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div id="reader" class="w-full"></div>
        </div>

        <div class="mt-4 flex justify-between text-sm">
            <span class="text-gray-500">Scanned this session: <b id="count">0</b></span>
            <a href="{{ route('events.attendees', $event) }}" class="text-maroon-700 font-semibold">View attendees
                &rarr;</a>
        </div>
    </div>

    <script>
        const box = document.getElementById('result');
        const counter = document.getElementById('count');
        let busy = false;
        let scanned = 0;

        // Short beep: high tone = success, low tone = error
        function beep(ok) {
            try {
                const ctx = new(window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                osc.frequency.value = ok ? 880 : 220;
                osc.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + (ok ? 0.15 : 0.4));
            } catch (e) {}
        }

        function show(msg, ok) {
            box.textContent = msg;
            box.className = 'mb-4 p-4 rounded-lg text-center text-lg font-semibold ' +
                (ok ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
            beep(ok);
            navigator.vibrate?.(ok ? 100 : [100, 80, 100]);
        }

        async function onScan(token) {
            if (busy) return;
            busy = true;
            try {
                const res = await fetch(@json(route('events.scan.store', $event)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        token
                    }),
                });
                const data = await res.json();
                if (res.ok) counter.textContent = ++scanned;
                show(data.message ?? 'Something went wrong.', res.ok);
            } catch (e) {
                show('Network error. Try again.', false);
            }
            setTimeout(() => (busy = false), 2000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            new window.Html5QrcodeScanner('reader', {
                fps: 10,
                qrbox: 250
            }, false).render(onScan, () => {});
        });
    </script>
</x-app-layout>
