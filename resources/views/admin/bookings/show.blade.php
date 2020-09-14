@extends('admin.layouts.index')

@push('extra_script')
<script src="https://cdn.jsdelivr.net/npm/vue-tables-2@2.0.34/dist/vue-tables.js"></script>
@endpush

@section('content')
    <div id="app">
        <h2>Booked by {{$booking->user->name}}</h2>
        <form>
            <div class="col-6">
                <div class="form-row">
                    <button type="button" class="btn btn-success">APPROVED</button>
                    <button type="button" class="btn btn-danger">DECLINE</button>
                    {{-- <div class="col">
                    <input type="text" class="form-control" placeholder="First name">
                    </div>
                    <div class="col">
                    <input type="text" class="form-control" placeholder="Last name">
                    </div> --}}
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>

const app = new Vue({
       el: '#app',
       data: {
         
       }
})
</script>
@endpush