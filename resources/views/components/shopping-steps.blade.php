<nav class="shopping-steps" aria-label="{{ __('Shopping progress') }}">
    <a href="{{ route('customer.shop') }}" @if($current === 1) aria-current="step" @endif><span>1</span>{{ __('Choose products') }}</a>
    <a href="{{ route('customer.cart') }}" @if($current === 2) aria-current="step" @endif><span>2</span>{{ __('Review cart') }}</a>
    <span @if($current === 3) aria-current="step" @endif><span>3</span>{{ __('Order') }}</span>
</nav>
