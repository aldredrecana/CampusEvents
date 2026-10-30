<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">My QR Code</h2></x-slot>
    <div class="py-8 max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg text-center">
            <canvas id="qr" class="mx-auto"></canvas>
            <p class="mt-4 font-semibold">{{ auth()->user()->name }}</p>
            <p class="text-sm text-gray-500">Show this to the organizer at the entrance.</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.4/build/qrcode.min.js"></script>
    <script>
        QRCode.toCanvas(document.getElementById('qr'), @json(auth()->user()->qr_token), { width: 260 });
    </script>
</x-app-layout>