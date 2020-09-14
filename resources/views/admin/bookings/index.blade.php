@extends('admin.layouts.index')

@push('extra_script')
<script src="https://cdn.jsdelivr.net/npm/vue-tables-2@2.0.34/dist/vue-tables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue-js-modal@2.0.0-rc.6/dist/index.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/vue2-datepicker/index.css">
<script src="https://unpkg.com/vue2-datepicker/index.min.js"></script>
@endpush
@push('styles')
<style>
.pending {
    background: grey;
    color: white;
}

.approved {
    background: green;
    color: white;
}

.declined {
    background: red;
    color: white;
}

.suggest-modal {
    padding: 2rem;
    width: 500px !important;
    height: auto !important;
}
</style>
@endpush
@section('content')
    <div id="app">
        <h2>Bookings</h2>
        <v-client-table ref="table" :data="filteredBookings" :columns="columns" :options="options"/>
            <template slot="actions" slot-scope="props">
                <button class="btn btn-success" @click="approved(props.row.id, props.row.remarks, props.row.selected_schedule)">
                    Approve
                </button>
                <button class="btn btn-danger" @click="decline(props.row.id, props.row.remarks, props.row.selected_schedule)">
                    Decline
                </button>
                <button class="btn btn-info" @click="suggest(props.row.id, props.row.remarks, props.row.selected_schedule)">
                    Suggest
                </button>
            </template>
        </v-client-table>
        <modal :classes="suggestModalClass" name="suggest-modal" :width="600":height="600":adaptive="true">   
            <div class="form-row">
                <div class="form-group col">
                    <label for="suggestedDate">Pick a Date</label>
                    <date-picker 
                        id="suggestedDate" 
                        type="date"  
                        v-model="form.suggestedDate"
                        :format="momentFormat"
                        :disabled-date="disabledBeforeToday"
                    >
                    </date-picker>
                    <span class="text-danger" role="alert" v-if="errors.suggestedDate">
                        <small><strong>@{{errors.suggestedDate}}</strong></small>
                    </span>
                </div>
                
                <div class="form-group col">
                    <label for="suggestedTime">Pick a Time</label>
                    <date-picker 
                        id="suggestedTime"
                        v-bind:class="{ 'is-invalid': errors.suggestedTime }" 
                        v-model="form.suggestedTime" lang="en" type="time" 
                        :time-picker-options="timePickerOptions"
                        format="hh:mm a"
                    >
                    </date-picker>
                    <span class="invalid-feedback" role="alert" v-if="errors.suggestedTime">
                        <strong>@{{errors.suggestedTime}}</strong>
                    </span>
                </div>
                <button type="button" class="btn btn-primary" @click="saveSuggest()">Suggest</button>
                <button type="button" class="btn btn-secondary" @click="closeModal()">Close</button>
            </div>
        </modal>
    </div>
@endsection

@push('scripts')
<script>
    const defaulTimePickerOptions = {
        start: '08:00',
        step: '00:30',
        end: '20:00',
    }
    
    Vue.use(window["vue-js-modal"].default);
    Vue.use(VueTables.ClientTable)
    Vue.use(VueTables.Event)

const app = new Vue({
       el: '#app',
       data: {
            id: '',
            form: {
                suggestedDate: '',
                suggestedTime: '',
            },
            bookingId: '',
            timePickerOptions: defaulTimePickerOptions,
            errors: [],
            momentFormat: {
                // Date to String
                stringify: (date) => {
                    return date ? moment(date).format('LL') : ''
                },
                // String to Date
                parse: (value) => {
                    return value ? moment(value, 'LL').toDate() : null
                }
            },
           suggestModalClass: 'suggest-modal',
           showModal:"false",
           bookings: [],
           bookingRemarks: [],
           suggestedScheduleRemarks: [],
           columns: ['id', 'user','table', 'number_of_persons', 'selected_schedule', 'remarks', 'suggested_schedule', 'suggested_schedule_remark', 'actions'],
           options: {
                // filterByColumn: true,
                headings: {
                    'id': 'ID',
                    'user': 'Booked By',
                    'number_of_persons': 'Diners',
                    'selected_schedule': 'Booking Date & Time',
                    'table': 'Table',
                    'remarks': 'Booking Status',
                    'suggested_schedule': 'Suggestion Date & Time',
                    'suggested_schedule_remark': 'Suggestion Status',
                    'actions': 'Actions'
                },
                templates: {
                    selected_schedule: function(_, row) {
                        return !row.selected_schedule || moment(row.selected_schedule).format('LLL')
                    },
                    suggested_schedule: function(_, row) {
                        return  !row.suggested_schedule || moment(row.suggested_schedule).format('LLL') 
                    },
                    remarks: function(_, row) {
                        return this.getBookingRemarkKey(row.remarks)
                    },
                    suggested_schedule_remark: function(_, row) {
                        return this.checkSuggestedScheduleRemark(row.suggested_schedule_remark)
                    },
                },
                cellClasses:{
                    remarks: [
                        {
                            class:'pending',
                            condition: row => row.remarks === 0
                        },
                        {
                            class:'approved',
                            condition: row => row.remarks === 1
                        },
                        {
                            class:'declined',
                            condition: row => row.remarks === 2
                        },
                    ]
                },
                initFilters: {
                    // id: 
                }
           }
       },
       async mounted() {
            await this.getBookingRemarks();
            await this.getSuggestedScheduleRemarks()
            await this.getAllBookings()
            this.id = window.location.pathname.split("/").pop()
            if (this.$refs.table && this.id) {
                console.log('object')
                this.$refs.table.setFilter(this.id)
            }
       },
       methods: {
           getAllBookings: async function() {
                try {
                    const response = await axios.get('/admin/bookings/get-all')
                    this.bookings = response.data.bookings
                } catch (error) {
                    
                }
           },
           getBookingRemarks: async function() {
                    try {
                        const response = await axios.get(`/booking-remarks`)
                        this.bookingRemarks = response.data.booking_remarks
                    } catch (error) {
                        console.log(error)
                    }
            },
            getSuggestedScheduleRemarks: async function() {
                try {
                    const response = await axios.get(`/suggested-schedule-remarks`)
                    this.suggestedScheduleRemarks = response.data.suggested_schedule_remarks
                } catch (error) {
                    console.log(error)
                }
            },
            getBookingRemarkKey: function(remark) {
                let checkedKey = ''

                Object.keys(this.bookingRemarks).map(key => {
                    if (this.bookingRemarks[key] === remark) {
                        checkedKey = key
                    }
                } )

                return checkedKey
            },
            getBookingRemarkValue(remark) {
                let checkedValue = ''

                Object.keys(this.bookingRemarks).map(key => {
                    if (key === remark) {
                        checkedValue = this.bookingRemarks[key]
                    }
                } )

                return checkedValue
            },
            checkSuggestedScheduleRemark: function(remark) {
                let checkedKey = ''

                Object.keys(this.bookingRemarks).map(key => {
                    if (this.bookingRemarks[key] === remark) {
                        checkedKey = key
                    }
                } )

                return checkedKey
            },
            approved(id, remarks, date) {
                const isExpired = this.isExpired(date)
                
                if (isExpired) {
                    Swal.fire({
                        title: `Error`,
                        text: `It's already expired.`,
                        icon: 'warning',
                    })
                    return
                }
                
                const approvedRemarks = this.getBookingRemarkValue('APPROVED')

                if (remarks === approvedRemarks) {
                    Swal.fire({
                        title: `Error`,
                        text: `It's already approved.`,
                        icon: 'warning',
                    })
                    return
                }

                Swal.fire({
                        title: `Do you want to approve this booking?`,
                        showCancelButton: true,
                        confirmButtonText: `Approved`
                    }).then((result) => {

                    if (!result.isConfirmed) {
                        return
                    }
                    this.approvedPost(id, approvedRemarks)
                })
            },
            async approvedPost(id, remarks) {
                try {
                    const response = await axios.post(`/admin/bookings/approved/${id}`, {remarks})
                    
                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: `${response.data.message}`,
                            showConfirmButton: true,
                        }).then(result => {
                            if (result) {
                                this.bookings = this.bookings.map(booking => {
                                    if (booking.id === id) {
                                        booking.remarks = remarks
                                    }
                                    return booking
                                })
                            }
                        })
                    }
                } catch (error) {
                    console.log(error)
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    })
                }
            },
            decline(id, remarks, date) {
                const isExpired = this.isExpired(date)
                
                if (isExpired) {
                    Swal.fire({
                        title: `Error`,
                        text: `It's already expired.`,
                        icon: 'warning',
                    })
                    return
                }

                const approvedRemarks = this.getBookingRemarkValue('APPROVED')
                
                if (remarks === approvedRemarks) {
                    Swal.fire({
                        title: `Error`,
                        text: `You can't declined an approved`,
                        icon: 'warning',
                    })
                    return
                }

                const declinedRemarks = this.getBookingRemarkValue('DECLINED')

                if (remarks === declinedRemarks) {
                    Swal.fire({
                        title: `Error`,
                        text: `It's already declined.`,
                        icon: 'warning',
                    })
                    return
                }

                Swal.fire({
                        title: `Are you sure you want to decline?`,
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: `Declined`
                    }).then((result) => {

                    if (!result.isConfirmed) {
                        return
                    }
                    this.declinedPost(id, declinedRemarks)
                })
            },
            async declinedPost(id, remarks) {
                try {
                    const response = await axios.post(`/admin/bookings/declined/${id}`, {remarks})
                    
                    if (response.data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: `${response.data.message}`,
                            showConfirmButton: true,
                        }).then(result => {
                            if (result) {
                                this.bookings = this.bookings.map(booking => {
                                    if (booking.id === id) {
                                        booking.remarks = remarks
                                    }
                                    return booking
                                })
                            }
                        })
                    }
                } catch (error) {
                    console.log(error)
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    })
                }
            },
            suggest(id, remarks) {
                const approvedRemarks = this.getBookingRemarkValue('APPROVED')
                
                if (remarks === approvedRemarks) {
                    Swal.fire({
                        title: `Error`,
                        text: `It's already an approved.`,
                        icon: 'warning',
                    })
                    return
                }

                this.bookingId = id;
                
                this.$modal.show('suggest-modal');
            },
            isExpired(date) {
                const selectedDate = moment(date);
                const now = moment();
                if (selectedDate.isBefore(now)) {
                    return true
                }
                return false
            },
            closeModal() {
                this.$modal.hide('suggest-modal');
            },
            async saveSuggest() {
                try {
                    if (!this.validateForm()) {
                        return
                    }
                    const booking = this.bookings.find(booking => booking.id === this.bookingId)
                    const data = {
                        'booking_id': booking.id,
                        'table_id': booking.table.id,
                        'suggested_schedule': this.form.suggestedDate,
                        'suggested_time': this.form.suggestedTime
                    }

                    const response = await axios.post(`/admin/bookings/suggestion/${this.bookingId}`, data) 
                 
                    let date  = moment.utc(response.data.booking.suggested_schedule).local()
                    
                    let time = moment.utc(response.data.booking.suggested_time).local()

                    this.form.suggestedDate = ''
                    
                    this.form.suggestedTime = ''
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Congrats, booked successfully.',
                        showConfirmButton: true,
                    }).then(result => {
                        if (result) {
                           this.bookings = this.bookings.map(booking => {
                               if (booking.id === this.bookingId) {
                                   booking = response.data.booking
                               }
                               return booking
                           })
                        }
                        this.$modal.hide('suggest-modal')
                    })
                } catch (error) {
                    this.errors = []
                    if (error.response.status === 422) {
                        const errors = error.response.data.errors
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        })
                        
                        if (errors.table_id) {
                            this.errors['table_id'] = errors.table_id[0]
                        }
                        
                        if (errors.suggested_schedule) {
                            this.errors['suggested_schedule'] = errors.selected_date[0]
                        }
                        
                        if (errors.booking_id) {
                            this.errors['booking_id'] = errors.number_of_persons[0]
                        }

                        if (errors.suggested_time) {
                            this.errors['suggested_time'] = errors.selected_time[0]
                        }
                    }
                }
            },
            disabledBeforeToday(date) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                return date < today;
            },
            validateForm: function() {
                this.errors = []
                if (!this.form?.suggestedDate) {
                    console.log('object')
                    this.errors['suggestedDate'] = `Please pick a Date.`
                    console.log(this.errors.suggestedDate)
                    return false
                }

                if (!this.form?.suggestedTime) {
                    this.errors['suggestedTime'] = `Please pick Time.`
                    return false
                }
                return true
            },
            getId() {
                return this.id
            }
       },
       watch: {
            'form.suggestedDate': function(newValue, oldValue) {
                this.errors['suggestedDate'] = undefined

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
            'form.suggestedTime': function(newValue, oldValue) {
                this.errors['suggestedTime'] = undefined
            },
       },
       computed: {
            filteredBookings() {
                return this.bookings.map(booking => {
                    return {
                        ...booking, 
                        table: booking.table.name,
                        user: booking.user.name,
                        uri: `/admin/bookings/${booking.id}`
                        }
                });
            },
        }
})
</script>
@endpush