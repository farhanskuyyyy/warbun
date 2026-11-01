<button {{ $attributes->merge(['type' => 'submit', 'class' => 'warbun-button warbun-button-primary']) }}>
    {{ $slot }}
</button>
