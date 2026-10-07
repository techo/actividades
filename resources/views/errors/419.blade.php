@extends('main')

@section('main_content')
    <div class="container text-center" style="padding: 48px 16px;">
        <div class="row d-flex justify-content-center">
            <h1 class="text-primary">{{ __('errors.e419.title') }}</h1>
        </div>
        <div class="row d-flex justify-content-center">
            <p style="max-width: 520px; margin: 16px auto;">
                {{ __('errors.e419.message') }}
            </p>
        </div>
        <div class="row d-flex justify-content-center">
            <p id="countdown-419" class="text-muted"></p>
        </div>
        <div class="row d-flex justify-content-center">
            <a href="{{ $retryUrl ?? '/' }}" class="btn btn-primary btn-lg">{{ __('errors.e419.retry') }}</a>
        </div>
    </div>

    <script>
        (function () {
            var url = @json($retryUrl ?? '/');
            var tpl = @json(__('errors.e419.countdown'));
            var seconds = 4;
            var el = document.getElementById('countdown-419');
            function tick() {
                if (el) el.textContent = tpl.replace(':seconds', seconds);
                if (seconds <= 0) { window.location.assign(url); return; }
                seconds--;
                setTimeout(tick, 1000);
            }
            tick();
        })();
    </script>
@endsection
