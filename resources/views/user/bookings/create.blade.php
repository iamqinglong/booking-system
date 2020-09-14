@extends('layouts.app')

@section('extra_script')
<link rel="stylesheet" href="https://unpkg.com/vue2-datepicker/index.css">
<script src="https://unpkg.com/vue2-datepicker/index.min.js"></script>
<!-- use the latest vue-select release -->
<script src="https://unpkg.com/vue-select@latest"></script>
<link rel="stylesheet" href="https://unpkg.com/vue-select@latest/dist/vue-select.css">
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Book a Table
                    <i class="fas fa-clock"></i>
                </div>

                <div class="card-body d-flex justify-content-center">
                    {{-- <validation-errors :errors="errors" v-if="errors"></validation-errors> --}}
                    <form method="POST" action="{{ route('user.bookings.store') }}" @submit.prevent="onSubmit">
                        @csrf
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="col-form-label text-md-right">Pick a Date</label>

                                <date-picker v-bind:class="{ 'is-invalid': errors.selectedDate }"  v-model="form.selectedDate" lang="en" type="date" :disabled-date="disabledBeforeToday" 
                                :format="momentFormat"
                                >
    
                                </date-picker>
                                <span class="invalid-feedback" role="alert" v-if="errors.selectedDate">
                                    <strong>@{{errors.selectedDate}}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="name" class="col-form-label text-md-right">Dining Time</label>

                                <date-picker v-bind:class="{ 'is-invalid': errors.selectedTime }" v-model="form.selectedTime" lang="en" type="time" 
                                  :time-picker-options="timePickerOptions"
                                  format="hh:mm a"
                                  >
                                
                                </date-picker>
                                <span class="invalid-feedback" role="alert" v-if="errors.selectedTime">
                                    <strong>@{{errors.selectedTime}}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label for="name" class="col-form-label text-md-right">How Many Diners? </label>
                                <input
                                    min="1"
                                    v-model="form.numberOfPersons"
                                    type="number"
                                    class="form-control"
                                    autofocus
                                    v-bind:class="{ 'is-invalid': errors.numberOfPersons }"
                                />
                                <span class="invalid-feedback" role="alert" v-if="errors.numberOfPersons">
                                    <strong>@{{errors.numberOfPersons}}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label for="password" class="col-form-label text-md-right">Choose your Table</label>
                                <v-select v-bind:class="{ 'is-invalid': errors.table }" :options="filteredOptions" v-model="form.selectedTable" label="name" :reduce="table => table.id"></v-select>
                                <span class="invalid-feedback" role="alert" v-if="errors.table">
                                    <strong>@{{errors.table}}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    const defaulTimePickerOptions = {
        start: '08:00',
        step: '00:30',
        end: '20:00',
    }
    
    Vue.component('v-select', VueSelect.VueSelect)
    
    const app = new Vue({
        el: '#app',
        data: {
            form: {
                numberOfPersons : undefined,
                selectedDate : undefined,
                selectedTime: undefined,
                selectedTable: undefined
            },
            tables: [],
            errors: [],
            timePickerOptions: defaulTimePickerOptions,
            success : false,   
            momentFormat: {
                // Date to String
                stringify: (date) => {
                    return date ? moment(date).format('LL') : ''
                },
                // String to Date
                parse: (value) => {
                    return value ? moment(value, 'LL').toDate() : null
                }
            } 
        },
        mounted() {
            this.getAllTables();
        },
        methods : {
            onSubmit: async function(e) {
                e.preventDefault()
                
                if (!this.validateForm()) {
                    return
                }
                
                const data = {
                        'selected_date': this.form.selectedDate,
                        'selected_time': this.form.selectedTime,
                        'number_of_persons': this.form.numberOfPersons,
                        'table_id': this.form.selectedTable
                    }

                try {
                    const response = await axios.post(e.target.action, data)

                    let date  = moment.utc(response.data.bookings.selected_schedule).local()
                    
                    let time = moment.utc(response.data.bookings.selected_time).local()

                    this.form.selectedDate = new Date(date)
                    
                    this.form.selectedTime = new Date(time)
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Congrats, booked successfully.',
                        showConfirmButton: true,
                    }).then(result => {
                        if (result) {
                            window.location = '/user/bookings'
                        }
                    })

                } catch (error) {   
                    this.errors = []
                    if (error.response.status === 422) {
                        const errors = error.response.data.errors
                        
                        if (!error.response.data.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: `${error.response.data.message}`,
                            })
                            return
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        })
                        
                        if (errors.table_id) {
                            this.errors['table'] = errors.table_id[0]
                        }
                        
                        if (errors.selected_date) {
                            this.errors['selectedDate'] = errors.selected_date[0]
                        }
                        
                        if (errors.number_of_persons) {
                            this.errors['numberOfPersons'] = errors.number_of_persons[0]
                        }

                        if (errors.selected_time) {
                            this.errors['selectedTime'] = errors.selected_time[0]
                        }
                    }
                }
            },
            getAllTables: async function() {
                try {
                    const response = await axios.get(`/user/tables/get-all`)
                    this.tables = response.data.tables
                } catch (error) {
                    console.log('THIS', error)   
                }
            },
            validateForm: function() {
                this.errors = []
                console.log('validate')
                if (!this.form?.selectedDate) {
                    this.errors['selectedDate'] = `Please pick a Date.`
                    return false
                }

                if (!this.form?.selectedTime) {
                    this.errors['selectedTime'] = `Please pick your dining time.`
                    return false
                }
                
                if (!this.form?.numberOfPersons) {
                    this.errors['numberOfPersons'] = `Please input how many diner/s.`
                    return false
                }
                
                if (!this.form?.selectedTable) {
                    this.errors['table'] = `Please pick your desired Table.`
                    return false
                }
                return true
            },
            disabledBeforeToday(date) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                return date < today;
            },
            validateTableCapacity(id) {
                 const table = this.filteredOptions.find(table => table.id === id)
                
                if (this.form?.numberOfPersons > table.number_of_seats) {
                    return table.number_of_seats;
                    // this.errors['numberOfPersons'] = `Sorry, only ${table.number_of_seats} diners for this table.`
                }
                return false;
            }
        },
        watch: {
            'form.selectedDate': function(newValue, oldValue) {
                this.errors['selectedDate'] = undefined

                let today = new Date()

                today.setHours(0,0,0,0)

                if (!newValue || newValue.getTime() !== today.getTime()) {
                    this.timePickerOptions = defaulTimePickerOptions
                    return
                }

                today = new Date()
                
                today.setMinutes(today.getMinutes() + 30)

                this.timePickerOptions = {
                    start: `${today.getHours()}:00`,
                    step: '00:30',
                    end: '20:00',
                }

                return
            },
            'form.selectedTime': function(newValue, oldValue) {
                this.errors['selectedTime'] = undefined
            },
            'form.numberOfPersons': function(newValue, oldValue) {
                this.errors['numberOfPersons'] = undefined
                this.errors['table'] = undefined
                
                if (!newValue || !this.form.selectedTable) return

                if (numSeats = this.validateTableCapacity(this.form.selectedTable)) {
                    this.errors['numberOfPersons'] = `Sorry, only ${numSeats} diners for this table.`
                    return
                }
            },
            'form.selectedTable': function(newValue, oldValue) {
                this.errors['table'] = undefined
                
                if (!newValue) return
                
                if (numSeats = this.validateTableCapacity(newValue)) {
                    this.errors['table'] = `Sorry, only ${numSeats} diners for this table. Please choose another table.`
                    console.log(this.errors)
                    return
                }
            },
        }, 
        computed: {
            filteredOptions() {
                return this.tables.map(option => {
                    return {...option, name: `${option.name} with ${option.number_of_seats} seats`}
                });
            },
        }
    });
</script>

@endpush
    

