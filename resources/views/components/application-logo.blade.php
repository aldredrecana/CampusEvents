{{-- CampusEvents seal in PUP maroon and gold. To use the official PUP logo instead,
     put the file in public/images/pup-logo.png and replace this SVG with:
     <img src="{{ asset('images/pup-logo.png') }}" alt="PUP" {{ $attributes }}> --}}
<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="CampusEvents" {{ $attributes }}>
    <circle cx="32" cy="32" r="31" fill="#fcc419" />
    <circle cx="32" cy="32" r="27" fill="#800000" />
    <circle cx="32" cy="32" r="23" fill="none" stroke="#fcc419" stroke-width="1"
        stroke-dasharray="2 2" />
    <text x="32" y="38" text-anchor="middle" font-family="Figtree, Arial, sans-serif" font-size="18" font-weight="700"
        fill="#fcc419">CE</text>
</svg>
