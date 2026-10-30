<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Scan: {{ $event->title }}</h2></x-slot>
    <div class="py-8 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            <div id="reader" class="w-full"></div>
            <div id="result" class="mt-4 p-3 rounded hidden"></div>
        </div>
        <a href="{{ route('events.attendees', $event) }}" class="inline-block mt-4 text-indigo-600">View attendees</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const box = document.getElementById('result');
        let busy = false;

        function show(msg, ok) {
            box.textContent = msg;
            box.className = 'mt-4 p-3 rounded ' + (ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
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
                    body: JSON.stringify({ token }),
                });
                const data = await res.json();
                show(data.message ?? 'Something went wrong.', res.ok);
            } catch (e) {
                show('Network error. Try again.', false);
            }
            setTimeout(() => (busy = false), 3000);
        }

        new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 }, false).render(onScan, () => {});
    </script>
</x-app-layout>