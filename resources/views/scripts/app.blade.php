<script>
window.Laravel = {!! json_encode([
    'csrfToken' => csrf_token(),
]) !!};

window.App = {
    stripePublicKey: '{{ config('services.stripe.key') }}'
}
</script>
