@extends('layouts.app')

@section('extra_script')
{{-- <script src="https://cdn.jsdelivr.net/npm/vue-tables-2@2.0.34/compiled/index.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/vue-tables-2@2.0.34/dist/vue-tables.js"></script>
@endsection
@section('content')
<div class="container" id="app">
    <div class="row justify-content-center">
        <div class="col">
            <v-client-table ref="table" :data="filteredBookings" :columns="columns" :options="options"/>
                <template slot="actions" slot-scope="props">
                    <button class="btn btn-success" @click="approved(props.row.id, props.row.remarks, props.row.selected_schedule)">
                        Approve
                    </button>
                    <button class="btn btn-danger" @click="decline(props.row.id, props.row.remarks, props.row.selected_schedule)">
                        Decline
                    </button>
                </template>
            </v-client-table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

    Vue.use(VueTables.ClientTable)

     const app = new Vue({
            el: '#app',
            data: {
                id: '',
                bookings: [],
                bookingRemarks: [],
                suggestedScheduleRemarks: [],
                columns: ['id', 'table', 'number_of_persons', 'selected_schedule', 'remarks', 'suggested_schedule', 'suggested_schedule_remark', 'actions'],
                options: {
                    headings: {
                        'id': 'ID',
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
                            return this.checkBookingRemark(row.remarks)
                        },
                        suggested_schedule_remark: function(_, row) {
                            return this.checkSuggestedScheduleRemark(row.suggested_schedule_remark)
                        },
                    }
                }
            },
            async mounted() {
                await this.getBookingRemarks();
                await this.getSuggestedScheduleRemarks()
                await this.getAllBookings();
                this.id = window.location.pathname.split("/").pop()
                if (this.$refs.table && this.id) {
                    console.log('object')
                    this.$refs.table.setFilter(this.id)
                }
            },
            methods: {
                getAllBookings: async function() {
                    try {
                        const response = await axios.get(`/user/bookings/get-all`)
                        this.bookings = response.data.bookings
                    } catch (error) {
                        console.log('THIS', error)   
                    }
                },
                getBookingRemarks: async function() {
                    try {
                        const response = await axios.get(`/booking-remarks`)
                        this.bookingRemarks = response.data.booking_remarks
                        console.log(this.bookingRemarks)
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
                checkBookingRemark: function(remark) {
                    let checkedKey = ''
                    Object.keys(this.bookingRemarks).map(key => {
                        if (this.bookingRemarks[key] === remark) {
                            checkedKey = key
                        }
                    } )

                    return checkedKey
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
                async approved(id) {
                    try {
                        const remarks = this.getBookingRemarkValue('APPROVED')
                        const response = await axios.post(`/user/bookings/approved/${id}`, {remarks})
                        
                        if (response.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: `${response.data.message}`,
                                showConfirmButton: true,
                            }).then(result => {
                                if (result) {
                                    this.bookings = this.bookings.map(booking => {
                                        if (booking.id === id) {
                                            booking.suggested_schedule_remark = remarks
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
                async decline(id) {
                    try {
                        const remarks = this.getBookingRemarkValue('DECLINED')
                        const response = await axios.post(`/user/bookings/declined/${id}`, {remarks})
                        
                        if (response.data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: `${response.data.message}`,
                                showConfirmButton: true,
                            }).then(result => {
                                if (result) {
                                    this.bookings = this.bookings.map(booking => {
                                        if (booking.id === id) {
                                            booking.suggested_schedule_remark = remarks
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
                getBookingRemarkValue(remark) {
                    let checkedValue = ''

                    Object.keys(this.bookingRemarks).map(key => {
                        if (key === remark) {
                            checkedValue = this.bookingRemarks[key]
                        }
                    } )

                    return checkedValue
                },
            },
            watch: {
            },
            computed: {
                filteredBookings() {
                    return this.bookings.map(booking => {
                        return {
                            ...booking, 
                            table: booking.table.name
                        }
                    });
                },
            }
        })
</script>
@endpush